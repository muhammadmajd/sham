<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Do not reveal whether the email exists
        if (!$user) {
            return response()->json([
                'message' => 'If that email exists, reset instructions were sent.',
            ]);
        }

        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $resetUrl = config('app.frontend_url', 'http://localhost:3000')
            . '/reset-password?email=' . urlencode($user->email)
            . '&token=' . urlencode($plainToken);

        Mail::raw(
            "Click the link to reset your password:\n\n{$resetUrl}",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset your password');
            }
        );

        return response()->json([
            'message' => 'If that email exists, reset instructions were sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $row = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$row) {
            return response()->json([
                'message' => 'Invalid or expired reset token',
            ], 400);
        }

        if (Carbon::parse($row->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->delete();

            return response()->json([
                'message' => 'Reset token expired',
            ], 400);
        }

        if (!Hash::check($validated['token'], $row->token)) {
            return response()->json([
                'message' => 'Invalid or expired reset token',
            ], 400);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->delete();

        // optional: revoke all refresh tokens
        $user->refreshTokens()->update(['revoked' => true]);

        return response()->json([
            'message' => 'Password has been reset successfully',
        ]);
    }
}
