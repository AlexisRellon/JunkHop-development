<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\WantedMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WantedMaterialController extends Controller
{
    /**
     * Display a listing of all public wanted materials.
     */
    public function index(Request $request): JsonResponse
    {
        // Apply filters if provided
        $query = WantedMaterial::with(['merchant', 'item'])
            ->active()
            ->public()
            ->available();

        // Filter by item type
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by merchant
        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('desired_price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('desired_price', '<=', $request->max_price);
        }

        // Filter by grade
        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }

        $wantedMaterials = $query->orderBy('created_at', 'desc')->get();

        return response()->json($wantedMaterials);
    }

    /**
     * Display the current merchant's wanted material listings.
     */
    public function myListings(): JsonResponse
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $wantedMaterials = WantedMaterial::with(['item'])
            ->where('merchant_id', $merchant->ulid)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($wantedMaterials);
    }

    /**
     * Store a newly created wanted material listing.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0',
            'desired_price' => 'required|numeric|min:0',
            'grade' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date|after:today',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $wantedMaterial = WantedMaterial::create([
            'ulid' => (string) Str::ulid(),
            'merchant_id' => $merchant->ulid,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'desired_price' => $request->desired_price,
            'grade' => $request->grade,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'is_public' => $request->has('is_public') ? $request->is_public : true,
            'is_active' => true,
        ]);

        // Load the item relationship
        $wantedMaterial->load('item');

        return response()->json([
            'message' => 'Wanted material listing created successfully',
            'wanted_material' => $wantedMaterial,
        ], 201);
    }

    /**
     * Display the specified wanted material listing.
     */
    public function show(string $ulid): JsonResponse
    {
        $wantedMaterial = WantedMaterial::where('ulid', $ulid)
            ->with(['merchant', 'item'])
            ->first();

        if (!$wantedMaterial) {
            return response()->json([
                'message' => 'Wanted material listing not found'
            ], 404);
        }

        // Check if the listing is not public
        if (!$wantedMaterial->is_public) {
            $user = Auth::user();
            
            // If not the owner, don't show private listings
            if (!$user || !$user->merchant || $user->merchant->ulid !== $wantedMaterial->merchant_id) {
                return response()->json([
                    'message' => 'Access to this listing is restricted'
                ], 403);
            }
        }

        return response()->json($wantedMaterial);
    }

    /**
     * Update the specified wanted material listing.
     */
    public function update(Request $request, string $ulid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'sometimes|exists:items,id',
            'quantity' => 'sometimes|numeric|min:0',
            'desired_price' => 'sometimes|numeric|min:0',
            'grade' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date|after:today',
            'is_public' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $wantedMaterial = WantedMaterial::where('ulid', $ulid)->first();

        if (!$wantedMaterial) {
            return response()->json([
                'message' => 'Wanted material listing not found'
            ], 404);
        }

        // Check if the user is the owner of the listing
        if ($wantedMaterial->merchant_id !== $merchant->ulid) {
            return response()->json([
                'message' => 'You do not have permission to update this listing'
            ], 403);
        }

        $wantedMaterial->update($request->only([
            'item_id',
            'quantity',
            'desired_price',
            'grade',
            'description',
            'deadline',
            'is_public',
            'is_active',
        ]));

        // Reload the item relationship
        $wantedMaterial->load('item');

        return response()->json([
            'message' => 'Wanted material listing updated successfully',
            'wanted_material' => $wantedMaterial,
        ]);
    }

    /**
     * Remove the specified wanted material listing.
     */
    public function destroy(string $ulid): JsonResponse
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $wantedMaterial = WantedMaterial::where('ulid', $ulid)->first();

        if (!$wantedMaterial) {
            return response()->json([
                'message' => 'Wanted material listing not found'
            ], 404);
        }

        // Check if the user is the owner of the listing
        if ($wantedMaterial->merchant_id !== $merchant->ulid) {
            return response()->json([
                'message' => 'You do not have permission to delete this listing'
            ], 403);
        }

        $wantedMaterial->delete();

        return response()->json([
            'message' => 'Wanted material listing deleted successfully'
        ]);
    }

    /**
     * Toggle the active status of a wanted material listing.
     */
    public function toggleActive(string $ulid): JsonResponse
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $wantedMaterial = WantedMaterial::where('ulid', $ulid)->first();

        if (!$wantedMaterial) {
            return response()->json([
                'message' => 'Wanted material listing not found'
            ], 404);
        }

        // Check if the user is the owner of the listing
        if ($wantedMaterial->merchant_id !== $merchant->ulid) {
            return response()->json([
                'message' => 'You do not have permission to update this listing'
            ], 403);
        }

        $wantedMaterial->is_active = !$wantedMaterial->is_active;
        $wantedMaterial->save();

        return response()->json([
            'message' => $wantedMaterial->is_active 
                ? 'Wanted material listing activated' 
                : 'Wanted material listing deactivated',
            'is_active' => $wantedMaterial->is_active
        ]);
    }
}
