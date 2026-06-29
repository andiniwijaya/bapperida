<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\PasswordResetResponse;
use App\Http\Responses\RegisterResponse;
use App\Http\Responses\VerifyEmailResponse;
use App\Models\Department;
use App\Services\Auth\AccountStatusService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

/**
 * Bootstraps Laravel Fortify authentication views, actions, and rate limiting.
 *
 * Business rules:
 * - Login validates account status (active only) via AccountStatusService.
 * - Registration creates pending users; email verification is required.
 * - Two-factor authentication is optional per-user via Security settings.
 *
 * Related modules: User Management, Registration, Department (register form).
 */
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register Fortify response bindings for registration and email verification.
     */
    public function register(): void
    {
        $this->app->singleton(
            RegisterResponseContract::class,
            RegisterResponse::class
        );

        $this->app->singleton(
            VerifyEmailResponseContract::class,
            VerifyEmailResponse::class
        );

        $this->app->singleton(
            PasswordResetResponseContract::class,
            PasswordResetResponse::class
        );
    }

    /**
     * Configure Fortify actions, views, rate limiters, and custom authentication.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureAuthentication();
    }

    /**
     * Bind Fortify password reset and user creation actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Register Blade views for Fortify authentication screens.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('livewire.auth.login'));
        Fortify::verifyEmailView(fn () => view('livewire.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::registerView(function () {
            return view('livewire.auth.register', [
                'departments' => Department::query()
                    ->availableForPublicRegistration()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray(),
            ]);
        });
        Fortify::resetPasswordView(fn () => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
    }

    /**
     * Apply per-route rate limiters for login, register, and two-factor challenges.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $login = Str::lower((string) $request->input(Fortify::username()));
            $throttleKey = Str::transliterate($login.'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('register', function (Request $request) {
            $email = Str::lower((string) $request->input('email', ''));
            $throttleKey = Str::transliterate($email.'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }

    /**
     * Authenticate by email/username and enforce account status before session login.
     *
     * Security: returns null on invalid credentials to avoid user enumeration in Fortify flow.
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $accountStatusService = app(AccountStatusService::class);

            $user = $accountStatusService->findByLogin((string) $request->input('login'));
            $password = (string) $request->input('password');

            if ($user === null || ! Hash::check($password, $user->password)) {
                return null;
            }

            $accountStatusService->validateForLogin($user);

            return $user;
        });
    }
}
