<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnalysisController;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Analysis history
    Route::get('/analyses', [AnalysisController::class, 'index']);
    Route::post('/analyses', [AnalysisController::class, 'store']);
    Route::get('/analyses/{id}', [AnalysisController::class, 'show']);
    Route::delete('/analyses/{id}', [AnalysisController::class, 'destroy']);

    // AI analysis
    Route::post('/analyze', [AnalysisController::class, 'analyze']);

    // Current logged-in user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});