<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\BidHistory;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MerchantBidController extends Controller
{
    /**
     * Display a listing of active bids that merchants can bid on
     */
    public function index(Request $request): JsonResponse
    {
        $query = Bid::with(['junkshop', 'item'])
            ->where('is_bidding_enabled', true)
            ->where('status', 'accepted')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereNotNull('starting_bid')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('end_date', 'asc');

        // Apply filters if provided
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }

        if ($request->has('min_price')) {
            $query->where('starting_bid', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('starting_bid', '<=', $request->max_price);
        }

        $bids = $query->get();

        // Add remaining time for each bid
        $bids->each(function ($bid) {
            $bid->remaining_time = now()->diffInSeconds($bid->end_date);
            $bid->formatted_remaining_time = $this->formatRemainingTime($bid->remaining_time);
        });

        return response()->json($bids);
    }

    /**
     * Get bid history for a specific bid
     */
    public function getBidHistory(string $ulid): JsonResponse
    {
        $bid = Bid::where('ulid', $ulid)->first();

        if (!$bid) {
            return response()->json(['message' => 'Bid not found'], 404);
        }

        $history = BidHistory::with('merchant')
            ->where('bid_id', $bid->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'bid_amount' => $item->bid_amount,
                    'merchant_name' => $item->merchant ? $item->merchant->user->name : 'Anonymous',
                    'is_current_user' => Auth::user()->merchant && $item->merchant_id === Auth::user()->merchant->ulid,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json($history);
    }

    /**
     * Place a bid on an item
     */
    public function placeBid(Request $request, string $ulid): JsonResponse
    {
        $user = Auth::user();

        if (!$user->merchant) {
            return response()->json(['message' => 'You must have a merchant profile to place bids'], 403);
        }

        // Find the bid
        $bid = Bid::where('ulid', $ulid)->first();

        if (!$bid) {
            return response()->json(['message' => 'Bid not found'], 404);
        }

        // Validate the bid is open for bidding
        if (!$bid->is_bidding_enabled) {
            return response()->json(['message' => 'This item is not open for bidding'], 400);
        }

        // Validate dates
        $now = now();
        if ($now < $bid->start_date || $now > $bid->end_date) {
            return response()->json(['message' => 'Bidding period is not active'], 400);
        }

        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'bid_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Get minimum bid amount - apply JunkHop algorithm
        $minimumBid = $this->calculateMinimumBid($bid);
        if ($request->bid_amount < $minimumBid) {
            return response()->json([
                'message' => "Bid amount must be at least ₱{$minimumBid}",
                'minimum_bid' => $minimumBid
            ], 422);
        }

        // Don't allow bidding on your own items if you're a junkshop owner
        if ($user->hasRole('junkshop_owner') && $bid->junkshop->user_id === $user->ulid) {
            return response()->json(['message' => 'You cannot bid on your own items'], 400);
        }
        
        // Store previous bidder ID to notify them later
        $previousBidderId = $bid->current_bidder_id;

        try {
            // Use a transaction to ensure bid and history are saved together
            DB::beginTransaction();

            // Create a bid history entry
            $bidHistory = new BidHistory();
            $bidHistory->bid_id = $bid->id;
            $bidHistory->merchant_id = $user->merchant->ulid;
            $bidHistory->bid_amount = $request->bid_amount;
            $bidHistory->notes = $request->notes;
            $bidHistory->save();

            // Update the bid with the new current bid
            $bid->current_bid = $request->bid_amount;
            $bid->current_bidder_id = $user->merchant->ulid;
            $bid->save();

            DB::commit();
            
            // Send notifications
            $this->sendBidNotifications($bid, $previousBidderId, $user->merchant);

            return response()->json([
                'message' => 'Bid placed successfully',
                'bid' => $bid,
                'history' => $bidHistory
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error placing bid: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to place bid'], 500);
        }
    }
    
    /**
     * Send notifications for a new bid
     */
    private function sendBidNotifications(Bid $bid, ?string $previousBidderId, Merchant $currentBidder): void
    {
        try {
            // 1. Notify junkshop owner
            if ($bid->junkshop && $bid->junkshop->user) {
                $bid->junkshop->user->notify(new \App\Notifications\NewBidPlacedNotification($bid, $currentBidder));
            }
            
            // 2. Notify previous bidder if they were outbid
            if ($previousBidderId && $previousBidderId !== $currentBidder->ulid) {
                $previousBidder = Merchant::where('ulid', $previousBidderId)->first();
                if ($previousBidder && $previousBidder->user) {
                    $previousBidder->user->notify(new \App\Notifications\OutbidNotification($bid, $currentBidder));
                }
            }
            
            // Log the notification action
            Log::info('Bid notifications sent', [
                'bid_id' => $bid->ulid,
                'current_bidder' => $currentBidder->ulid,
                'previous_bidder' => $previousBidderId
            ]);
            
        } catch (\Exception $e) {
            // Just log the error, don't fail the bid placement
            Log::error('Error sending bid notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate the minimum bid amount based on the JunkHop recommended algorithm
     * - If no bids yet, minimum is the starting bid
     * - Otherwise, minimum is current bid + 5% increment
     */
    private function calculateMinimumBid(Bid $bid): float
    {
        $startingBid = $bid->starting_bid ?? $bid->price_per_kg;
        $currentBid = $bid->current_bid ?? 0;

        // If no bids yet, minimum is the starting bid
        if ($currentBid == 0) {
            return $startingBid;
        }

        // JunkHop recommended algorithm: current bid + 5% increment
        // This encourages reasonable bid increments while preventing tiny increases
        // This ensures the market value is properly established through competitive bidding
        return $currentBid * 1.05;
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
