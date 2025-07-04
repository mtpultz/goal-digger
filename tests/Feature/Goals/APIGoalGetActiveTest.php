<?php

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\Traits\CreatesGoalTree;

uses(RefreshDatabase::class, CreatesGoalTree::class);

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

test('Goal get Active endpoint includes root and parent goals for nested goals', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    $rootGoal = Goal::factory()->create([
        'user_id' => $user->id,
        'parent_id' => null,
        'status' => 'OPEN',
    ]);
    $rootGoal->update(['root_id' => $rootGoal->id]);

    $parentGoal = Goal::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $rootGoal->id,
        'root_id' => $rootGoal->id,
        'status' => 'OPEN',
    ]);

    $childGoal = Goal::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $parentGoal->id,
        'root_id' => $rootGoal->id,
        'status' => 'ACTIVE', // This is the goal we expect to fetch
    ]);

    // Act
    $response = $this->getJson('/api/goals/active');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data');

    $response_data = $response->json('data.0');

    $this->assertEquals($childGoal->id, $response_data['id']);

    // Assert root goal is correct
    $this->assertNotNull($response_data['root'], 'Root goal not present for child goal');
    $this->assertEquals($rootGoal->id, $response_data['root']['id']);
    $this->assertArrayNotHasKey('root', $response_data['root']);
    $this->assertArrayNotHasKey('parent', $response_data['root']);

    // Assert parent goal is correct
    $this->assertNotNull($response_data['parent'], 'Parent goal not present for child goal');
    $this->assertEquals($parentGoal->id, $response_data['parent']['id']);
});

test('Goal get Active endpoint returns the single active leaf from a generated tree', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    // Use the trait to create a goal tree of depth 3
    $generatedGoals = $this->createGoalTree($user, 3);
    $activeLeaf = $generatedGoals['activeLeaf'];

    // Act
    $response = $this->getJson('/api/goals/active');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data');
    $this->assertEquals($activeLeaf->id, $response->json('data.0.id'));
});

test('Goal get Active endpoint returns all active leaves from multiple trees', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    // Create three separate goal trees for the same user
    $activeLeaf1 = $this->createGoalTree($user)['activeLeaf'];
    $activeLeaf2 = $this->createGoalTree($user)['activeLeaf'];
    $activeLeaf3 = $this->createGoalTree($user)['activeLeaf'];

    $expectedActiveGoalIds = collect([$activeLeaf1, $activeLeaf2, $activeLeaf3])->pluck('id')->sort()->values();

    // Act
    $response = $this->getJson('/api/goals/active');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');

    $responseGoalIds = $response->collect('data')->pluck('id')->sort()->values();
    $this->assertEquals($expectedActiveGoalIds, $responseGoalIds);
});

test('Goal resource returns due_date, created_at, and links when present', function () {
    // Arrange
    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    Passport::actingAs($user);

    $dueDate = now()->addDays(10);
    $links = ['Example' => 'https://example.com'];

    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'status' => 'ACTIVE',
        'due_date' => $dueDate,
        'links' => $links,
    ]);

    // Act
    $response = $this->getJson('/api/goals/active');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data');
    $data = $response->json('data.0');

    $this->assertEquals($goal->id, $data['id']);
    $this->assertEquals($goal->due_date->toISOString(), $data['due_date']);
    $this->assertEquals($goal->created_at->toISOString(), $data['created_at']);
    $this->assertEquals($links, $data['links']);
});
