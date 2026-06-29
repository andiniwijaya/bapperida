<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Notifications\Data\SystemNotificationPayload;
use App\Notifications\SystemNotification;
use App\Notifications\UserCreatedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Central communication layer for dispatching user notifications.
 *
 * Business rules:
 * - Business services call this service; controllers must not notify directly.
 * - Database notifications are default; email used for critical auth flows.
 * - Activity Log remains separate (audit trail vs user communication).
 */
class NotificationService
{
    /**
     * Dispatch a notification to a single user without breaking business flow on failure.
     */
    public function notify(User $user, SystemNotificationPayload $payload): void
    {
        try {
            $user->notify(new SystemNotification($payload));
        } catch (\Throwable $e) {
            Log::warning('Notification delivery failed.', [
                'user_id' => $user->id,
                'module' => $payload->module,
                'action' => $payload->action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  Collection<int, User>|array<int, User>  $users
     */
    public function notifyMany(Collection|array $users, SystemNotificationPayload $payload): void
    {
        foreach ($users as $user) {
            $this->notify($user, $payload);
        }
    }

    public function notifySuperAdmins(SystemNotificationPayload $payload): void
    {
        $users = User::query()
            ->where('role', 'superadmin')
            ->where('status', 'active')
            ->get();

        $this->notifyMany($users, $payload);
    }

    public function notifyAdminsAndSuperAdmins(SystemNotificationPayload $payload): void
    {
        $users = User::query()
            ->whereIn('role', ['superadmin', 'admin'])
            ->where('status', 'active')
            ->get();

        $this->notifyMany($users, $payload);
    }

    /**
     * Notify a newly created user to set their password via Fortify reset link.
     */
    public function userCreated(User $user, string $passwordSetupUrl): void
    {
        $this->passwordSetupInvitation($user, $passwordSetupUrl, 'created');
    }

    /**
     * Send password-setup invitation for account creation, admin reset, or resend flows.
     */
    public function passwordSetupInvitation(User $user, string $passwordSetupUrl, string $scenario = 'created'): void
    {
        try {
            $user->notify(new UserCreatedNotification($passwordSetupUrl, $scenario));
        } catch (\Throwable $e) {
            Log::warning('Password setup notification failed.', [
                'user_id' => $user->id,
                'scenario' => $scenario,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function registrationSubmitted(User $user): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Registrasi Berhasil',
            message: 'Permintaan registrasi akun Anda telah dikirim dan menunggu persetujuan Super Administrator.',
            module: 'auth',
            action: 'registration_submitted',
            url: url('/login'),
        ));

        $this->notifySuperAdmins(new SystemNotificationPayload(
            title: 'Registrasi Pengguna Baru',
            message: sprintf('Pengguna %s mengajukan registrasi akun.', $user->email),
            module: 'auth',
            action: 'registration_submitted',
            url: route('admin.registration-requests.index'),
            metadata: ['user_id' => $user->id],
        ));
    }

    public function emailVerified(User $user): void
    {
        $url = ($user->isPending() || $user->isRejected())
            ? route('register.success')
            : route('dashboard');

        $this->notify($user, new SystemNotificationPayload(
            title: 'Email Terverifikasi',
            message: 'Email akun Anda berhasil diverifikasi.',
            module: 'auth',
            action: 'email_verified',
            url: $url,
        ));
    }

    public function registrationApproved(User $user): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Akun Disetujui',
            message: 'Registrasi akun Anda telah disetujui. Anda dapat login ke sistem.',
            module: 'auth',
            action: 'registration_approved',
            url: url('/login'),
            channels: ['database', 'mail'],
        ));
    }

    public function registrationRejected(User $user, ?string $reason = null): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Akun Ditolak',
            message: $reason
                ? sprintf('Registrasi akun ditolak: %s', $reason)
                : 'Registrasi akun Anda ditolak oleh Super Administrator.',
            module: 'auth',
            action: 'registration_rejected',
            url: url('/login'),
            metadata: ['reason' => $reason],
            channels: ['database', 'mail'],
        ));
    }

    public function passwordChanged(User $user): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Password Diubah',
            message: 'Password akun Anda berhasil diubah.',
            module: 'auth',
            action: 'password_changed',
        ));
    }

    public function passwordReset(User $user): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Password Direset',
            message: 'Password akun Anda berhasil direset.',
            module: 'auth',
            action: 'password_reset',
            channels: ['database', 'mail'],
        ));
    }

    public function userRoleChanged(User $user, string $role): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Role Diperbarui',
            message: sprintf('Role akun Anda diubah menjadi %s.', $role),
            module: 'user',
            action: 'user_role_changed',
            metadata: ['role' => $role],
        ));
    }

    public function userStatusChanged(User $user, string $status): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Status Diperbarui',
            message: sprintf('Status akun Anda diubah menjadi %s.', $status),
            module: 'user',
            action: 'user_status_changed',
            metadata: ['status' => $status],
        ));
    }

    public function departmentCreated(User $actor, string $name, string $code): void
    {
        $this->notifyAdminsAndSuperAdmins(new SystemNotificationPayload(
            title: 'Bidang Dibuat',
            message: sprintf('Bidang %s (%s) berhasil dibuat oleh %s.', $name, $code, $actor->name),
            module: 'department',
            action: 'department_created',
            url: route('admin.departments.index'),
            metadata: ['code' => $code],
        ));
    }

    public function departmentUpdated(User $actor, string $name, string $code): void
    {
        $this->notifyAdminsAndSuperAdmins(new SystemNotificationPayload(
            title: 'Bidang Diperbarui',
            message: sprintf('Data bidang %s (%s) diperbarui oleh %s.', $name, $code, $actor->name),
            module: 'department',
            action: 'department_updated',
            url: route('admin.departments.index'),
        ));
    }

    public function departmentDeleted(User $actor, string $name, string $code): void
    {
        $this->notifyAdminsAndSuperAdmins(new SystemNotificationPayload(
            title: 'Bidang Dihapus',
            message: sprintf('Bidang %s (%s) dihapus oleh %s.', $name, $code, $actor->name),
            module: 'department',
            action: 'department_deleted',
        ));
    }

    public function departmentRestored(User $actor, string $name, string $code): void
    {
        $this->notifyAdminsAndSuperAdmins(new SystemNotificationPayload(
            title: 'Bidang Dipulihkan',
            message: sprintf('Bidang %s (%s) dipulihkan oleh %s.', $name, $code, $actor->name),
            module: 'department',
            action: 'department_restored',
            url: route('admin.departments.index'),
        ));
    }

    public function letterRegistrationCreated(User $user, string $letterNumber): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Registrasi Nomor Surat',
            message: sprintf('Registrasi nomor surat %s berhasil dibuat.', $letterNumber),
            module: 'letter_number_registration',
            action: 'registration_created',
            url: url('/letter-number-registrations'),
            metadata: ['letter_number' => $letterNumber],
        ));
    }

    public function letterRegistrationUpdated(User $user, string $letterNumber): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Registrasi Diperbarui',
            message: sprintf('Registrasi nomor surat %s berhasil diperbarui.', $letterNumber),
            module: 'letter_number_registration',
            action: 'registration_updated',
            metadata: ['letter_number' => $letterNumber],
        ));
    }

    public function letterRegistrationRestored(User $user, string $letterNumber): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Registrasi Dipulihkan',
            message: sprintf('Registrasi nomor surat %s berhasil dipulihkan.', $letterNumber),
            module: 'letter_number_registration',
            action: 'registration_restored',
            metadata: ['letter_number' => $letterNumber],
        ));
    }

    public function incomingLetterCreated(User $user, string $letterNumber): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Surat Masuk Dibuat',
            message: sprintf('Arsip surat masuk %s berhasil dibuat.', $letterNumber),
            module: 'incoming_letter',
            action: 'created',
            url: url('/incoming-letters'),
            metadata: ['letter_number' => $letterNumber],
        ));
    }

    public function incomingLetterUpdated(User $user, string $letterNumber): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Surat Masuk Diperbarui',
            message: sprintf('Arsip surat masuk %s berhasil diperbarui.', $letterNumber),
            module: 'incoming_letter',
            action: 'updated',
            metadata: ['letter_number' => $letterNumber],
        ));
    }

    public function incomingLetterRestored(User $user, string $letterNumber): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Surat Masuk Dipulihkan',
            message: sprintf('Arsip surat masuk %s berhasil dipulihkan.', $letterNumber),
            module: 'incoming_letter',
            action: 'restored',
            metadata: ['letter_number' => $letterNumber],
        ));
    }

    public function outgoingLetterCreated(User $user, int $id): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Surat Keluar Dibuat',
            message: sprintf('Arsip surat keluar (ID %d) berhasil dibuat.', $id),
            module: 'outgoing_letter',
            action: 'created',
            url: url('/outgoing-letters'),
            metadata: ['outgoing_letter_id' => $id],
        ));
    }

    public function outgoingLetterUpdated(User $user, int $id): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Surat Keluar Diperbarui',
            message: sprintf('Arsip surat keluar (ID %d) berhasil diperbarui.', $id),
            module: 'outgoing_letter',
            action: 'updated',
            metadata: ['outgoing_letter_id' => $id],
        ));
    }

    public function outgoingLetterRestored(User $user, int $id): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Surat Keluar Dipulihkan',
            message: sprintf('Arsip surat keluar (ID %d) berhasil dipulihkan.', $id),
            module: 'outgoing_letter',
            action: 'restored',
            metadata: ['outgoing_letter_id' => $id],
        ));
    }

    public function reportExportPdf(User $user, string $reportTypeLabel): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Export PDF Selesai',
            message: sprintf('Laporan %s berhasil diekspor ke PDF.', $reportTypeLabel),
            module: 'report',
            action: 'export_pdf',
            url: url('/reports'),
        ));
    }

    public function reportExportExcel(User $user, string $reportType): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Export Excel Selesai',
            message: sprintf('Laporan %s berhasil diekspor ke Excel.', $reportType),
            module: 'report',
            action: 'export_excel',
            url: url('/reports'),
        ));
    }

    public function reportPrinted(User $user): void
    {
        $this->notify($user, new SystemNotificationPayload(
            title: 'Laporan Dicetak',
            message: 'Laporan berhasil dicetak.',
            module: 'report',
            action: 'print',
            url: url('/reports'),
        ));
    }

    public function systemSettingUpdated(User $actor): void
    {
        $this->notifySuperAdmins(new SystemNotificationPayload(
            title: 'Konfigurasi Diperbarui',
            message: sprintf('Pengaturan sistem diperbarui oleh %s.', $actor->name),
            module: 'system_setting',
            action: 'setting_updated',
            url: route('admin.system-settings.index'),
            metadata: ['updated_by' => $actor->id],
        ));
    }
}
