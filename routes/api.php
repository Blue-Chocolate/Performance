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
use App\Http\Controllers\Api\PerformanceCertificateController\PerformanceCertificateController;

Route::prefix('performance-certificates')->group(function () {
    // ➊ Create new certificate
    Route::post('/', [PerformanceCertificateController::class, 'store'])->name('performance-certificates.store');

    // ➋ Get questions by path
    Route::get('/questions/{path}', [PerformanceCertificateController::class, 'getQuestionsByPath'])->name('performance-certificates.questions');

    // ➌ Submit answers (axis-based)
    Route::post('/{certificateId}/answers', [PerformanceCertificateController::class, 'submitAnswers'])->name('performance-certificates.submit-answers');

    // ➍ Submit strategic path answers
    Route::post('/{certificateId}/strategic-answers', [PerformanceCertificateController::class, 'submitStrategicAnswers'])->name('performance-certificates.submit-strategic-answers');

    // ➎ Show final certificate details
    Route::get('/{id}', [PerformanceCertificateController::class, 'show'])->name('performance-certificates.show');

    // ➏ Update strategic path answers
    Route::put('/{id}/strategic-answers', [PerformanceCertificateController::class, 'updateStrategicAnswers'])->name('performance-certificates.update-strategic-answers');

    // ➐ Delete certificate
    Route::delete('/{id}', [PerformanceCertificateController::class, 'destroy'])->name('performance-certificates.destroy');
});

use App\Http\Controllers\Api\StrategicPathController;

Route::apiResource('strategic-paths', StrategicPathController::class);

