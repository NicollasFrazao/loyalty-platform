<?php

declare(strict_types=1);

use App\Http\Controllers\BackOffice\ModuleController;
use App\Http\Controllers\BackOffice\SessionController;
use App\Http\Controllers\BackOffice\StaffController;
use App\Http\Controllers\BackOffice\StaffRemovalController;
use Illuminate\Support\Facades\Route;

// An id in a URL is a positive integer, so the identifiers of the domain accept it.
Route::pattern('member', '[1-9][0-9]{0,18}');

Route::view('/', 'welcome')->name('home');

// The back office (L1): staff members sign in, and every page acts on their own company. No route
// names a company.
Route::middleware('guest')->group(static function (): void {
    Route::get('/back-office/login', [SessionController::class, 'create'])->name('login');
    Route::post('/back-office/login', [SessionController::class, 'store']);
});

Route::middleware('auth')->prefix('back-office')->group(static function (): void {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/', [ModuleController::class, 'index'])->name('back-office');
    Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('back-office.modules');
    Route::get('/staff', [StaffController::class, 'index'])->name('back-office.staff');
    Route::post('/staff', [StaffController::class, 'store'])->name('back-office.staff.store');
    Route::delete('/staff/{member}', [StaffRemovalController::class, 'destroy'])->name('back-office.staff.destroy');
});
