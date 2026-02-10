<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardUserController extends Controller
{
    /**
     * List users. Search by username, display_name, id. Show plan, reputation (points), created_at, invited by, status, actions (ban).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['plan', 'referrer'])
            ->withCount(['posts', 'comments', 'referrals']);

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('username', 'like', "%{$term}%")
                    ->orWhere('display_name', 'like', "%{$term}%")
                    ->orWhere('id', 'like', "%{$term}%");
            });
        }
        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->input('username') . '%');
        }
        if ($request->filled('display_name')) {
            $query->where('display_name', 'like', '%' . $request->input('display_name') . '%');
        }
        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }

        $users = $query->latest()->paginate(20);

        return response()->json([
            'users' => DashboardUserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Ban a user.
     */
    public function ban(User $user): JsonResponse
    {
        $user->update(['banned_at' => now()]);

        return response()->json([
            'message' => 'User banned successfully.',
            'user' => new DashboardUserResource($user->load(['plan', 'referrer'])->loadCount(['posts', 'comments', 'referrals'])),
        ]);
    }

    /**
     * Unban a user.
     */
    public function unban(User $user): JsonResponse
    {
        $user->update(['banned_at' => null]);

        return response()->json([
            'message' => 'User unbanned successfully.',
            'user' => new DashboardUserResource($user->load(['plan', 'referrer'])->loadCount(['posts', 'comments', 'referrals'])),
        ]);
    }
}
