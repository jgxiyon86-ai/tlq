<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LicenseController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\ManualController;
use App\Http\Controllers\Api\V1\ChallengeController;

Route::group(['prefix' => 'v1'], function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/google', [AuthController::class, 'googleLogin']);
    Route::post('/login/facebook', [AuthController::class, 'facebookLogin']);
    Route::post('/login/phone', [AuthController::class, 'phoneLogin']);
    Route::get('/manual/{series_id}', [ManualController::class, 'getBySeries']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/profile/update', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        
        // License management
        Route::get('/licenses', [LicenseController::class, 'index']);
        Route::post('/licenses/activate', [LicenseController::class, 'activate']);
        Route::post('/licenses/release', [LicenseController::class, 'release']);

        // Content
        Route::get('/shake', [ContentController::class, 'shake']);

        // Challenge & Journal
        Route::get('/challenges', [ChallengeController::class, 'index']);
        Route::post('/challenges/activate', [ChallengeController::class, 'activate']);
        Route::post('/challenges/{challenge}/roll', [ChallengeController::class, 'rollContent']);
        Route::get('/challenges/{challenge}/history', [ChallengeController::class, 'history']);
        Route::post('/journal/{entry}/before', [ChallengeController::class, 'saveBefore']);
        Route::post('/journal/{entry}/after', [ChallengeController::class, 'saveAfter']);
        Route::post('/challenges/{challenge}/reflections', [ChallengeController::class, 'saveFinalReflections']);
    });
});
