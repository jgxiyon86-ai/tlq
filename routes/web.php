<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\ManualPageController;

Route::get('/', function () { return redirect()->route('admin.dashboard'); });

Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Monitoring Challenges
    Route::get('/monitoring/challenges',                [AdminController::class, 'monitoringChallenges'])->name('monitoring.challenges');
    Route::post('/monitoring/challenges',               [AdminController::class, 'storeChallenge'])->name('monitoring.challenges.store');
    Route::delete('/monitoring/challenges/{challenge}', [AdminController::class, 'destroyChallenge'])->name('monitoring.challenges.destroy');

    // AJAX endpoints (lazy load)
    Route::get('/ajax/users/search',                    [AdminController::class, 'searchUsers'])->name('ajax.users.search');
    Route::get('/ajax/users/{user}/challenges',         [AdminController::class, 'userChallenges'])->name('ajax.user.challenges');
    Route::get('/ajax/challenges/{challenge}/journals', [AdminController::class, 'challengeJournals'])->name('ajax.challenge.journals');

    // Monitoring & Licenses
    Route::get('/monitoring/licenses',    [AdminController::class, 'monitoringLicenses'])->name('monitoring.licenses');
    Route::get('/profile/password',       [AdminController::class, 'profilePassword'])->name('profile.password');
    Route::post('/profile/password',      [AdminController::class, 'updateProfilePassword'])->name('profile.password.update');
    Route::get('/licenses',               [AdminController::class, 'licenses'])->name('licenses');
    Route::post('/licenses/generate',     [AdminController::class, 'generateLicenses'])->name('licenses.generate');
    Route::get('/licenses/print/{id}',    [AdminController::class, 'printLicense'])->name('licenses.print');
    Route::post('/licenses/bulk-print',   [AdminController::class, 'bulkPrint'])->name('licenses.bulk-print');

    // Contents & Guides
    Route::resource('contents', ContentController::class);
    Route::resource('manual-pages', ManualPageController::class);

    // User Management (enforced super_admin in controller)
    Route::get('/users',                  [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}',           [UserController::class,  'show'])->name('users.show');
    Route::post('/users/{user}/promote',  [AdminController::class, 'promoteAdmin'])->name('users.promote');
    Route::post('/users/{user}/password', [AdminController::class, 'updateUserPassword'])->name('users.password');
});
