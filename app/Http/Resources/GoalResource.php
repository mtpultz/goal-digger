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
            'category' => $this->category,
            'status' => $this->status,
            'due_date' => $this->when(! is_null($this->due_date), optional($this->due_date)->toISOString()),
            'created_at' => $this->when(! is_null($this->created_at), optional($this->created_at)->toISOString()),
            'updated_at' => $this->when(! is_null($this->updated_at), optional($this->updated_at)->toISOString()),
            'links' => $this->when(! is_null($this->links), $this->links),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'root' => $this->when(
                ! $this->isRoot() && $this->relationLoaded('root'),
                fn () => new self($this->root)
            ),
            'parent' => $this->when(
                ! $this->isRoot() && $this->relationLoaded('parent'),
                fn () => new self($this->parent)
            ),
            'children' => $this->when(
                $this->relationLoaded('children'),
                fn () => self::collection($this->children)
            ),
        ];
    }
}
