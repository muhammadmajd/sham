<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        AuditService::log('EmailVerification.sendVerificationEmail', 'User', [
            'from' => false,
            'to' => true,
        ]);
        $user = User::find($request->user_id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->verification_token = Str::random(40);
        $user->save();

        // Note: You should create an actual Mailable class; here we keep it minimal.
        Mail::raw('Verification token: ' . $user->verification_token, function ($m) use ($user) {
            $m->to($user->email)->subject('Verify your email');
        });

        return response()->json(['message' => 'Verification email sent']);
    }

    public function verify($token)
    {
        AuditService::log('EmailVerification.verify', 'User', [
            'from' => false,
            'to' => true,
        ]);
        $user = User::where('verification_token', $token)->first();

        if (!$user) return response()->json(['message' => 'Invalid token'], 400);

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        return response()->json(['message' => 'Email verified']);
    }
}
