<?php

namespace App\Http\Controllers;

use App\Http\Resources\GoalCollection;
use Illuminate\Support\Facades\Auth;

class GoalsController extends Controller
{
    public function getActiveGoals(): GoalCollection
    {
        $user = Auth::user();
        $activeGoals = $user->goals()->with(['root', 'parent'])->where('status', 'ACTIVE')->paginate(25);

        return new GoalCollection($activeGoals);
    }
}
