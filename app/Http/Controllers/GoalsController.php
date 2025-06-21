<?php

namespace App\Http\Controllers;

use App\Http\Resources\GoalCollection;
use Illuminate\Support\Facades\Auth;

class GoalsController extends Controller
{
    public function getActiveGoals(): GoalCollection
    {
        $user = Auth::user();
        $activeGoals = $user->goals()->where('status', 'ACTIVE')->get();

        return new GoalCollection($activeGoals);
    }
}
