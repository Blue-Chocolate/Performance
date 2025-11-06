<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController\OrganizationController;
use App\Http\Controllers\Api\AxisResponseController\AxisResponseController;
use App\Http\Controllers\Api\ReleaseController\ReleaseController;
use App\Http\Controllers\Api\PodcastController\PodcastController;
use App\Http\Controllers\Api\PerformanceController\PerformanceController;



use App\Http\Controllers\Api\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('releases', [ReleaseController::class, 'index']);
Route::get('releases/{id}', [ReleaseController::class, 'show']);

Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
  Route::get('podcasts', [PodcastController::class, 'index']);
    Route::get('podcasts/{id}', [PodcastController::class, 'show']);
    
Route::middleware('auth:sanctum')->group(function(){
      Route::post('/organizations', [OrganizationController::class, 'store']);

    // Axes & Responses
    Route::get('/organizations/{org}/axes', [AxisResponseController::class, 'index']);
    Route::get('/organizations/{org}/axes/{axis}', [AxisResponseController::class, 'show']);
    Route::post('/organizations/{org}/axes/{axis}', [AxisResponseController::class, 'storeOrUpdate']);
    Route::get('/organizations/{org}/score', [OrganizationController::class, 'score']);

    // Organization details (keep last)
Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);
        Route::get('/axis-responses/{orgId}/{axisId}', [AxisResponseController::class, 'show']);

    // Create or update response for a specific axis
    Route::post('/axis-responses/{orgId}/{axisId}', [AxisResponseController::class, 'storeOrUpdate']);

   
});
use App\Http\Controllers\Api\PerformanceCertificateController\PerformanceCertificateController;

Route::prefix('certificates')->group(function () {
    
    // ➊ Organization Registration
    Route::post('/', [PerformanceCertificateController::class, 'store']);
    
    // ➋ Get questions by path (strategic, operational, hr)
    Route::get('/questions/{path}', [PerformanceCertificateController::class, 'getQuestionsByPath'])
        ->whereIn('path', ['strategic', 'operational', 'hr']);
    
    // ➌ Submit answers for a certificate
    Route::post('/{certificateId}/answers', [PerformanceCertificateController::class, 'submitAnswers']);
    
    // ➍ Get certificate details with answers
    Route::get('/{id}', [PerformanceCertificateController::class, 'show']);
    
    // ➎ Update answers (for corrections)
    Route::put('/{certificateId}/answers', [PerformanceCertificateController::class, 'updateAnswers']);
    
    // ➏ Delete certificate
    Route::delete('/{id}', [PerformanceCertificateController::class, 'destroy']);
});

use App\Http\Controllers\Api\StrategicPathController;

Route::apiResource('strategic-paths', StrategicPathController::class);

use App\Http\Controllers\Api\BlogController\BlogController;

Route::get('blogs', [BlogController::class, 'index']);
Route::get('blogs/{id}', [BlogController::class, 'show']);