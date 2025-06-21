<?php

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

test('Goal get Active endpoint should return a list of active goals for the current user', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    Goal::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'ACTIVE']);
    Goal::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'OPEN']);

    $response = $this->getJson('/api/goals/active');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});
