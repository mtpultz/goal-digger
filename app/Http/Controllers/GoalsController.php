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
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = $user->goals()->with(['root', 'parent']);

        // If root parameter is true, only show root goals (goals without parent_id)
        if (request()->boolean('root')) {
            $query->whereNull('parent_id');
        }

        $goals = $query->paginate(25);

        return new GoalCollection($goals);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'category' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:goals,id',
        ]);

        $user = Auth::user();

        // If parent_id is provided, verify the parent goal belongs to the user
        if ($request->has('parent_id')) {
            $parentGoal = $user->goals()->findOrFail($request->parent_id);
            $rootId = $parentGoal->root_id ?? $parentGoal->id;
        } else {
            $rootId = null;
        }

        $goal = $user->goals()->create([
            'parent_id' => $request->parent_id,
            'root_id' => $rootId,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->target_date,
            'status' => 'OPEN',
        ]);

        $goal->load(['root', 'parent']);

        return new GoalResource($goal);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $goal = $user->goals()->with(['root', 'parent'])->findOrFail($id);

        // If include_children parameter is present, load child goals
        if (request()->has('include_children')) {
            $goal->load(['children' => function ($query) {
                $query->with(['root', 'parent']);
            }]);
        }

        return new GoalResource($goal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'target_date' => 'sometimes|nullable|date',
            'category' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|required|in:OPEN,ACTIVE,COMPLETE,SKIPPED',
        ]);

        $user = Auth::user();
        $goal = $user->goals()->findOrFail($id);

        // If updating status, use the existing logic
        if ($request->has('status')) {
            return $this->updateStatus($request, $goal);
        }

        // Update other fields
        $goal->update([
            'title' => $request->get('title', $goal->title),
            'description' => $request->get('description', $goal->description),
            'due_date' => $request->get('target_date', $goal->due_date),
        ]);

        $goal->load(['root', 'parent']);

        return new GoalResource($goal);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $goal = $user->goals()->findOrFail($id);

        $goal->delete();

        return response()->json(['message' => 'Goal deleted successfully']);
    }

    public function getActiveGoals(): GoalCollection
    {
        $user = Auth::user();
        $activeGoals = $user->goals()
            ->with(['root', 'parent'])
            ->withCount('comments')
            ->where('status', 'ACTIVE')
            ->paginate(25);

        return new GoalCollection($activeGoals);
    }

    /**
     * Return the entire goal hierarchy for a root goal.
     */
    public function getGoalHierarchy($id)
    {
        $user = Auth::user();
        $goal = $user->goals()->findOrFail($id);

        if ($goal->parent_id !== null) {
            return response()->json([
                'error' => 'Goal is not a root goal.',
            ], 400);
        }

        // Recursively load all descendants
        $goal->load(['children' => function ($query) {
            $query->with('children');
        }]);

        return new GoalResource($goal);
    }

    /**
     * Update the status of a goal.
     */
    protected function updateStatus(Request $request, Goal $goal)
    {
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
        $goal->load(['root', 'parent']);

        return new GoalResource($goal);
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
