<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogPageController;
use App\Http\Controllers\DashboardPageController;
use App\Http\Controllers\DepartmentPageController;
use App\Http\Controllers\IncomingLetterPageController;
use App\Http\Controllers\LetterNumberRegistrationPageController;
use App\Http\Controllers\OutgoingLetterPageController;
use App\Http\Controllers\RegistrationRequestPageController;
use App\Http\Controllers\ReportPageController;
use App\Http\Controllers\SystemSettingPageController;
use App\Http\Controllers\UserPageController;

Route::view('/', 'welcome')->name('home');

Route::view(
    '/register/success',
    'auth.register-success'
)->name('register.success');

Route::view(
    '/password/reset-success',
    'auth.password-reset-success'
)->name('password.reset.success');

Route::middleware([
    'auth',
    'verified',
    'active',
])->group(function () {

    Route::get('dashboard', [DashboardPageController::class, 'index'])
        ->name('dashboard');

    Route::prefix('letter-number-registrations')
        ->name('letter-number-registrations.')
        ->group(function () {

            Route::get('/', [LetterNumberRegistrationPageController::class, 'index'])
                ->name('index');

            Route::get('/create', [LetterNumberRegistrationPageController::class, 'create'])
                ->name('create');

            Route::get('/print', [LetterNumberRegistrationPageController::class, 'print'])
                ->name('print');

            Route::get('/export-pdf', [LetterNumberRegistrationPageController::class, 'exportPdf'])
                ->name('export-pdf');

            Route::get('/{letterNumberRegistration}', [LetterNumberRegistrationPageController::class, 'show'])
                ->name('show');

            Route::get('/{letterNumberRegistration}/edit', [LetterNumberRegistrationPageController::class, 'edit'])
                ->name('edit');
        });

    Route::prefix('outgoing-letters')
        ->name('outgoing-letters.')
        ->group(function () {
            Route::get('/', [OutgoingLetterPageController::class, 'index'])
                ->name('index');

            Route::get('/create', [OutgoingLetterPageController::class, 'create'])
                ->name('create');

            Route::get('/print', [OutgoingLetterPageController::class, 'print'])
                ->name('print');

            Route::get('/export-pdf', [OutgoingLetterPageController::class, 'exportPdf'])
                ->name('export-pdf');

            Route::get('/{outgoingLetter}', [OutgoingLetterPageController::class, 'show'])
                ->name('show');

            Route::get('/{outgoingLetter}/edit', [OutgoingLetterPageController::class, 'edit'])
                ->name('edit');
        });

    Route::prefix('incoming-letters')
        ->name('incoming-letters.')
        ->group(function () {
            Route::get('/', [IncomingLetterPageController::class, 'index'])
                ->name('index');

            Route::get('/create', [IncomingLetterPageController::class, 'create'])
                ->name('create');

            Route::get('/print', [IncomingLetterPageController::class, 'print'])
                ->name('print');

            Route::get('/export-pdf', [IncomingLetterPageController::class, 'exportPdf'])
                ->name('export-pdf');

            Route::get('/{incomingLetter}', [IncomingLetterPageController::class, 'show'])
                ->name('show');

            Route::get('/{incomingLetter}/edit', [IncomingLetterPageController::class, 'edit'])
                ->name('edit');
        });

    Route::prefix('reports')
        ->name('reports.')
        ->group(function () {
            Route::get('/', [ReportPageController::class, 'index'])
                ->name('index');

            Route::get('/print', [ReportPageController::class, 'print'])
                ->name('print');

            Route::get('/export-pdf', [ReportPageController::class, 'exportPdf'])
                ->name('export-pdf');
        });

    Route::prefix('users')
        ->name('admin.users.')
        ->group(function () {
            Route::get('/', [UserPageController::class, 'index'])
                ->name('index');

            Route::get('/create', [UserPageController::class, 'create'])
                ->name('create');

            Route::get('/{user}', [UserPageController::class, 'show'])
                ->name('show');

            Route::get('/{user}/edit', [UserPageController::class, 'edit'])
                ->name('edit');
        });

    Route::prefix('departments')
        ->name('admin.departments.')
        ->group(function () {
            Route::get('/', [DepartmentPageController::class, 'index'])
                ->name('index');

            Route::get('/create', [DepartmentPageController::class, 'create'])
                ->name('create');

            Route::get('/{department}/edit', [DepartmentPageController::class, 'edit'])
                ->name('edit');
        });

    Route::get('registration-requests', [RegistrationRequestPageController::class, 'index'])
        ->name('admin.registration-requests.index');

    Route::prefix('activity-logs')
        ->name('admin.activity-logs.')
        ->group(function () {
            Route::get('/', [ActivityLogPageController::class, 'index'])
                ->name('index');

            Route::get('/{activityLog}', [ActivityLogPageController::class, 'show'])
                ->name('show');
        });

    Route::get('system-settings', [SystemSettingPageController::class, 'index'])
        ->name('admin.system-settings.index');
});

require __DIR__.'/settings.php';
