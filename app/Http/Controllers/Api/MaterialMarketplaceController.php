<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Services\MaterialMatchingService;
use App\Models\MaterialPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialMarketplaceController extends Controller
{
    protected $matchingService;

    public function __construct(MaterialMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Get personalized marketplace listings for the authenticated merchant
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $merchant = $user->merchant;

            if (!$merchant) {
                return response()->json([
                    'message' => 'Merchant profile not found'
                ], 404);
            }

            // Debug info
            \Log::info('Merchant found', ['merchant' => $merchant->toArray()]);            // Get all available materials
            $availableBids = Bid::query()
                ->with(['item', 'junkshop'])
                ->whereHas('item')
                ->whereHas('junkshop')
                ->where(function($query) {
                    $query->where('is_bidding_enabled', true)
                        ->where('end_date', '>', now())
                        ->orWhere('is_bidding_enabled', false);
                })
                ->get();

            \Log::info('Available bids', [
                'count' => $availableBids->count(),
                'bids' => $availableBids->toArray()
            ]);

            if ($availableBids->isEmpty()) {
                return response()->json([
                    'message' => 'No bids available',
                    'data' => []
                ]);
            }

            // Apply material matching algorithm
            $matchedBids = $this->matchingService->findOptimalMatches($merchant, $availableBids);

            \Log::info('Matched bids', [
                'count' => $matchedBids->count(),
                'matches' => $matchedBids->toArray()
            ]);            // Transform the results to include preference scores
            $results = $matchedBids->map(function ($match) {
                $bid = $match['bid'];
                return [
                    'id' => $bid->id,
                    'ulid' => $bid->ulid,
                    'junkshop' => [
                        'name' => $bid->junkshop->name,
                        'address' => $bid->junkshop->address
                    ],
                    'item' => [
                        'id' => $bid->item->id,
                        'name' => $bid->item->name
                    ],
                    'quantity' => $bid->quantity,
                    'price_per_kg' => $bid->price_per_kg,
                    'starting_bid' => $bid->starting_bid,
                    'current_bid' => $bid->current_bid,
                    'start_date' => $bid->start_date,
                    'end_date' => $bid->end_date,
                    'grade' => $bid->grade,
                    'preference_score' => $match['score'],
                    'is_preferred_material' => $match['score'] >= 50, // Has material type match
                    'is_bidding_enabled' => $bid->is_bidding_enabled
                ];
            });

            return response()->json($results);
        } catch (\Exception $e) {
            \Log::error('Error in marketplace listings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch marketplace listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
