<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Vistas de autenticación
        Fortify::loginView(function () {
            return inertia('auth/Login', [
                'canResetPassword' => true,
                'canRegister' => true,
                'status' => session('status'),
            ]);
        });

        Fortify::registerView(function () {
            return inertia('auth/Register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return inertia('auth/ForgotPassword');
        });

        Fortify::resetPasswordView(function (Request $request) {
            return inertia('auth/ResetPassword', [
                'email' => $request->email,
                'token' => $request->route('token'),
            ]);
        });

        Fortify::verifyEmailView(function () {
            return inertia('auth/VerifyEmail');
        });

        Fortify::confirmPasswordView(function () {
            return inertia('auth/ConfirmPassword');
        });

        Fortify::twoFactorChallengeView(function () {
            return inertia('auth/TwoFactorLogin');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}