<?php

use App\Http\Controllers\Admin\BedController;
use App\Http\Controllers\Admin\BlockController;
use App\Http\Controllers\Admin\FeeStructureController;
use App\Http\Controllers\Admin\FloorController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\HostelController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RoomAllocationController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Phase 2 — Hostel structure (admin + warden only)
    Route::middleware('role:admin,warden')->group(function () {
        Route::resource('hostels', HostelController::class)->except(['show']);
        Route::resource('blocks', BlockController::class)->except(['show']);
        Route::resource('floors', FloorController::class)->except(['show']);
        Route::resource('rooms', RoomController::class)->except(['show']);

        Route::get('/beds', [BedController::class, 'index'])->name('beds.index');
        Route::post('/beds', [BedController::class, 'store'])->name('beds.store');
        Route::patch('/beds/{bed}/status', [BedController::class, 'updateStatus'])->name('beds.status');
        Route::delete('/beds/{bed}', [BedController::class, 'destroy'])->name('beds.destroy');
    });

    // Phase 3 — Students, Guardians, Room Allocation (admin + warden only)
    Route::middleware('role:admin,warden')->group(function () {
        Route::resource('students', StudentController::class);

        Route::post('/students/{student}/guardians', [GuardianController::class, 'store'])->name('guardians.store');
        Route::delete('/guardians/{guardian}', [GuardianController::class, 'destroy'])->name('guardians.destroy');

        Route::get('/room-allocations', [RoomAllocationController::class, 'index'])->name('room-allocations.index');
        Route::get('/room-allocations/create', [RoomAllocationController::class, 'create'])->name('room-allocations.create');
        Route::post('/room-allocations', [RoomAllocationController::class, 'store'])->name('room-allocations.store');
        Route::post('/room-allocations/{allocation}/transfer', [RoomAllocationController::class, 'transfer'])->name('room-allocations.transfer');
        Route::post('/room-allocations/{allocation}/checkout', [RoomAllocationController::class, 'checkout'])->name('room-allocations.checkout');
        Route::get('/rooms/{room}/available-beds', [RoomAllocationController::class, 'availableBeds'])->name('rooms.available-beds');
    });

    // Phase 4 — Fee & Payment Management (admin + warden only)
    Route::middleware('role:admin,warden')->group(function () {
        Route::resource('fee-structures', FeeStructureController::class)->except(['show']);

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });
});