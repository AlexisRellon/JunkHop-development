<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceBidController extends Controller
{
    /**
     * Get verified/accepted bids for the marketplace
     */
    public function index(Request $request): JsonResponse
    {
        $query = Bid::with(['junkshop', 'item'])
            ->where('status', 'accepted')
            ->whereNotNull('accepted_at')
            ->orderBy('accepted_at', 'desc');

        // Apply filters
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }

        if ($request->has('min_price')) {
            $query->where('price_per_kg', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price_per_kg', '<=', $request->max_price);
        }

        return response()->json($query->get());
    }

    /**
     * Get details for a specific bid
     */
    public function show(string $ulid): JsonResponse
    {
        $bid = Bid::with(['junkshop', 'item'])
            ->where('ulid', $ulid)
            ->where('status', 'accepted')
            ->whereNotNull('accepted_at')
            ->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        return response()->json($bid);
    }
}
