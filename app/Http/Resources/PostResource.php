<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'is_anonymous' => $this->is_anonymous,
            'is_featured' => $this->when(isset($this->is_featured), fn () => $this->is_featured),
            'is_hidden' => $this->when(isset($this->is_hidden), fn () => $this->is_hidden),
            'reported_at' => $this->when(isset($this->reported_at), fn () => $this->reported_at?->toISOString()),
            'user' => $this->when(
                !$this->is_anonymous,
                fn () => new UserResource($this->whenLoaded('user')),
            ),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Reaction counts
            'likes_count' => $this->whenCounted('likes'),
            'stars_count' => $this->whenCounted('stars'),
            'reactions_count' => $this->whenCounted('reactions'),
            'comments_count' => $this->whenCounted('comments'),
        ];
    }
}
