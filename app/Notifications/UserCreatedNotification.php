<?php

namespace App\Notifications;

use App\Services\SystemSetting\SystemConfigurationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail and database notification for admin-driven account password onboarding.
 *
 * Triggered by: StoreUserService, ResetUserPasswordService, ResendPasswordSetupEmailService.
 */
class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $passwordSetupUrl  Fortify password reset URL with broker token.
     * @param  string  $scenario  created|reset|resent
     */
    public function __construct(
        protected string $passwordSetupUrl,
        protected string $scenario = 'created',
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->databaseTitle(),
            'message' => $this->databaseMessage(),
            'module' => 'user',
            'action' => $this->databaseAction(),
            'url' => $this->passwordSetupUrl,
            'metadata' => [
                'username' => $notifiable->username,
                'role' => $notifiable->role,
                'scenario' => $this->scenario,
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $branding = app(SystemConfigurationService::class)->emailBranding();
        $roleLabel = $this->roleLabel($notifiable->role);

        return (new MailMessage)
            ->subject($this->mailSubject($branding['app_name']))
            ->markdown('mail.user-account-created', [
                'notifiable' => $notifiable,
                'passwordSetupUrl' => $this->passwordSetupUrl,
                'roleLabel' => $roleLabel,
                'scenario' => $this->scenario,
                'appName' => $branding['app_name'],
                'institutionName' => $branding['institution_name'],
                'institutionLogoUrl' => $branding['institution_logo_url'],
                'kabBandungLogoUrl' => $branding['kab_bandung_logo_url'],
                'bapperidaLogoUrl' => $branding['bapperida_logo_url'],
            ]);
    }

    private function databaseTitle(): string
    {
        return match ($this->scenario) {
            'reset' => 'Atur Kata Sandi Diperlukan',
            'resent' => 'Email Atur Kata Sandi Dikirim Ulang',
            default => 'Akun Anda Telah Dibuat',
        };
    }

    private function databaseMessage(): string
    {
        return match ($this->scenario) {
            'reset' => 'Password akun Anda direset. Silakan atur kata sandi melalui email yang telah dikirim.',
            'resent' => 'Silakan atur kata sandi melalui email yang telah dikirim.',
            default => 'Akun Anda telah dibuat. Silakan atur kata sandi melalui email yang telah dikirim.',
        };
    }

    private function databaseAction(): string
    {
        return match ($this->scenario) {
            'reset' => 'password_setup_reset',
            'resent' => 'password_setup_resent',
            default => 'user_created',
        };
    }

    private function mailSubject(string $appName): string
    {
        return match ($this->scenario) {
            'reset' => sprintf('Atur Kata Sandi — %s', $appName),
            'resent' => sprintf('Kirim Ulang Atur Kata Sandi — %s', $appName),
            default => sprintf('Akun Berhasil Dibuat — %s', $appName),
        };
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'staff' => 'Staff',
            default => ucfirst($role),
        };
    }
}
