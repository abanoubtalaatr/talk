<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'post' => $this->whenLoaded('post', fn () => ['id' => $this->post->id, 'content' => Str::limit($this->post->content, 50)]),
            'replies' => CommentResource::collection($this->whenLoaded('allReplies')),
            'reported_at' => $this->when(isset($this->reported_at), fn () => $this->reported_at?->toISOString()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
