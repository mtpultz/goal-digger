<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GoalsController extends Controller
{
    public function getActiveGoals(): JsonResponse
    {
        $user = Auth::user();
        $activeGoals = $user->goals()->where('status', 'ACTIVE')->get();

        return response()->json($activeGoals);
    }
}
