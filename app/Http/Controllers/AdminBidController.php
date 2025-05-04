<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Junkshop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminBidController extends Controller
{
    /**
     * Get all bids for admin management
     */
    public function index(): JsonResponse
    {
        // Check if user is admin
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized access. Admin role required.'
            ], 403);
        }        // Get all bids with relationships
        $bids = Bid::with(['item', 'junkshop'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Log that an admin viewed all bids
        \App\Services\AdminLogger::logMaintenance(
            'view_all_bids',
            "Admin viewed list of all bids"
        );

        return response()->json($bids);
    }

    /**
     * Update bid status (admin only)
     */
    public function updateStatus(Request $request, $bidId): JsonResponse
    {
        // Check if user is admin
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized access. Admin role required.'
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,rejected',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the bid
        $bid = Bid::find($bidId);
        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        // Update status and related timestamps
        $status = $request->status;
        $bid->status = $status;
        
        if ($status === 'accepted') {
            $bid->accepted_at = now();
            // Clear rejection reason if previously rejected
            $bid->rejection_reason = null;
        } else if ($status === 'rejected') {
            $bid->rejected_at = now();
            if ($request->has('rejection_reason')) {
                $bid->rejection_reason = $request->rejection_reason;
            }
        }        $bid->save();

        // Log bid status change by admin
        \App\Services\TransactionLogger::logBidStatusChange(
            $bid,
            $bid->getOriginal('status'), // Original status before update
            $status
        );

        // Load relations
        $bid->load(['item', 'junkshop']);

        return response()->json($bid);
    }

    /**
     * Get details for a specific bid
     */
    public function show($bidId): JsonResponse
    {
        // Check if user is admin
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized access. Admin role required.'
            ], 403);
        }        // Find bid with relationships
        $bid = Bid::with(['item', 'junkshop'])
            ->where('id', $bidId)
            ->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        // Log that an admin viewed bid details
        \App\Services\AdminLogger::logMaintenance(
            'view_bid_details',
            "Admin viewed details for bid #{$bidId}"
        );

        return response()->json($bid);
    }
}
