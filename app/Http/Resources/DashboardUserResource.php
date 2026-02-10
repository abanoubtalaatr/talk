<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'display_name' => $this->display_name,
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'reputation_points' => $this->points,
            'created_at' => $this->created_at->toISOString(),
            'invited_by' => new UserResource($this->whenLoaded('referrer')),
            'status' => $this->banned_at ? 'banned' : ($this->plan?->name ?? 'no_plan'),
            'banned_at' => $this->banned_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'posts_count' => $this->whenCounted('posts'),
            'comments_count' => $this->whenCounted('comments'),
            'referrals_count' => $this->whenCounted('referrals'),
        ];
    }
}
