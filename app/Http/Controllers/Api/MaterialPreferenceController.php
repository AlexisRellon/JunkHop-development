<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MaterialPreferenceController extends Controller
{
    /**
     * Get all material preferences for the authenticated merchant.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $preferences = MaterialPreference::where('merchant_id', $merchant->ulid)
            ->get()
            ->keyBy('item_id')
            ->map(function ($preference) {
                return [
                    'minPrice' => $preference->min_price,
                    'maxPrice' => $preference->max_price,
                ];
            });

        return response()->json($preferences);
    }

    /**
     * Update or create material preferences for a specific item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrCreate(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'minPrice' => 'required|numeric|min:0',
            'maxPrice' => 'required|numeric|min:0|gt:minPrice',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }
        
        $preference = MaterialPreference::updateOrCreate(
            [
                'merchant_id' => $merchant->ulid,
                'item_id' => $itemId,
            ],
            [
                'min_price' => $request->minPrice,
                'max_price' => $request->maxPrice,
            ]
        );

        return response()->json([
            'status' => 'success',
            'preference' => [
                'itemId' => $itemId,
                'minPrice' => $preference->min_price,
                'maxPrice' => $preference->max_price,
            ],
        ]);
    }

    /**
     * Calculate matching score for a bid based on merchant preferences
     *
     * @param \App\Models\Bid $bid
     * @param \App\Models\MaterialPreference $preference
     * @return float
     */
    private function calculateMatchingScore($bid, $preference)
    {
        $score = 0;
        
        // Base score - start at 100
        $score = 100;
        
        // Price match (40% weight)
        $priceScore = 40;
        if ($bid->current_bid > $preference->max_price) {
            $priceScore *= ($preference->max_price / $bid->current_bid);
        } else if ($bid->current_bid < $preference->min_price) {
            $priceScore *= ($bid->current_bid / $preference->min_price);
        }
        $score += $priceScore;

        // Grade match (30% weight)
        if ($bid->grade === 'A') {
            $score += 30;
        } else if ($bid->grade === 'B') {
            $score += 20;
        } else if ($bid->grade === 'C') {
            $score += 10;
        }

        // Quantity availability (30% weight)
        $score += min(($bid->quantity_available / $preference->desired_quantity) * 30, 30);

        return $score;
    }

    /**
     * Get sorted marketplace listings based on preferences
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMatchedListings(Request $request)
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        // Get merchant's preferences
        $preferences = MaterialPreference::where('merchant_id', $merchant->ulid)
            ->with('item')
            ->get();

        // Get all active bids
        $bids = Bid::with(['item', 'junkshop'])
            ->where('status', 'pending')
            ->where('end_date', '>', now())
            ->get();

        // Calculate scores for each bid
        $scoredBids = $bids->map(function ($bid) use ($preferences) {
            $score = $this->calculateMatchScore($bid, $preferences);
            return [
                'bid' => $bid,
                'score' => $score,
                'matchDetails' => $this->getMatchDetails($bid, $preferences)
            ];
        })->sortByDesc('score')->values();

        return response()->json($scoredBids);
    }

    /**
     * Calculate the match score between a bid and merchant preferences.
     *
     * @param Bid $bid
     * @param Collection $preferences
     * @return float
     */
    private function calculateMatchScore($bid, $preferences)
    {
        $preference = $preferences->firstWhere('item_id', $bid->item_id);
        if (!$preference) {
            return 0; // No preference for this material
        }

        $score = 0;
        
        // Price match (40% weight)
        $priceScore = $this->calculatePriceScore(
            $bid->current_bid ?: $bid->starting_bid,
            $preference->min_price,
            $preference->max_price
        );
        $score += $priceScore * 0.4;

        // Grade match (30% weight)
        $gradeScore = $this->calculateGradeScore($bid->grade, $preference->grade ?? 'any');
        $score += $gradeScore * 0.3;

        // Quantity availability (30% weight)
        $quantityScore = $this->calculateQuantityScore($bid->quantity_available);
        $score += $quantityScore * 0.3;

        return $score;
    }

    private function calculatePriceScore($bidPrice, $minPreference, $maxPreference)
    {
        if (!$minPreference || !$maxPreference) {
            return 50; // Neutral score if no price preference
        }

        if ($bidPrice >= $minPreference && $bidPrice <= $maxPreference) {
            return 100; // Perfect match
        }

        // Calculate how far the price is from the preferred range
        $distanceFromRange = min(
            abs($bidPrice - $minPreference),
            abs($bidPrice - $maxPreference)
        );
        
        // Score decreases as distance from range increases
        return max(0, 100 - ($distanceFromRange / $maxPreference) * 100);
    }

    private function calculateGradeScore($bidGrade, $preferredGrade)
    {
        if ($preferredGrade === 'any' || !$preferredGrade) {
            return 75; // Good score for any grade if no preference
        }

        return $bidGrade === $preferredGrade ? 100 : 25;
    }

    private function calculateQuantityScore($quantity)
    {
        // Higher quantity gets better score, max at 1000kg
        return min(100, ($quantity / 1000) * 100);
    }

    private function getMatchDetails($bid, $preferences)
    {
        $preference = $preferences->firstWhere('item_id', $bid->item_id);
        if (!$preference) {
            return [
                'priceMatch' => false,
                'gradeMatch' => false,
                'message' => 'No preferences set for this material'
            ];
        }

        $priceMatch = $bid->current_bid >= $preference->min_price && 
                     $bid->current_bid <= $preference->max_price;

        return [
            'priceMatch' => $priceMatch,
            'inPriceRange' => $priceMatch ? 'Price within your preferred range' : 'Price outside preferred range',
            'gradeMatch' => $preference->grade ? $bid->grade === $preference->grade : true,
        ];
    }
}
