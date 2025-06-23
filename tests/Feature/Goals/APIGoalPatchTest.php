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
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function it_changes_status_from_active_to_complete_and_activates_sibling()
    {
        $parent = Goal::factory()->create(['user_id' => $this->user->id]);
        $goal1 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'ACTIVE']);
        $goal2 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'OPEN']);

        $response = $this->patchJson("/api/goals/{$goal1->id}", ['status' => 'COMPLETE']);
        $response->assertOk();
        $this->assertEquals('COMPLETE', $goal1->fresh()->status);
        $this->assertEquals('ACTIVE', $goal2->fresh()->status);
    }

    /** @test */
    public function it_bubbles_complete_when_no_open_or_active_siblings()
    {
        $parent = Goal::factory()->create(['user_id' => $this->user->id]);
        $goal1 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'ACTIVE']);
        $goal2 = Goal::factory()->create(['user_id' => $this->user->id, 'parent_id' => $parent->id, 'status' => 'COMPLETE']);

        $response = $this->patchJson("/api/goals/{$goal1->id}", ['status' => 'COMPLETE']);
        $response->assertOk();
        $this->assertEquals('COMPLETE', $goal1->fresh()->status);
        $this->assertEquals('COMPLETE', $parent->fresh()->status);
    }

    /** @test */
    public function it_returns_error_if_not_active()
    {
        $goal = Goal::factory()->create(['user_id' => $this->user->id, 'status' => 'OPEN']);
        $response = $this->patchJson("/api/goals/{$goal->id}", ['status' => 'COMPLETE']);
        $response->assertStatus(422);
    }
}
