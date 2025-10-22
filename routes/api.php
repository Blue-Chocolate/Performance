<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController\OrganizationController;
use App\Http\Controllers\Api\AxisResponseController\AxisResponseController;
use App\Http\Controllers\Api\ReleaseController\ReleaseController;
use App\Http\Controllers\Api\PodcastController\PodcastController;
use App\Http\Controllers\Api\PerformanceCertificateController\PerformanceCertificateController;
use App\Http\Controllers\Api\PerformanceController\PerformanceController;



use App\Http\Controllers\Api\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('releases', [ReleaseController::class, 'index']);

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

     Route::get('podcasts', [PodcastController::class, 'index']);
    Route::post('podcasts', [PodcastController::class, 'store']);
    Route::get('podcasts/{id}', [PodcastController::class, 'show']);
});
 Route::prefix('certificates')->group(function () {
    Route::post('/', [PerformanceCertificateController::class, 'store']); // إنشاء شهادة جديدة
    Route::get('/questions/{path}', [PerformanceCertificateController::class, 'getQuestionsByPath']); // جلب الأسئلة حسب المسار
    Route::post('/{id}/answers', [PerformanceCertificateController::class, 'submitAnswers']); // إرسال الإجابات
    Route::get('/{id}', [PerformanceCertificateController::class, 'show']); // عرض شهادة معينة


Route::get('/questions/{path}', [PerformanceCertificateController::class, 'getQuestionsByPath']);
Route::post('/answers', [PerformanceController::class, 'submitAnswers']);
});