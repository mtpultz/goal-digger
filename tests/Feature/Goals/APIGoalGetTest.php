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

    Goal::factory()->count(26)->create(['user_id' => $user->id, 'status' => 'ACTIVE']);
    Goal::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'OPEN']);

    // Act
    $response = $this->getJson('/api/goals/active');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(25, 'data');
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
