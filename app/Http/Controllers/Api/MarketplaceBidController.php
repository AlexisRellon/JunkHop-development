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
            ->whereNotNull('accepted_at');
            
        // Only include active bidding items if filter is applied
        if ($request->has('bidding_only') && $request->bidding_only) {
            $query->where('is_bidding_enabled', true)
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereNotNull('starting_bid')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now());
        }
        
        $query->orderBy('accepted_at', 'desc');

        // Apply filters
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }

        if ($request->has('min_price')) {
            // Check either price_per_kg or starting_bid depending on the bidding status
            $query->where(function($q) use ($request) {
                $q->where('price_per_kg', '>=', $request->min_price)
                  ->orWhere('starting_bid', '>=', $request->min_price);
            });
        }

        if ($request->has('max_price')) {
            // Check either price_per_kg or starting_bid depending on the bidding status
            $query->where(function($q) use ($request) {
                $q->where('price_per_kg', '<=', $request->max_price)
                  ->orWhere('starting_bid', '<=', $request->max_price);
            });
        }

        $bids = $query->get();

        // Add remaining time for bidding-enabled items
        $bids->each(function ($bid) {
            if ($bid->is_bidding_enabled && $bid->end_date) {
                $bid->remaining_time = now()->diffInSeconds($bid->end_date);
                $bid->formatted_remaining_time = $this->formatRemainingTime($bid->remaining_time);
            }
        });

        return response()->json($bids);
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

        if ($bid->is_bidding_enabled && $bid->end_date) {
            $bid->remaining_time = now()->diffInSeconds($bid->end_date);
            $bid->formatted_remaining_time = $this->formatRemainingTime($bid->remaining_time);
        }

        return response()->json($bid);
    }
    
    /**
     * Format remaining time in a human-readable format
     */
    private function formatRemainingTime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($days > 0) {
            return "{$days}d {$hours}h";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }
}
