<?php

namespace Tests\Traits;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

trait CreatesGoalTree
{
    /**
     * Creates a goal tree with a specified depth and a random number of sub-goals at each level.
     * It will randomly set one leaf goal to ACTIVE.
     *
     * @param  User  $user  The user for whom to create the goals.
     * @param  int  $maxDepth  The maximum depth of the tree.
     * @param  int  $maxChildren  The maximum number of children for each node.
     * @return array{'root': Goal, 'activeLeaf': Goal}
     */
    public function createGoalTree(User $user, int $maxDepth = 3, int $maxChildren = 3): array
    {
        // 1. Create the root goal
        $rootGoal = Goal::factory()->create([
            'user_id' => $user->id,
            'parent_id' => null,
            'root_id' => null,
            'status' => 'OPEN',
        ]);
        $rootGoal->update(['root_id' => $rootGoal->id]);

        // 2. Build the tree recursively
        $leafGoals = new Collection;
        $this->generateChildren($user, $rootGoal, $rootGoal, 1, $maxDepth, $maxChildren, $leafGoals);

        // 3. If no children were created, the root is the only leaf
        if ($leafGoals->isEmpty() && $maxDepth > 0) {
            $leafGoals->push($rootGoal);
        }

        // 4. Pick one leaf goal and set it to active
        /** @var Goal $activeLeaf */
        $activeLeaf = $leafGoals->random();
        $activeLeaf->update(['status' => 'ACTIVE']);

        // 5. Return the root and the active leaf
        return [
            'root' => $rootGoal,
            'activeLeaf' => $activeLeaf,
        ];
    }

    private function generateChildren(User $user, Goal $root, Goal $parent, int $currentDepth, int $maxDepth, int $maxChildren, Collection &$leafGoals): void
    {
        if ($currentDepth >= $maxDepth) {
            return;
        }

        $numberOfChildren = rand(1, $maxChildren);

        for ($i = 0; $i < $numberOfChildren; $i++) {
            $childGoal = Goal::factory()->create([
                'user_id' => $user->id,
                'parent_id' => $parent->id,
                'root_id' => $root->id,
                'status' => 'OPEN',
            ]);

            // If the next level is the max depth, this is a leaf node.
            if ($currentDepth + 1 === $maxDepth) {
                $leafGoals->push($childGoal);
            }

            // Recurse to the next level
            $this->generateChildren($user, $root, $childGoal, $currentDepth + 1, $maxDepth, $maxChildren, $leafGoals);
        }
    }
}
