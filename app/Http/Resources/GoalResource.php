<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->when(! is_null($this->due_date), optional($this->due_date)->toISOString()),
            'created_at' => $this->when(! is_null($this->created_at), optional($this->created_at)->toISOString()),
            'links' => $this->when(! is_null($this->links), $this->links),
            'root' => $this->when(
                ! $this->isRoot() && $this->relationLoaded('root'),
                fn () => new self($this->root)
            ),
            'parent' => $this->when(
                ! $this->isRoot() && $this->relationLoaded('parent'),
                fn () => new self($this->parent)
            ),
        ];
    }
}
