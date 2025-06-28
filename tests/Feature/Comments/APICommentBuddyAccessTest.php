<?php

use App\Models\BuddyGoal;
use App\Models\Comment;
use App\Models\Goal;
use App\Models\User;
use Laravel\Passport\Passport;

describe('Comment access for buddies', function () {
    it('allows buddies to view comments on goals they have access to', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        BuddyGoal::factory()->create([
            'goal_id' => $goal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'ACCEPTED',
        ]);
        Comment::factory()->create([
            'user_id' => $goalOwner->id,
            'goal_id' => $goal->id,
        ]);
        Passport::actingAs($buddy);

        // Act
        $response = $this->getJson("/api/goals/{$goal->id}/comments");

        // Assert
        $response->assertStatus(200);
    });

    it('allows buddies to create comments on goals they have access to', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        BuddyGoal::factory()->create([
            'goal_id' => $goal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'ACCEPTED',
        ]);
        Passport::actingAs($buddy);
        $payload = [
            'content' => 'Comment from buddy',
        ];

        // Act
        $response = $this->postJson("/api/goals/{$goal->id}/comments", $payload);

        // Assert
        $response->assertStatus(201);
    });

    it('allows buddies to access comments on child goals if they have access to parent', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $parentGoal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        $childGoal = Goal::factory()->create([
            'user_id' => $goalOwner->id,
            'parent_id' => $parentGoal->id,
        ]);
        BuddyGoal::factory()->create([
            'goal_id' => $parentGoal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'ACCEPTED',
        ]);
        Comment::factory()->create([
            'user_id' => $goalOwner->id,
            'goal_id' => $childGoal->id,
        ]);
        Passport::actingAs($buddy);

        // Act
        $response = $this->getJson("/api/goals/{$childGoal->id}/comments");

        // Assert
        $response->assertStatus(200);
    });

    it('allows buddies to create comments on child goals if they have access to parent', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $parentGoal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        $childGoal = Goal::factory()->create([
            'user_id' => $goalOwner->id,
            'parent_id' => $parentGoal->id,
        ]);
        BuddyGoal::factory()->create([
            'goal_id' => $parentGoal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'ACCEPTED',
        ]);
        Passport::actingAs($buddy);
        $payload = [
            'content' => 'Comment from buddy on child goal',
        ];

        // Act
        $response = $this->postJson("/api/goals/{$childGoal->id}/comments", $payload);

        // Assert
        $response->assertStatus(201);
    });

    it('denies access to pending buddy requests', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        BuddyGoal::factory()->create([
            'goal_id' => $goal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'PENDING',
        ]);
        Passport::actingAs($buddy);

        // Act
        $response = $this->getJson("/api/goals/{$goal->id}/comments");

        // Assert
        $response->assertStatus(403);
    });

    it('denies access to rejected buddy requests', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        BuddyGoal::factory()->create([
            'goal_id' => $goal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'REJECTED',
        ]);
        Passport::actingAs($buddy);

        // Act
        $response = $this->getJson("/api/goals/{$goal->id}/comments");

        // Assert
        $response->assertStatus(403);
    });

    it('allows buddies to update their own comments', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        BuddyGoal::factory()->create([
            'goal_id' => $goal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'ACCEPTED',
        ]);
        $comment = Comment::factory()->create([
            'user_id' => $buddy->id,
            'goal_id' => $goal->id,
        ]);
        Passport::actingAs($buddy);
        $payload = [
            'content' => 'Updated comment',
        ];

        // Act
        $response = $this->patchJson("/api/goals/{$goal->id}/comments/{$comment->id}", $payload);

        // Assert
        $response->assertStatus(200);
    });

    it('allows buddies to delete their own comments', function () {
        // Arrange
        $goalOwner = User::factory()->create();
        $buddy = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $goalOwner->id]);
        BuddyGoal::factory()->create([
            'goal_id' => $goal->id,
            'user_id' => $goalOwner->id,
            'buddy_id' => $buddy->id,
            'status' => 'ACCEPTED',
        ]);
        $comment = Comment::factory()->create([
            'user_id' => $buddy->id,
            'goal_id' => $goal->id,
        ]);
        Passport::actingAs($buddy);

        // Act
        $response = $this->deleteJson("/api/goals/{$goal->id}/comments/{$comment->id}");

        // Assert
        $response->assertStatus(200);
    });
});
