<?php

namespace App\Http\Controllers;

use App\Http\Resources\GoalCollection;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoalsController extends Controller
{
    /**
     * Update the status of a goal.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:OPEN,ACTIVE,COMPLETE,SKIPPED',
        ]);

        $user = Auth::user();
        $goal = Goal::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $newStatus = $request->input('status');
        $oldStatus = $goal->status;

        DB::transaction(function () use ($goal, $newStatus, $oldStatus) {
            $goal->status = $newStatus;
            $goal->save();

            // If status changed from SKIPPED or COMPLETE to OPEN or ACTIVE, and parent is COMPLETE, set parent to OPEN and bubble up
            if (in_array($oldStatus, ['SKIPPED', 'COMPLETE']) && in_array($newStatus, ['OPEN', 'ACTIVE'])) {
                $parent = $goal->parent;
                while ($parent && $parent->status === 'COMPLETE') {
                    $parent->status = 'OPEN';
                    $parent->save();
                    $parent = $parent->parent;
                }
            }

            if (in_array($newStatus, ['COMPLETE', 'SKIPPED'])) {
                // Only find and activate a sibling if the goal has a parent
                if ($goal->parent) {
                    $openSibling = $goal->parent->children()
                        ->where('status', 'OPEN')
                        ->orderBy('id')
                        ->first();
                    if ($openSibling) {
                        $openSibling->status = 'ACTIVE';
                        $openSibling->save();
                    }
                }
            }

            if ($newStatus === 'COMPLETE') {
                // Only bubble complete if there are no OPEN or ACTIVE siblings
                if ($goal->parent) {
                    $openOrActiveSiblings = $goal->parent->children()
                        ->whereIn('status', ['OPEN', 'ACTIVE'])
                        ->count();
                    if ($openOrActiveSiblings === 0) {
                        $this->bubbleComplete($goal->parent);
                    }
                } else {
                    // If no parent, do not bubble
                }
            }
        });

        $goal->refresh();

        return new GoalResource($goal);
    }

    public function getActiveGoals(): GoalCollection
    {
        $user = Auth::user();
        $activeGoals = $user->goals()->with(['root', 'parent'])->where('status', 'ACTIVE')->paginate(25);

        return new GoalCollection($activeGoals);
    }

    /**
     * Recursively mark parent as COMPLETE if all siblings are COMPLETE or SKIPPED.
     */
    protected function bubbleComplete($parent)
    {
        while ($parent) {
            $openSiblings = $parent->children()->where('status', 'OPEN')->count();
            $activeSiblings = $parent->children()->where('status', 'ACTIVE')->count();
            if ($openSiblings === 0 && $activeSiblings === 0) {
                $parent->status = 'COMPLETE';
                $parent->save();
                $parent = $parent->parent;
            } else {
                break;
            }
        }
    }
}
