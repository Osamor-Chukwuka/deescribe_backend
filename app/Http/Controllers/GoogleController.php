<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    //
    public function redirectToGoogle($action)
    {
        if (!in_array($action, ['login', 'register'])) {
            abort(400, 'Invalid action');
        }

        $redirectUrl = config('app.url') . '/api/auth/google/callback';

        return Socialite::driver('google')->with(['state' => $action, 'prompt' => 'select_account'])->redirectUrl($redirectUrl)->redirect();
    }

    // handle google call back
    public function handleGoogleCallback()
    {
        $frontendUrl = config('app.frontend_url');
        $action = request()->query('state');
        try {
            $redirectUrl = config('app.url') . '/api/auth/google/callback';
            $googleUser = Socialite::driver('google')->stateless()->redirectUrl($redirectUrl)->user();
            
            
            $user = User::where('google_id', $googleUser->getId())->orWhere('email', $googleUser->getEmail())->first();

            if ($user && $action === 'login') {
                // Existing user, so login
                Auth::login($user);

                $user->tokens()->delete(); // revoke all previous tokens
                $token = $user->createToken('auth_token')->plainTextToken;

                return redirect()->away($frontendUrl . '/login?token=' . $token . '&user=' . urlencode(json_encode($user)));
            } else if($user && $action === 'register') {
                return redirect()->away($frontendUrl . '/sign-up?error=Account already exist, please login');
            }

            // NEW user...Create user if request is from register flow
            if ($action === 'register') {
                $newUser = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(str()->random(12)),
                ]);

                $token = $newUser->createToken('auth_token')->plainTextToken;

                return redirect()->away($frontendUrl . '/sign-up?token=' . $token . '&user=' . urlencode(json_encode($newUser)));
            }

            // Otherwise, block login attempt
            return redirect()->away($frontendUrl . '/login?error=No account found. Please sign up first');
        } catch (\Exception $e) {
            return redirect()->away($frontendUrl . '/'.$action.'?error=Something went wrong while authenticating with Google.');
        }
    }
}
