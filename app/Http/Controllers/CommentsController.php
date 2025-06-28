<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CommentsController extends Controller
{
    private const MAX_CONTENT_LENGTH = 1000;

    /**
     * Get all comments for a goal.
     */
    public function index(Request $request, int $goalId): JsonResponse
    {
        $user = Auth::user();
        $goal = Goal::findOrFail($goalId);

        // Check if user has access to this goal
        if (! $this->userHasAccessToGoal($user, $goal)) {
            return $this->unauthorizedResponse();
        }

        // Get root comments with their replies (one level deep)
        $comments = $goal->rootComments()
            ->with(['user', 'children.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->commentsIndexResponse($comments);
    }

    /**
     * Create a new comment.
     */
    public function store(Request $request, int $goalId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:' . self::MAX_CONTENT_LENGTH,
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $user = Auth::user();
        $goal = Goal::findOrFail($goalId);

        // Check if user has access to this goal
        if (! $this->userHasAccessToGoal($user, $goal)) {
            return $this->unauthorizedResponse();
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

        return $this->commentResponse($comment, Response::HTTP_CREATED);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, int $goalId, int $commentId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:' . self::MAX_CONTENT_LENGTH,
        ]);

        $user = Auth::user();
        $comment = Comment::where('id', $commentId)
            ->where('goal_id', $goalId)
            ->firstOrFail();

        // Check if user owns the comment
        if ($comment->user_id !== $user->id) {
            return $this->unauthorizedResponse();
        }

        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load('user');

        return $this->commentResponse($comment);
    }

    /**
     * Delete a comment.
     */
    public function destroy(int $goalId, int $commentId): JsonResponse
    {
        $user = Auth::user();
        $comment = Comment::where('id', $commentId)
            ->where('goal_id', $goalId)
            ->firstOrFail();

        // Check if user owns the comment
        if ($comment->user_id !== $user->id) {
            return $this->unauthorizedResponse();
        }

        // Delete the comment and all its descendants (cascade delete handles this automatically)
        // but we'll also explicitly delete descendants for clarity
        $this->deleteCommentAndDescendants($comment);

        return $this->deleteSuccessResponse();
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
    private function getAllDescendants(Comment $comment): Collection
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
    private function userHasAccessToGoal($user, Goal $goal): bool
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

    /**
     * Response helper methods
     */
    private function commentsIndexResponse(Collection $comments): JsonResponse
    {
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

    private function commentResponse(Comment $comment, int $statusCode = Response::HTTP_OK): JsonResponse
    {
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
        ], $statusCode);
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
    }

    private function deleteSuccessResponse(): JsonResponse
    {
        return response()->json(['message' => 'Comment and all replies deleted successfully']);
    }
}
