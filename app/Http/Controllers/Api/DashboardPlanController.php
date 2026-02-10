<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class DashboardPlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::orderBy('is_free', 'desc')->orderBy('name')->get();

        return response()->json([
            'plans' => PlanResource::collection($plans),
        ]);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = Plan::create($request->validated());

        return response()->json([
            'message' => 'Plan created successfully.',
            'plan' => new PlanResource($plan),
        ], 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json([
            'plan' => new PlanResource($plan),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validated());

        return response()->json([
            'message' => 'Plan updated successfully.',
            'plan' => new PlanResource($plan),
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ], 200);
    }
}
