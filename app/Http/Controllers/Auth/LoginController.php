<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Exception;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
       public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user exists
            $existingUser = User::where('email', $googleUser->email)->first();
            
            if ($existingUser) {
                // Log them in
                Auth::login($existingUser);
            } else {
                // Create a new user
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => bcrypt("password"), // Random password as they'll login through Google
                    'email_verified_at' => now(), // No need for email verification
                ]);
                
                Auth::login($newUser);
            }
            
            // Redirect to admin subdomain dashboard
            return redirect()->away('http://localhost.admin:8000/dashboard');
            
        } catch (Exception $e) {
            // If there's an error, redirect back with message
            return redirect()->route('login')
                ->with('error', 'Google authentication failed: ' . $e->getMessage());
        }
    }
}