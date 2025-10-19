<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Models\Ride;
use App\Models\Payment;
use App\Models\Driver;
use App\Http\Controllers\RideExportController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// Role-based dashboards
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Volt::route('admin', 'dashboard-superadmin')->middleware('permission:can.view.superadmin.dashboard')->name('admin.dashboard');
    Volt::route('admin/drivers', 'admin-drivers')->middleware('permission:can.manage.drivers')->name('admin.drivers');
    Volt::route('admin/roles', 'admin-roles')->middleware('permission:can.manage.roles')->name('admin.roles');
    Volt::route('admin/payments', 'admin-payments')->middleware('permission:can.view.payments')->name('admin.payments');

    // PDF export for superadmin
    Route::get('admin/rides/export/pdf', [RideExportController::class, 'adminRidesPdf'])
        ->middleware('permission:can.view.superadmin.dashboard')
        ->name('admin.rides.export.pdf');
});

Route::middleware(['auth', 'role:driver'])->group(function () {
    Volt::route('driver', 'dashboard-driver')->name('driver.dashboard');
    Volt::route('driver/rides', 'driver-rides')->middleware('permission:can.view.assigned.rides')->name('driver.rides');

    // PDF export for driver (disabled)
    // Route::get('driver/rides/export/pdf', [RideExportController::class, 'driverRidesPdf'])
    //     ->middleware('permission:can.view.assigned.rides')
    //     ->name('driver.rides.export.pdf');
});

Route::middleware(['auth', 'role:user'])->group(function () {
    Volt::route('user', 'dashboard-user')->name('user.dashboard');
    Volt::route('user/rides/book', 'user-book-ride')->middleware('permission:can.book.ride')->name('user.rides.book');

    // PDF export for user (disabled)
    // Route::get('user/rides/export/pdf', [RideExportController::class, 'userRidesPdf'])
    //     ->middleware('permission:can.book.ride')
    //     ->name('user.rides.export.pdf');
});

// Payment routes
Route::middleware(['auth', 'permission:can.pay.online'])->group(function () {
    Route::post('paystack/initialize', [\App\Http\Controllers\PaymentController::class, 'initialize'])->name('payment.initialize');
    Route::get('paystack/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
});

require __DIR__.'/auth.php';
