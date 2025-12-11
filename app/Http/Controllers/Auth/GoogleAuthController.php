<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update Google ID if not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }

                // Clear invitation token if exists
                if ($user->invitation_token) {
                    $user->update([
                        'invitation_token' => null,
                        'invited_at' => null,
                    ]);
                }

                Auth::login($user);

                // Redirect based on role
                return $this->redirectByRole($user);
            }

            // Check if user has invitation token
            if ($request->has('token')) {
                $invitedUser = User::where('invitation_token', $request->token)
                    ->where('email', $googleUser->getEmail())
                    ->first();

                if ($invitedUser) {
                    $invitedUser->update([
                        'google_id' => $googleUser->getId(),
                        'invitation_token' => null,
                        'invited_at' => null,
                        'email_verified_at' => now(),
                    ]);

                    Auth::login($invitedUser);
                    return $this->redirectByRole($invitedUser);
                }
            }

            return redirect()->route('login')
                ->with('error', 'You are not authorized to access this system. Please contact administrator.');

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Google Auth Error: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Redirect user based on their role
     */
    private function redirectByRole(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('reseller.dashboard');
        }
    }
}

