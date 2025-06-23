<?php

namespace Tests\Feature\Goals;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class APIGoalPatchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\User
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_changes_status_from_active_to_complete_and_activates_sibling()
    {
        // Arrange
        $this->actingAs($this->user, 'api');
        $parent = Goal::factory()->create(['user_id' => $this->user->id]);
        $goal1 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'ACTIVE']);
        $goal2 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'OPEN']);

        // Act
        $response = $this->patchJson("/api/goals/{$goal1->id}", ['status' => 'COMPLETE']);

        // Assert
        $response->assertOk();
        $this->assertEquals('COMPLETE', $goal1->fresh()->status);
        $this->assertEquals('ACTIVE', $goal2->fresh()->status);
    }

    /** @test */
    public function it_bubbles_complete_when_no_open_or_active_siblings()
    {
        // Arrange
        $this->actingAs($this->user, 'api');
        $parent = Goal::factory()->create(['user_id' => $this->user->id]);
        $goal1 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'ACTIVE']);
        $goal2 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'COMPLETE']);

        // Act
        $response = $this->patchJson("/api/goals/{$goal1->id}", ['status' => 'COMPLETE']);

        // Assert
        $response->assertOk();
        $this->assertEquals('COMPLETE', $goal1->fresh()->status);
        $this->assertEquals('COMPLETE', $parent->fresh()->status);
    }

    /** @test */
    public function it_bubbles_open_up_when_status_changes_from_complete_to_open()
    {
        // Arrange
        $this->actingAs($this->user, 'api');
        $grandparent = Goal::factory()->create(['user_id' => $this->user->id, 'status' => 'COMPLETE']);
        $parent = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $grandparent->id, 'status' => 'COMPLETE']);
        $goal = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'COMPLETE']);

        // Act
        $response = $this->patchJson("/api/goals/{$goal->id}", ['status' => 'OPEN']);

        // Assert
        $response->assertOk();
        $this->assertEquals('OPEN', $goal->fresh()->status);
        $this->assertEquals('OPEN', $parent->fresh()->status);
        $this->assertEquals('OPEN', $grandparent->fresh()->status);
    }

    /** @test */
    public function user_cannot_patch_goal_of_another_user()
    {
        // Arrange
        $this->actingAs($this->user, 'api');
        $otherUser = User::factory()->create();
        $goal = Goal::factory()->create(['user_id' => $otherUser->id, 'status' => 'ACTIVE']);

        // Act
        $response = $this->patchJson("/api/goals/{$goal->id}", ['status' => 'COMPLETE']);

        // Assert
        $response->assertStatus(404); // Should not be found for security reasons
        $this->assertEquals('ACTIVE', $goal->fresh()->status);
    }

    /** @test */
    public function unauthenticated_user_cannot_patch_goal()
    {
        // Arrange
        $goal = Goal::factory()->create(['status' => 'ACTIVE']);

        // Act
        $response = $this->patchJson("/api/goals/{$goal->id}", ['status' => 'COMPLETE']);

        // Assert
        $response->assertStatus(401);
        $this->assertEquals('ACTIVE', $goal->fresh()->status);
    }
}
