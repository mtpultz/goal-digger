<?php

namespace Database\Factories;

use App\Models\BuddyGoal;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BuddyGoal>
 */
class BuddyGoalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BuddyGoal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'user_id' => User::factory(),
            'buddy_id' => User::factory(),
            'status' => 'PENDING',
            'role' => 'VIEWER',
        ];
    }

    /**
     * Indicate that the buddy goal relationship is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ACCEPTED',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Indicate that the buddy goal relationship is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REJECTED',
        ]);
    }

    /**
     * Indicate that the buddy has viewer role.
     */
    public function viewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'VIEWER',
        ]);
    }

    /**
     * Indicate that the buddy has contributor role.
     */
    public function contributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'CONTRIBUTOR',
        ]);
    }

    /**
     * Indicate that the buddy has collaborator role.
     */
    public function collaborator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'COLLABORATOR',
        ]);
    }
}
