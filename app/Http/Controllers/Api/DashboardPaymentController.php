<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardPaymentController extends Controller
{
    /**
     * List payments. Search by name (user), id, user_id. Filter by status (under_review, approved, disapproved). Has image, amount, type, user, plan. Actions: approve, disapprove, delete.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['user', 'plan']);

        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('name')) {
            $term = $request->input('name');
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('username', 'like', "%{$term}%")
                    ->orWhere('display_name', 'like', "%{$term}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('under_review') && $request->boolean('under_review')) {
            $query->underReview();
        }

        $payments = $query->latest()->paginate(20);

        return response()->json([
            'payments' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function approve(Payment $payment): JsonResponse
    {
        $payment->update(['status' => Payment::STATUS_APPROVED]);

        return response()->json([
            'message' => 'Payment approved.',
            'payment' => new PaymentResource($payment->load(['user', 'plan'])),
        ]);
    }

    public function disapprove(Payment $payment): JsonResponse
    {
        $payment->update(['status' => Payment::STATUS_DISAPPROVED]);

        return response()->json([
            'message' => 'Payment disapproved.',
            'payment' => new PaymentResource($payment->load(['user', 'plan'])),
        ]);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.',
        ], 200);
    }
}
