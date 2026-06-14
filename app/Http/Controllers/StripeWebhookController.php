<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                env('STRIPE_WEBHOOK_SECRET')
            );
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        $type = $event->type;
        $dataObj = $event->data->object;

        try {
            switch ($type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($dataObj);
                    break;

                case 'customer.subscription.updated':
                    $this->handleCustomerSubscriptionUpdated($dataObj);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleCustomerSubscriptionDeleted($dataObj);
                    break;

                case 'invoice.paid':
                    $this->handleInvoicePaid($dataObj);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($dataObj);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handling failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return response('Webhook handler failed', 500);
        }

        return response('OK', 200);
    }

    private function handleCheckoutSessionCompleted($session): void
    {
        $customerEmail = $session->customer_details->email ?? $session->customer_email ?? null;
        $stripeSubscriptionId = $session->subscription ?? null;

        $metadata = (array) ($session->metadata ?? []);
        $userId = $metadata['user_id'] ?? null;
        $planId = $metadata['plan_id'] ?? null;
        $paymentType = $metadata['payment_type'] ?? 'by_user';

        $user = null;

        if ($userId) {
            $user = User::find($userId);
        }

        if (!$user && $customerEmail) {
            $user = User::where('email', $customerEmail)->first();
        }

        if (!$user || !$stripeSubscriptionId) {
            Log::warning('checkout.session.completed missing user or subscription', [
                'user_id' => $userId,
                'customer_email' => $customerEmail,
                'stripe_subscription_id' => $stripeSubscriptionId,
            ]);
            return;
        }

        $sub = StripeSubscription::retrieve($stripeSubscriptionId);
        $priceId = $sub->items->data[0]->price->id ?? null;

        $plan = null;
        if ($planId) {
            $plan = Plan::find($planId);
        }
        if (!$plan && $priceId) {
            $plan = Plan::where('stripe_price_id', $priceId)->first();
        }

        if (!$plan) {
            Log::warning('No plan found for completed checkout', [
                'user_id' => $user->id,
                'price_id' => $priceId,
                'plan_id' => $planId,
            ]);
            return;
        }

        $this->applySubscriptionToUser(
            user: $user,
            plan: $plan,
            stripeSubscription: $sub,
            paymentType: $paymentType,
            notes: 'Checkout session completed'
        );
    }

    private function handleCustomerSubscriptionUpdated($sub): void
    {
        $stripeSubId = $sub->id ?? null;
        if (!$stripeSubId) {
            return;
        }

        $user = User::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$user) {
            return;
        }

        $priceId = $sub->items->data[0]->price->id ?? null;
        $plan = Plan::where('stripe_price_id', $priceId)->first();

        if (!$plan) {
            Log::warning('No plan found for subscription.updated', [
                'stripe_subscription_id' => $stripeSubId,
                'price_id' => $priceId,
            ]);
            return;
        }

        $paymentType = $sub->metadata->payment_type ?? 'by_user';

        $this->applySubscriptionToUser(
            user: $user,
            plan: $plan,
            stripeSubscription: $sub,
            paymentType: $paymentType,
            notes: 'Subscription updated'
        );
    }

    private function handleCustomerSubscriptionDeleted($sub): void
    {
        $stripeSubId = $sub->id ?? null;
        if (!$stripeSubId) {
            return;
        }

        $user = User::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$user) {
            return;
        }

        $user->subscription = 'canceled';
        $user->subscription_canceled_at = now();
        $user->subscription_ends_at = now();
        $user->save();

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $user->plan_id,
            'subscription' => 'canceled',
            'payment_type' => 'by_user',
            'started_at' => $user->subscription_started_at,
            'ends_at' => now(),
            'price_cents' => 0,
            'currency' => null,
            'traffic_limit' => $user->traffic_limit ?? 0,
            'stripe_subscription_id' => $stripeSubId,
            'stripe_price_id' => null,
            'notes' => 'Subscription deleted from Stripe',
        ]);

        $user->plan_id = null;
        $user->stripe_subscription_id = null;
        $user->save();
    }

    private function handleInvoicePaid($invoice): void
    {
        $stripeSubId = $invoice->subscription ?? null;
        if (!$stripeSubId) {
            return;
        }

        $user = User::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$user) {
            return;
        }

        if ($user->subscription !== 'active') {
            $user->subscription = 'active';
            $user->subscription_renewed_at = now();
            $user->save();
        }
    }

    private function handleInvoicePaymentFailed($invoice): void
    {
        $stripeSubId = $invoice->subscription ?? null;
        if (!$stripeSubId) {
            return;
        }

        $user = User::where('stripe_subscription_id', $stripeSubId)->first();
        if (!$user) {
            return;
        }

        $user->subscription = 'payment_failed';
        $user->save();
    }

    private function applySubscriptionToUser(
        User $user,
        Plan $plan,
        $stripeSubscription,
        string $paymentType,
        ?string $notes = null
    ): void {
        $status = $stripeSubscription->status ?? 'active';
        $periodStart = $stripeSubscription->current_period_start ?? null;
        $periodEnd = $stripeSubscription->current_period_end ?? null;
        $priceId = $stripeSubscription->items->data[0]->price->id ?? null;

        $startedAt = $periodStart ? Carbon::createFromTimestamp($periodStart) : now();
        $endsAt = $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null;

        $user->plan_id = $plan->id;
        $user->subscription = $status;
        $user->stripe_subscription_id = $stripeSubscription->id ?? null;
        $user->stripe_customer_id = $stripeSubscription->customer ?? $user->stripe_customer_id;
        $user->traffic_limit = $plan->traffic_limit;
        $user->subscription_started_at = $startedAt;
        $user->subscription_ends_at = $endsAt;
        $user->subscription_renewed_at = now();
        $user->save();

        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscription' => $status,
            'payment_type' => $paymentType,
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'price_cents' => $plan->price_cents,
            'currency' => $plan->currency,
            'traffic_limit' => $plan->traffic_limit,
            'stripe_subscription_id' => $stripeSubscription->id ?? null,
            'stripe_price_id' => $priceId,
            'notes' => $notes,
        ]);
    }
}
