<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Plan;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'price_id' => ['required', 'string'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ]);

        $plan = Plan::where('stripe_price_id', $data['price_id'])->firstOrFail();

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'customer_email' => $user->email,
            'line_items' => [[
                'price' => $data['price_id'],
                'quantity' => 1,
            ]],
            'metadata' => [
                'user_id' => (string) $user->id,
                'plan_id' => (string) $plan->id,
                'payment_type' => 'by_user',
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan_id' => (string) $plan->id,
                    'payment_type' => 'by_user',
                ],
            ],
            'success_url' => $data['success_url'] ?? env('APP_URL') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $data['cancel_url'] ?? env('APP_URL') . '/payment/cancel',
        ]);

        return response()->json([
            'id' => $session->id,
            'url' => $session->url,
        ]);
    }
}
