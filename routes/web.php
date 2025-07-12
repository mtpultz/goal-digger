<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Email verification route
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

// Serve React app for all other routes (excluding API routes)
Route::get('{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
