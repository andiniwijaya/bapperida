<?php

use App\Http\Controllers\Api\ActivityLog\ActivityLogController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Department\DepartmentController;
use App\Http\Controllers\Api\IncomingLetterController;
use App\Http\Controllers\Api\LetterNumberRegistrationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OutgoingLetterController;
use App\Http\Controllers\Api\Registration\RegistrationApprovalController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SystemSetting\SystemSettingController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');

    Route::get('/me', [AuthController::class, 'me'])
        ->middleware(['auth:sanctum', 'active']);

    Route::patch('/profile', [AuthController::class, 'updateProfile'])
        ->middleware(['auth:sanctum', 'active']);

    Route::patch('/password', [AuthController::class, 'changePassword'])
        ->middleware(['auth:sanctum', 'active']);

});

Route::middleware([
    'auth:sanctum',
    'active',
    'role:superadmin,admin',
])->group(function () {
    Route::get('departments', [DepartmentController::class, 'index']);
    Route::get('departments/{department}', [DepartmentController::class, 'show']);

    Route::apiResource('users', UserController::class);
    Route::patch('users/{id}/restore', [UserController::class, 'restore']);
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::patch('users/{user}/resend-password-setup', [UserController::class, 'resendPasswordSetup']);
});

Route::middleware([
    'auth:sanctum',
    'active',
    'role:superadmin',
])->group(function () {

    Route::get(
        '/registration-requests',
        [RegistrationApprovalController::class, 'index']
    );

    Route::patch(
        '/registration-requests/{registrationRequest}/approve',
        [RegistrationApprovalController::class, 'approve']
    );

    Route::patch(
        '/registration-requests/{registrationRequest}/reject',
        [RegistrationApprovalController::class, 'reject']
    );

    Route::apiResource(
        'departments',
        DepartmentController::class
    )->except(['index', 'show']);

    Route::patch(
        '/departments/{id}/restore',
        [DepartmentController::class, 'restore']
    );

    Route::patch(
        '/users/{user}/role',
        [UserController::class, 'changeRole']
    );

    Route::patch(
        '/users/{user}/status',
        [UserController::class, 'changeStatus']
    );

});

Route::middleware([
    'auth:sanctum',
    'active',
])->name('api.')->group(function () {

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{notificationId}', [NotificationController::class, 'destroy']);

    Route::get(
        'letter-number-registrations/create',
        [LetterNumberRegistrationController::class, 'create']
    );

    Route::get(
        'letter-number-registrations/preview',
        [LetterNumberRegistrationController::class, 'preview']
    );

    Route::get(
        'letter-number-registrations/filters',
        [LetterNumberRegistrationController::class, 'filters']
    );

    Route::get(
        'outgoing-letters/create',
        [OutgoingLetterController::class, 'create']
    );

    Route::get(
        'outgoing-letters/filters',
        [OutgoingLetterController::class, 'filters']
    );

    Route::get(
        'outgoing-letters/{outgoingLetter}/download',
        [OutgoingLetterController::class, 'downloadFile']
    );

    Route::get(
        'outgoing-letters/export-excel',
        [OutgoingLetterController::class, 'exportExcel']
    );

    Route::get(
        'incoming-letters/create-init',
        [IncomingLetterController::class, 'filters']
    );

    Route::get(
        'incoming-letters/filters',
        [IncomingLetterController::class, 'filters']
    );

    Route::get(
        'incoming-letters/{incomingLetter}/download',
        [IncomingLetterController::class, 'downloadFile']
    );

    Route::get(
        'incoming-letters/export-excel',
        [IncomingLetterController::class, 'exportExcel']
    );

    Route::get(
        'reports',
        [ReportController::class, 'index']
    );

    Route::get(
        'reports/filters',
        [ReportController::class, 'filters']
    );

    Route::get(
        'reports/statistics',
        [ReportController::class, 'statistics']
    );

    Route::get(
        'dashboard',
        [DashboardController::class, 'index']
    );

    Route::get(
        'reports/export-excel',
        [ReportController::class, 'exportExcel']
    );

    Route::apiResource(
        'outgoing-letters',
        OutgoingLetterController::class
    )->except(['create']);

    Route::apiResource(
        'incoming-letters',
        IncomingLetterController::class
    )->except(['create']);

    Route::post(
        'incoming-letters/{id}/restore',
        [IncomingLetterController::class, 'restore']
    );

    Route::post(
        'outgoing-letters/{id}/restore',
        [OutgoingLetterController::class, 'restore']
    );

    Route::post(
        'letter-number-registrations/{id}/restore',
        [LetterNumberRegistrationController::class, 'restore']
    );

    Route::apiResource(
        'letter-number-registrations',
        LetterNumberRegistrationController::class
    )->except(['create']);

    Route::get(
        'system-settings',
        [SystemSettingController::class, 'show']
    );

    Route::patch(
        'system-settings',
        [SystemSettingController::class, 'update']
    );

    Route::get(
        'activity-logs',
        [ActivityLogController::class, 'index']
    );

    Route::get(
        'activity-logs/export-excel',
        [ActivityLogController::class, 'exportExcel']
    );

    Route::get(
        'activity-logs/{activityLog}',
        [ActivityLogController::class, 'show']
    );

});
