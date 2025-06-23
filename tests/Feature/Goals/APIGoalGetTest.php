<?php

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

test('Goal get Active endpoint should return a list of active goals for the current user', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    Goal::factory()->count(24)->create(['user_id' => $user->id, 'status' => 'ACTIVE']);
    Goal::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'OPEN']);

    // Act
    $response = $this->getJson('/api/goals/active');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(24, 'data');
    $response->assertJsonStructure([
        'data',
        'links',
        'meta' => [
            'per_page',
        ],
    ]);
    $response->assertJsonPath('meta.per_page', 25);
});

test('Goal get Active endpoint supports pagination', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    Goal::factory()->count(30)->create(['user_id' => $user->id, 'status' => 'ACTIVE']);

    // Act & Assert for Page 1
    $response_page1 = $this->getJson('/api/goals/active?page=1');
    $response_page1->assertStatus(200);
    $response_page1->assertJsonCount(25, 'data');
    $page1_ids = collect($response_page1->json('data'))->pluck('id');

    // Act & Assert for Page 2
    $response_page2 = $this->getJson('/api/goals/active?page=2');
    $response_page2->assertStatus(200);
    $response_page2->assertJsonCount(5, 'data');
    $page2_ids = collect($response_page2->json('data'))->pluck('id');

    // Assert that page 2 does not contain any of page 1's items
    $this->assertEmpty($page1_ids->intersect($page2_ids));
});

test('Goal get Active endpoint includes the root goal for nested goals', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    $rootGoal = Goal::factory()->create([
        'user_id' => $user->id,
        'parent_id' => null,
        'status' => 'OPEN', // Root goal is not active itself
    ]);

    // Set root_id for the root goal itself
    $rootGoal->root_id = $rootGoal->id;
    $rootGoal->save();

    $childGoal = Goal::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $rootGoal->id,
        'root_id' => $rootGoal->id,
        'status' => 'ACTIVE', // This is the goal we expect to fetch
    ]);

    // Act
    $response = $this->getJson('/api/goals/active');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data');

    // Find the child goal in the response
    $response_child_goal = collect($response->json('data'))->firstWhere('id', $childGoal->id);

    $this->assertNotNull($response_child_goal, 'Child goal not found in response');
    $this->assertNotNull($response_child_goal['root'], 'Root goal not present for child goal');
    $this->assertEquals($rootGoal->id, $response_child_goal['root']['id']);
    $this->assertEquals($rootGoal->title, $response_child_goal['root']['title']);
    $this->assertArrayNotHasKey('root', $response_child_goal['root']);
});
