<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController\OrganizationController;
use App\Http\Controllers\Api\AxisResponseController\AxisResponseController;


Route::middleware('auth:sanctum')->group(function(){
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::get('/organizations/{org}', [OrganizationController::class, 'show']);
    Route::get('/organizations/{org}/axes', [AxisResponseController::class, 'index']);
    Route::get('/organizations/{org}/axes/{axis}', [AxisResponseController::class, 'show']);
    Route::post('/organizations/{org}/axes/{axis}', [AxisResponseController::class, 'storeOrUpdate']);
    Route::get('/organizations/{org}/score', [OrganizationController::class, 'score']);
});
