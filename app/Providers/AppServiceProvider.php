<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Gate;
use App\Models\IncomingLetter;
use App\Models\LetterNumberRegistration;
use App\Models\OutgoingLetter;
use App\Models\Report;
use App\Models\User;
use App\Models\Department;
use App\Models\RegistrationRequest;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\Dashboard;
use App\Policies\IncomingLetterPolicy;
use App\Policies\LetterNumberRegistrationPolicy;
use App\Policies\ReportPolicy;
use App\Policies\OutgoingLetterPolicy;
use App\Policies\UserPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\RegistrationRequestPolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\ActivityLogPolicy;
use Illuminate\Auth\Events\Verified;
use App\Listeners\LogVerifiedEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use App\Policies\DashboardPolicy;

class AppServiceProvider extends ServiceProvider
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
        $this->configureDefaults();

        Blade::component('components.button', 'button');
        Blade::component('components.input', 'input');
        Blade::component('components.textarea', 'textarea');
        Blade::component('components.select', 'select');
        Blade::component('components.checkbox', 'checkbox');
        Blade::component('components.radio', 'radio');
        Blade::component('components.switch', 'switch');
        Blade::component('components.card', 'card');
        Blade::component('components.stat-card', 'stat-card');
        Blade::component('components.table', 'table');
        Blade::component('components.pagination', 'pagination');
        Blade::component('components.modal', 'modal');
        Blade::component('components.alert', 'alert');
        Blade::component('components.toast', 'toast');
        Blade::component('components.badge', 'badge');
        Blade::component('components.avatar', 'avatar');
        Blade::component('components.dropdown', 'dropdown');
        Blade::component('components.page-header', 'page-header');
        Blade::component('components.breadcrumb', 'breadcrumb');
        Blade::component('components.empty-state', 'empty-state');
        Blade::component('components.loading', 'loading');
        Blade::component('components.search', 'search');
        Blade::component('components.filter', 'filter');
        Blade::component('components.form.file-upload', 'file-upload');
        Blade::component('components.confirm-dialog', 'confirm-dialog');

        Gate::policy(
            LetterNumberRegistration::class,
            LetterNumberRegistrationPolicy::class
        );

        Gate::policy(
            OutgoingLetter::class,
            OutgoingLetterPolicy::class
        );

        Gate::policy(
            IncomingLetter::class,
            IncomingLetterPolicy::class
        );

        Gate::policy(
            Report::class,
            ReportPolicy::class
        );

        Gate::policy(
            Dashboard::class,
            DashboardPolicy::class
        );

        Gate::policy(
            User::class,
            UserPolicy::class
        );

        Gate::policy(
            Department::class,
            DepartmentPolicy::class
        );

        Gate::policy(
            RegistrationRequest::class,
            RegistrationRequestPolicy::class
        );

        Gate::policy(
            SystemSetting::class,
            SystemSettingPolicy::class
        );

        Gate::policy(
            ActivityLog::class,
            ActivityLogPolicy::class
        );

        Event::listen(Verified::class, LogVerifiedEmail::class);

        View::composer(['pdf.*', 'reports.print', 'exports.*'], function ($view) {
            if (! array_key_exists('reportBranding', $view->getData())) {
                $view->with(
                    'reportBranding',
                    app(SystemConfigurationService::class)->reportBranding()
                );
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
