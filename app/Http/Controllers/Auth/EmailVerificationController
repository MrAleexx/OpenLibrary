<?php
// app/Http/Controllers/Auth/EmailVerificationController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice.
     */
    public function notice(Request $request): Response|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard'))
                    : Inertia::render('Auth/VerifyEmail', [
                        'status' => $request->session()->get('status'),
                    ]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard').'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($request->user()));
        }

        return redirect()->intended(route('dashboard').'?verified=1');
    }

    /**
     * Send a new email verification notification.
     */
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
