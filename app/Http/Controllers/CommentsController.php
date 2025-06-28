<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CommentsController extends Controller
{
    /**
     * Get all comments for a goal.
     */
    public function index(Request $request, $goalId)
    {
        $user = Auth::user();
        $goal = Goal::findOrFail($goalId);

        // Check if user has access to this goal
        if (! $this->userHasAccessToGoal($user, $goal)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get root comments with their replies (one level deep)
        $comments = $goal->rootComments()
            ->with(['user', 'children.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ],
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'replies' => $comment->children->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                            ],
                            'created_at' => $reply->created_at,
                            'updated_at' => $reply->updated_at,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Create a new comment.
     */
    public function store(Request $request, $goalId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $user = Auth::user();
        $goal = Goal::findOrFail($goalId);

        // Check if user has access to this goal
        if (! $this->userHasAccessToGoal($user, $goal)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If parent_id is provided, validate it's a root comment for this goal
        if ($request->has('parent_id')) {
            $parentComment = Comment::where('id', $request->parent_id)
                ->where('goal_id', $goalId)
                ->whereNull('parent_id') // Ensure it's a root comment
                ->first();

            if (! $parentComment) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Parent comment must be a root comment for this goal.',
                ]);
            }
        }

        $comment = Comment::create([
            'user_id' => $user->id,
            'goal_id' => $goalId,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ],
        ], 201);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, $goalId, $commentId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $comment = Comment::where('id', $commentId)
            ->where('goal_id', $goalId)
            ->firstOrFail();

        // Check if user owns the comment
        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy($goalId, $commentId)
    {
        $user = Auth::user();
        $comment = Comment::where('id', $commentId)
            ->where('goal_id', $goalId)
            ->firstOrFail();

        // Check if user owns the comment
        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the comment and all its descendants (cascade delete handles this automatically)
        // but we'll also explicitly delete descendants for clarity
        $this->deleteCommentAndDescendants($comment);

        return response()->json(['message' => 'Comment and all replies deleted successfully']);
    }

    /**
     * Recursively delete a comment and all its descendants.
     */
    private function deleteCommentAndDescendants(Comment $comment): void
    {
        // Get all descendants first
        $descendants = $this->getAllDescendants($comment);

        // Delete descendants first (to avoid foreign key constraint issues)
        foreach ($descendants as $descendant) {
            $descendant->delete();
        }

        // Then delete the comment itself
        $comment->delete();
    }

    /**
     * Get all descendants of a comment (children, grandchildren, etc.).
     */
    private function getAllDescendants(Comment $comment): \Illuminate\Support\Collection
    {
        $descendants = collect();

        // Get immediate children
        $children = $comment->children;

        foreach ($children as $child) {
            $descendants->push($child);
            // Recursively get descendants of this child
            $descendants = $descendants->merge($this->getAllDescendants($child));
        }

        return $descendants;
    }

    /**
     * Check if user has access to a goal (owner or buddy).
     */
    private function userHasAccessToGoal($user, $goal): bool
    {
        // User owns the goal
        if ($goal->user_id === $user->id) {
            return true;
        }

        // Check if user is a buddy for this goal
        $buddyGoal = $goal->acceptedBuddyGoals()
            ->where('buddy_id', $user->id)
            ->first();
        if ($buddyGoal) {
            return true;
        }

        // Check if user is a buddy for any parent goals
        $currentGoal = $goal;
        while ($currentGoal->parent) {
            $currentGoal = $currentGoal->parent;
            $buddyGoal = $currentGoal->acceptedBuddyGoals()
                ->where('buddy_id', $user->id)
                ->first();
            if ($buddyGoal) {
                return true;
            }
        }

        return false;
    }
}
