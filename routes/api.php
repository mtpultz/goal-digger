<?php

use App\Http\Controllers\GoalsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('goals/active', [GoalsController::class, 'getActiveGoals'])->middleware('auth:api');

Route::patch('goals/{id}', [GoalsController::class, 'update'])->middleware('auth:api');
