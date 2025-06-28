<?php

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

test('should change status from active to complete and activate sibling', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    $parent = Goal::factory()->create(['user_id' => $user->id]);
    $goal1 = Goal::factory()->create(['user_id' => $user->id, 'parent_id' => $parent->id, 'status' => 'ACTIVE']);
    $goal2 = Goal::factory()->create(['user_id' => $user->id, 'parent_id' => $parent->id, 'status' => 'OPEN']);

    // Act
    $response = $this->patchJson("/api/goals/{$goal1->id}", ['status' => 'COMPLETE']);

    // Assert
    $response->assertStatus(Response::HTTP_OK);
    $this->assertEquals('COMPLETE', $goal1->fresh()->status);
    $this->assertEquals('ACTIVE', $goal2->fresh()->status);
});

test('should bubble complete when no open or active siblings', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    $parent = Goal::factory()->create(['user_id' => $user->id]);
    $goal1 = Goal::factory()->create(['user_id' => $user->id, 'parent_id' => $parent->id, 'status' => 'ACTIVE']);
    $goal2 = Goal::factory()->create(['user_id' => $user->id, 'parent_id' => $parent->id, 'status' => 'COMPLETE']);

    // Act
    $response = $this->patchJson("/api/goals/{$goal1->id}", ['status' => 'COMPLETE']);

    // Assert
    $response->assertStatus(Response::HTTP_OK);
    $this->assertEquals('COMPLETE', $goal1->fresh()->status);
    $this->assertEquals('COMPLETE', $parent->fresh()->status);
});

test('should bubble open up when status changes from complete to open', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    $grandparent = Goal::factory()->create(['user_id' => $user->id, 'status' => 'COMPLETE']);
    $parent = Goal::factory()->create(['user_id' => $user->id, 'parent_id' => $grandparent->id, 'status' => 'COMPLETE']);
    $goal = Goal::factory()->create(['user_id' => $user->id, 'parent_id' => $parent->id, 'status' => 'COMPLETE']);

    // Act
    $response = $this->patchJson("/api/goals/{$goal->id}", ['status' => 'OPEN']);

    // Assert
    $response->assertStatus(Response::HTTP_OK);
    $this->assertEquals('OPEN', $goal->fresh()->status);
    $this->assertEquals('OPEN', $parent->fresh()->status);
    $this->assertEquals('OPEN', $grandparent->fresh()->status);
});

test('user cannot patch goal of another user', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    $otherUser = User::factory()->createOne();
    $goal = Goal::factory()->create(['user_id' => $otherUser->id, 'status' => 'ACTIVE']);

    // Act
    $response = $this->patchJson("/api/goals/{$goal->id}", ['status' => 'COMPLETE']);

    // Assert
    $response->assertStatus(Response::HTTP_NOT_FOUND); // Should not be found for security reasons
    $this->assertEquals('ACTIVE', $goal->fresh()->status);
});

test('unauthenticated user cannot patch goal', function () {
    // Arrange
    $goal = Goal::factory()->create(['status' => 'ACTIVE']);

    // Act
    $response = $this->patchJson("/api/goals/{$goal->id}", ['status' => 'COMPLETE']);

    // Assert
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    $this->assertEquals('ACTIVE', $goal->fresh()->status);
});
