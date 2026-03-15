<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\ManualPageController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('monitoring');
    Route::get('/licenses', [AdminController::class, 'licenses'])->name('licenses');
    Route::post('/licenses/generate', [AdminController::class, 'generateLicenses'])->name('licenses.generate');
    Route::get('/licenses/print/{id}', [AdminController::class, 'printLicense'])->name('licenses.print');
    Route::post('/licenses/bulk-print', [AdminController::class, 'bulkPrint'])->name('licenses.bulk-print');
    
    Route::resource('contents', ContentController::class);
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::resource('manual-pages', ManualPageController::class);
});
