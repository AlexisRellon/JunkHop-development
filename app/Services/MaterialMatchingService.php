<?php

namespace App\Services;

use App\Models\Bid;
use App\Models\MaterialPreference;
use App\Models\Merchant;
use App\Models\MerchantItemInterest;

class MaterialMatchingService
{
    /**
     * Calculate match score between a merchant's preferences and a bid
     */    private function calculateMatchScore(Merchant $merchant, Bid $bid): float
    {
        $score = 0;
        
        \Log::info('Calculating score for bid', [
            'bid_id' => $bid->id,
            'merchant_id' => $merchant->ulid,
            'item_id' => $bid->item_id
        ]);
        
        // Get merchant's preference for this material
        $preference = MaterialPreference::where('merchant_id', $merchant->ulid)
            ->where('item_id', $bid->item_id)
            ->first();
            
        \Log::info('Found preference', ['preference' => $preference]);
            
        // Material type match (50 points)
        // If merchant has this material in their preferences, award full points
        if ($preference) {
            $score += 50;
            
            // Price compatibility score (up to 20 points)
            // Check if bid price is within merchant's preferred range
            if ($bid->price_per_kg >= $preference->min_price && $bid->price_per_kg <= $preference->max_price) {
                $score += 20; // Perfect price match
            } else {
                // Calculate how far the price is from the preferred range
                $minDiff = abs($bid->price_per_kg - $preference->min_price);
                $maxDiff = abs($bid->price_per_kg - $preference->max_price);
                $closestDiff = min($minDiff, $maxDiff);
                
                // The closer to the range, the higher the score
                $priceScore = max(0, 20 - ($closestDiff / $preference->min_price) * 20);
                $score += $priceScore;
            }
        }

        // Distance factor (up to 20 points)
        $distance = $this->calculateDistance(
            $merchant->address,
            $bid->junkshop->address
        );
        $distanceScore = max(0, 20 - ($distance / 10) * 20); // 10km reference distance
        $score += $distanceScore;

        // Quantity fulfillment (up to 10 points)
        // For now, we'll give full points if quantity is available
        // This can be refined based on merchant's typical order quantities
        if ($bid->quantity > 0) {
            $score += 10;
        }

        return $score;
    }

    /**
     * Find optimal matches for a merchant
     */
    public function findOptimalMatches(Merchant $merchant, $availableBids)
    {
        // Calculate scores for each bid
        $scoredBids = $availableBids->map(function ($bid) use ($merchant) {
            $score = $this->calculateMatchScore($merchant, $bid);
            return [
                'bid' => $bid,
                'score' => $score
            ];
        });

        // Sort by score in descending order
        return $scoredBids->sortByDesc('score')->values();
    }

    /**
     * Calculate distance between two addresses
     * This is a simplified version - you might want to use a geocoding service
     */
    private function calculateDistance(string $address1, string $address2): float
    {
        // TODO: Implement proper distance calculation using geocoding
        // For now, return a random distance between 0-20km
        return rand(0, 20);
    }
}
