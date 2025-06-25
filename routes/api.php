<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoalsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes (no middleware required)
Route::post('auth/register', [AuthController::class, 'register']);

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::get('goals/active', [GoalsController::class, 'getActiveGoals']);
    Route::patch('goals/{id}', [GoalsController::class, 'update']);
});
