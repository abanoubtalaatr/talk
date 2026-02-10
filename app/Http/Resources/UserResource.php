<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'display_name' => $this->when(isset($this->display_name), $this->display_name),
            'bio' => $this->bio,
            'image' => $this->image,
            'points' => $this->points,
            'referral_link' => url("/api/register?referral_username={$this->username}"),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Conditional relationships
            'referrer' => new UserResource($this->whenLoaded('referrer')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'blocked_users' => UserResource::collection($this->whenLoaded('blockedUsers')),

            // Conditional counts
            'posts_count' => $this->whenCounted('posts'),
            'comments_count' => $this->whenCounted('comments'),
            'referrals_count' => $this->whenCounted('referrals'),
        ];
    }
}
