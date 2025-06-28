<?php

use App\Models\Comment;
use App\Models\Goal;
use App\Models\User;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->goal = Goal::factory()->create(['user_id' => $this->user->id]);
    Passport::actingAs($this->user);
});

describe('GET /goals/{goalId}/comments', function () {
    it('returns comments for a goal the user owns', function () {
        // Arrange
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);

        // Act
        $response = $this->getJson("/api/goals/{$this->goal->id}/comments");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'user' => ['id', 'name'],
                        'created_at',
                        'updated_at',
                        'replies',
                    ],
                ],
            ]);
    });

    it('returns 403 for a goal the user does not have access to', function () {
        // Arrange
        $otherUser = User::factory()->create();
        $otherGoal = Goal::factory()->create(['user_id' => $otherUser->id]);

        // Act
        $response = $this->getJson("/api/goals/{$otherGoal->id}/comments");

        // Assert
        $response->assertStatus(403);
    });

    it('returns comments with replies one level deep', function () {
        // Arrange
        $rootComment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);
        $reply = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'parent_id' => $rootComment->id,
        ]);

        // Act
        $response = $this->getJson("/api/goals/{$this->goal->id}/comments");

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertCount(1, $data[0]['replies']);
    });
});

describe('POST /goals/{goalId}/comments', function () {
    it('creates a root comment', function () {
        // Arrange
        $payload = [
            'content' => 'Test comment',
        ];

        // Act
        $response = $this->postJson("/api/goals/{$this->goal->id}/comments", $payload);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'user' => ['id', 'name'],
                    'parent_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'content' => 'Test comment',
            'parent_id' => null,
        ]);
    });

    it('creates a reply to a root comment', function () {
        // Arrange
        $rootComment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);
        $payload = [
            'content' => 'Test reply',
            'parent_id' => $rootComment->id,
        ];

        // Act
        $response = $this->postJson("/api/goals/{$this->goal->id}/comments", $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'content' => 'Test reply',
            'parent_id' => $rootComment->id,
        ]);
    });

    it('prevents creating more than one reply to a comment', function () {
        // Arrange
        $rootComment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);
        Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'parent_id' => $rootComment->id,
        ]);
        $payload = [
            'content' => 'Second reply',
            'parent_id' => $rootComment->id,
        ];

        // Act
        $response = $this->postJson("/api/goals/{$this->goal->id}/comments", $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    });

    it('prevents creating a reply to a reply', function () {
        // Arrange
        $rootComment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);
        $reply = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'parent_id' => $rootComment->id,
        ]);
        $payload = [
            'content' => 'Reply to reply',
            'parent_id' => $reply->id,
        ];

        // Act
        $response = $this->postJson("/api/goals/{$this->goal->id}/comments", $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    });

    it('returns 403 for a goal the user does not have access to', function () {
        // Arrange
        $otherUser = User::factory()->create();
        $otherGoal = Goal::factory()->create(['user_id' => $otherUser->id]);
        $payload = [
            'content' => 'Test comment',
        ];

        // Act
        $response = $this->postJson("/api/goals/{$otherGoal->id}/comments", $payload);

        // Assert
        $response->assertStatus(403);
    });

    it('validates required fields', function () {
        // Arrange
        $payload = [];

        // Act
        $response = $this->postJson("/api/goals/{$this->goal->id}/comments", $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    });
});

describe('PATCH /goals/{goalId}/comments/{commentId}', function () {
    it('updates a comment owned by the user', function () {
        // Arrange
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'content' => 'Original content',
        ]);
        $payload = [
            'content' => 'Updated content',
        ];

        // Act
        $response = $this->patchJson("/api/goals/{$this->goal->id}/comments/{$comment->id}", $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    });

    it('returns 403 when updating another user comment', function () {
        // Arrange
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $otherUser->id,
            'goal_id' => $this->goal->id,
        ]);
        $payload = [
            'content' => 'Updated content',
        ];

        // Act
        $response = $this->patchJson("/api/goals/{$this->goal->id}/comments/{$comment->id}", $payload);

        // Assert
        $response->assertStatus(403);
    });

    it('validates required fields', function () {
        // Arrange
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);
        $payload = [];

        // Act
        $response = $this->patchJson("/api/goals/{$this->goal->id}/comments/{$comment->id}", $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    });
});

describe('DELETE /goals/{goalId}/comments/{commentId}', function () {
    it('deletes a comment owned by the user', function () {
        // Arrange
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/goals/{$this->goal->id}/comments/{$comment->id}");

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });

    it('deletes a comment and all its replies', function () {
        // Arrange
        $rootComment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);
        $reply = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'parent_id' => $rootComment->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/goals/{$this->goal->id}/comments/{$rootComment->id}");

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $rootComment->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
    });

    it('deletes a comment and all its descendants to any depth', function () {
        // Arrange
        $rootComment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);
        $reply = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'parent_id' => $rootComment->id,
        ]);
        $replyToReply = Comment::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'parent_id' => $reply->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/goals/{$this->goal->id}/comments/{$rootComment->id}");

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $rootComment->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
        $this->assertDatabaseMissing('comments', ['id' => $replyToReply->id]);
    });

    it('returns 403 when deleting another user comment', function () {
        // Arrange
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $otherUser->id,
            'goal_id' => $this->goal->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/goals/{$this->goal->id}/comments/{$comment->id}");

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    });
});
