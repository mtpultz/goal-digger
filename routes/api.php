<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\GoalsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes (no middleware required)
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('auth/resend-verification', [AuthController::class, 'resendVerification']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::get('goals/active', [GoalsController::class, 'getActiveGoals']);
    Route::patch('goals/{id}', [GoalsController::class, 'update']);

    // Comment routes
    Route::get('goals/{goalId}/comments', [CommentsController::class, 'index']);
    Route::post('goals/{goalId}/comments', [CommentsController::class, 'store']);
    Route::patch('goals/{goalId}/comments/{commentId}', [CommentsController::class, 'update']);
    Route::delete('goals/{goalId}/comments/{commentId}', [CommentsController::class, 'destroy']);
});
