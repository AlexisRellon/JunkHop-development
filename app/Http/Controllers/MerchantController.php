<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Junkshop;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MerchantController extends Controller
{
    /**
     * Display the authenticated merchant's profile.
     */
    public function show()
    {
        $user = Auth::user();
        $merchant = Merchant::where('user_id', $user->ulid)->first();

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        // Load relationships
        $merchant->load(['items']);

        return response()->json($merchant);
    }

    /**
     * Store a new merchant profile
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        // Check if user already has a merchant profile
        $existingMerchant = Merchant::where('user_id', $user->ulid)->first();
        if ($existingMerchant) {
            return response()->json([
                'message' => 'Merchant profile already exists',
                'merchant' => $existingMerchant
            ], 409);
        }

        // Create merchant profile
        $merchant = Merchant::create([
            'ulid' => Str::ulid()->toBase32(),
            'business_name' => $request->business_name,
            'contact' => $request->contact,
            'address' => $request->address,
            'description' => $request->description,
            'user_id' => $user->ulid
        ]);

        return response()->json([
            'message' => 'Merchant profile created successfully',
            'merchant' => $merchant
        ], 201);
    }

    /**
     * Update the merchant profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'business_name' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $merchant = Merchant::where('user_id', $user->ulid)->first();

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        // Track changes
        $changes = [];
        $updatableFields = ['business_name', 'contact', 'description', 'address'];
        
        foreach ($updatableFields as $field) {
            if ($request->has($field) && $merchant->$field !== $request->$field) {
                $changes[$field] = [
                    'old' => $merchant->$field,
                    'new' => $request->$field
                ];
            }
        }

        $merchant->update($request->all());
        
        // Log merchant update with detailed changes
        if (!empty($changes)) {
            if (Auth::check() && Auth::user()->hasRole('admin')) {
                \App\Services\AdminLogger::log(
                    'admin',
                    $merchant,
                    'merchant_updated',
                    "Admin updated merchant: {$merchant->business_name}",
                    Auth::id(),
                    $changes
                );
            } else {
                \App\Services\ActivityLogger::logMerchant($merchant, 'updated', null, Auth::id(), $changes);
            }
        }

        return response()->json([
            'message' => 'Merchant profile updated successfully',
            'merchant' => $merchant
        ]);
    }

    /**
     * Update the specified Merchant by an admin.
     */
    public function adminUpdate(Request $request, string $ulid): \Illuminate\Http\JsonResponse
    {
        $merchant = Merchant::where('ulid', $ulid)->firstOrFail();

        $request->validate([
            'business_name' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'user_id' => 'sometimes|required|string|exists:users,ulid',
        ]);

        $updateData = $request->only(['business_name', 'contact', 'address', 'description', 'user_id']);
        
        // Track changes for activity log
        $changes = [];
        foreach ($updateData as $field => $newValue) {
            if ($merchant->{$field} !== $newValue) {
                $changes[$field] = [
                    'old' => $merchant->{$field},
                    'new' => $newValue
                ];
            }
        }

        $merchant->update($updateData);
        
        // Log merchant update with detailed changes
        if (!empty($changes)) {
            \App\Services\ActivityLogger::log(
                'merchant',
                $merchant,
                'updated',
                null,
                Auth::check() ? Auth::user()->ulid : null,
                $changes
            );
        }

        return response()->json([
            'message' => 'Merchant profile updated successfully',
            'merchant' => $merchant
        ]);
    }

    /**
     * Toggle merchant's interest in an item
     */
    public function toggleItemInterest(Request $request, $itemId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $merchant = Merchant::where('user_id', $user->ulid)->first();

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        try {
            // Check if the merchant is already interested in this item using DB query
            $existingInterest = \DB::table('merchant_item_interests')
                ->where('merchant_id', $merchant->ulid)
                ->where('item_id', $itemId)
                ->first();

            if ($existingInterest) {
                // Remove interest
                $deleted = \DB::table('merchant_item_interests')
                    ->where('merchant_id', $merchant->ulid)
                    ->where('item_id', $itemId)
                    ->delete();
                
                if (!$deleted) {
                    throw new \Exception('Failed to remove item interest');
                }
                
                $status = 'removed';
            } else {
                // Add interest
                \DB::table('merchant_item_interests')->insert([
                    'merchant_id' => $merchant->ulid,
                    'item_id' => $itemId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $status = 'added';
            }
        } catch (\Exception $e) {
            Log::error('Failed to toggle item interest:', [
                'merchant_id' => $merchant->ulid,
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to update item interest'
            ], 500);
        }

        return response()->json([
            'status' => $status,
            'message' => $status === 'added' ? 'Item interest added successfully' : 'Item interest removed successfully'
        ]);
    }

    /**
     * Get all items that the merchant is interested in
     */
    public function getInterestedItems()
    {
        $user = Auth::user();
        $merchant = Merchant::where('user_id', $user->ulid)->first();

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        // Get items using join to ensure proper ULID handling
        $items = Item::join('merchant_item_interests', 'items.id', '=', 'merchant_item_interests.item_id')
            ->where('merchant_item_interests.merchant_id', $merchant->ulid)
            ->select('items.*')
            ->get();

        return response()->json($items);
    }

    /**
     * Remove the specified Merchant.
     */
    public function destroy(string $ulid): \Illuminate\Http\JsonResponse
    {
        $merchant = Merchant::where('ulid', $ulid)->firstOrFail();
        
        // Log merchant deletion by admin
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            \App\Services\ActivityLogger::logMerchant($merchant, 'deleted', "Admin removed merchant: {$merchant->business_name}");
        }
        
        $merchant->delete();

        return response()->json(['message' => 'Merchant deleted successfully'], 200);
    }
}