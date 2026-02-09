<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at->toISOString(),

            // Conditional counts (available from dashboard)
            'posts_count' => $this->whenCounted('posts'),
            'users_count' => $this->whenCounted('users'),
        ];
    }
}
