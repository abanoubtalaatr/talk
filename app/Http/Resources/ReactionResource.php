<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'user' => new UserResource($this->whenLoaded('user')),
            'post_id' => $this->post_id,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
