<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Item;
use App\Models\Junkshop;
use App\Models\WantedMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BidController extends Controller
{
    /**
     * Display a listing of bids.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        // Determine which bids to retrieve based on filter
        $filter = $request->input('filter', 'outgoing');
        
        if ($filter === 'outgoing') {
            // Bids made by this merchant
            $bids = Bid::with(['junkshop', 'item'])
                ->where('merchant_id', $merchant->ulid)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Bids on this merchant's wanted materials
            $wantedMaterialIds = WantedMaterial::where('merchant_id', $merchant->ulid)
                ->pluck('id');
            
            $bids = Bid::with(['junkshop', 'item', 'wantedMaterial'])
                ->whereIn('wanted_material_id', $wantedMaterialIds)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json($bids);
    }

    /**
     * Store a newly created bid.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'junkshop_id' => 'required|string|exists:junkshops,ulid',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'price_per_kg' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'expiry_date' => 'nullable|date|after:today',
            'is_bulk_order' => 'boolean',
            'wanted_material_id' => 'nullable|exists:wanted_materials,id',
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

        // Verify the junkshop exists
        $junkshop = Junkshop::where('ulid', $request->junkshop_id)->first();
        if (!$junkshop) {
            return response()->json([
                'message' => 'Junkshop not found'
            ], 404);
        }

        // Verify the item exists
        $item = Item::find($request->item_id);
        if (!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        // Check if bidding on a wanted material
        $wantedMaterialId = $request->wanted_material_id;
        if ($wantedMaterialId) {
            $wantedMaterial = WantedMaterial::find($wantedMaterialId);
            if (!$wantedMaterial || !$wantedMaterial->is_active) {
                return response()->json([
                    'message' => 'Wanted material listing not found or is inactive'
                ], 404);
            }
        }

        // Create the bid
        $bid = Bid::create([
            'ulid' => (string) Str::ulid(),
            'merchant_id' => $merchant->ulid,
            'junkshop_id' => $request->junkshop_id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'price_per_kg' => $request->price_per_kg,
            'notes' => $request->notes,
            'expiry_date' => $request->expiry_date,
            'status' => 'pending',
            'is_bulk_order' => $request->has('is_bulk_order') ? $request->is_bulk_order : false,
            'wanted_material_id' => $wantedMaterialId,
        ]);

        // Load relationships
        $bid->load(['junkshop', 'item']);

        return response()->json([
            'message' => 'Bid created successfully',
            'bid' => $bid,
        ], 201);
    }

    /**
     * Display the specified bid.
     */
    public function show(string $ulid): JsonResponse
    {
        $bid = Bid::where('ulid', $ulid)
            ->with(['merchant', 'junkshop', 'item', 'wantedMaterial'])
            ->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Check if user is the merchant who made the bid or owns the junkshop the bid was made to
        $isBidder = $user->merchant && $user->merchant->ulid === $bid->merchant_id;
        $isJunkshopOwner = $user->junkshop && $user->junkshop->ulid === $bid->junkshop_id;
        
        // Or check if user owns the wanted material associated with the bid
        $ownsWantedMaterial = false;
        if ($bid->wanted_material_id && $user->merchant) {
            $wantedMaterial = WantedMaterial::find($bid->wanted_material_id);
            $ownsWantedMaterial = $wantedMaterial && $wantedMaterial->merchant_id === $user->merchant->ulid;
        }

        if (!$isBidder && !$isJunkshopOwner && !$ownsWantedMaterial) {
            return response()->json([
                'message' => 'You do not have permission to view this bid'
            ], 403);
        }

        return response()->json($bid);
    }

    /**
     * Update the specified bid.
     */
    public function update(Request $request, string $ulid): JsonResponse
    {
        $bid = Bid::where('ulid', $ulid)->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        // Only the merchant who created the bid can update it
        if (!$merchant || $merchant->ulid !== $bid->merchant_id) {
            return response()->json([
                'message' => 'You do not have permission to update this bid'
            ], 403);
        }

        // Bids can only be updated if they are pending
        if ($bid->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update bid that is not in pending status'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|numeric|min:0.1',
            'price_per_kg' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update the bid
        $bid->update($request->only([
            'quantity',
            'price_per_kg',
            'notes',
            'expiry_date',
        ]));

        // Load relationships
        $bid->load(['junkshop', 'item']);

        return response()->json([
            'message' => 'Bid updated successfully',
            'bid' => $bid,
        ]);
    }

    /**
     * Cancel a bid (by the merchant who created it).
     */
    public function cancel(string $ulid): JsonResponse
    {
        $bid = Bid::where('ulid', $ulid)->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        // Only the merchant who created the bid can cancel it
        if (!$merchant || $merchant->ulid !== $bid->merchant_id) {
            return response()->json([
                'message' => 'You do not have permission to cancel this bid'
            ], 403);
        }

        // Bids can only be cancelled if they are pending
        if ($bid->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot cancel bid that is not in pending status'
            ], 400);
        }

        // Update the bid status
        $bid->status = 'cancelled';
        $bid->save();

        return response()->json([
            'message' => 'Bid cancelled successfully'
        ]);
    }

    /**
     * Accept a bid (by the junkshop owner or merchant with wanted material).
     */
    public function accept(string $ulid): JsonResponse
    {
        $bid = Bid::where('ulid', $ulid)->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Check if user owns the junkshop the bid was made to
        $isJunkshopOwner = $user->junkshop && $user->junkshop->ulid === $bid->junkshop_id;
        
        // Or check if user owns the wanted material associated with the bid
        $ownsWantedMaterial = false;
        if ($bid->wanted_material_id && $user->merchant) {
            $wantedMaterial = WantedMaterial::find($bid->wanted_material_id);
            $ownsWantedMaterial = $wantedMaterial && $wantedMaterial->merchant_id === $user->merchant->ulid;
        }

        if (!$isJunkshopOwner && !$ownsWantedMaterial) {
            return response()->json([
                'message' => 'You do not have permission to accept this bid'
            ], 403);
        }

        // Bids can only be accepted if they are pending
        if ($bid->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot accept bid that is not in pending status'
            ], 400);
        }

        // Update the bid status
        $bid->status = 'accepted';
        $bid->accepted_at = now();
        $bid->save();

        // TODO: Create a transaction or notification here

        return response()->json([
            'message' => 'Bid accepted successfully'
        ]);
    }

    /**
     * Reject a bid (by the junkshop owner or merchant with wanted material).
     */
    public function reject(Request $request, string $ulid): JsonResponse
    {
        $bid = Bid::where('ulid', $ulid)->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Check if user owns the junkshop the bid was made to
        $isJunkshopOwner = $user->junkshop && $user->junkshop->ulid === $bid->junkshop_id;
        
        // Or check if user owns the wanted material associated with the bid
        $ownsWantedMaterial = false;
        if ($bid->wanted_material_id && $user->merchant) {
            $wantedMaterial = WantedMaterial::find($bid->wanted_material_id);
            $ownsWantedMaterial = $wantedMaterial && $wantedMaterial->merchant_id === $user->merchant->ulid;
        }

        if (!$isJunkshopOwner && !$ownsWantedMaterial) {
            return response()->json([
                'message' => 'You do not have permission to reject this bid'
            ], 403);
        }

        // Bids can only be rejected if they are pending
        if ($bid->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot reject bid that is not in pending status'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update the bid status
        $bid->status = 'rejected';
        $bid->rejected_at = now();
        $bid->rejection_reason = $request->rejection_reason;
        $bid->save();

        return response()->json([
            'message' => 'Bid rejected successfully'
        ]);
    }

    /**
     * Mark a bid as completed (after the transaction is done).
     */
    public function complete(string $ulid): JsonResponse
    {
        $bid = Bid::where('ulid', $ulid)->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Check if user owns the junkshop or is the merchant
        $isJunkshopOwner = $user->junkshop && $user->junkshop->ulid === $bid->junkshop_id;
        $isMerchant = $user->merchant && $user->merchant->ulid === $bid->merchant_id;

        if (!$isJunkshopOwner && !$isMerchant) {
            return response()->json([
                'message' => 'You do not have permission to complete this bid'
            ], 403);
        }

        // Bids can only be completed if they are accepted
        if ($bid->status !== 'accepted') {
            return response()->json([
                'message' => 'Cannot complete bid that is not in accepted status'
            ], 400);
        }

        // Update the bid status
        $bid->status = 'completed';
        $bid->save();

        // TODO: Create a transaction record or notification here

        return response()->json([
            'message' => 'Bid marked as completed successfully'
        ]);
    }    /**
     * Get all bids for a specific junkshop by its ULID.
     */
    public function getJunkshopBidsByUlid(Request $request, $ulid): JsonResponse
    {
        $user = Auth::user();
        $junkshop = Junkshop::where('ulid', $ulid)->first();

        if (!$junkshop) {
            return response()->json([
                'message' => 'Junkshop not found'
            ], 404);
        }

        // Verify the user owns this junkshop
        if ($junkshop->user_id !== $user->ulid) {
            return response()->json([
                'message' => 'Unauthorized access to junkshop'
            ], 403);
        }

        // Get bids for this junkshop
        $bids = Bid::with(['item'])
            ->where('junkshop_id', $junkshop->ulid)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bids);
    }

    /**
     * Create a new bid from a junkshop by its ULID.
     */
    public function createJunkshopBidByUlid(Request $request, $ulid): JsonResponse
    {        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:pending,accepted,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $junkshop = Junkshop::where('ulid', $ulid)->first();

        if (!$junkshop) {
            return response()->json([
                'message' => 'Junkshop not found'
            ], 404);
        }        // Verify the user owns this junkshop
        if ($junkshop->user_id !== $user->ulid) {
            return response()->json([
                'message' => 'Unauthorized access to junkshop'
            ], 403);
        }

        // Verify the item exists and belongs to this junkshop
        $item = Item::join('junkshop_items', 'items.id', '=', 'junkshop_items.item_id')
            ->where('items.id', $request->item_id)
            ->where('junkshop_items.junkshop_id', $junkshop->ulid)
            ->select('items.*')
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item not found or does not belong to this junkshop'
            ], 404);
        }        // Create the bid
        $bid = new Bid();
        $bid->ulid = (string) Str::uuid();
        $bid->junkshop_id = $junkshop->ulid;
        $bid->merchant_id = $user->merchant ? $user->merchant->ulid : null;
        $bid->item_id = $request->item_id;
        $bid->quantity = $request->quantity;
        $bid->price_per_kg = $request->price_per_kg;
        $bid->notes = $request->notes;
        $bid->status = $request->status ?? 'pending';
        $bid->save();

        // Load the item relationship
        $bid->load('item');

        return response()->json($bid, 201);
    }

    /**
     * Update a junkshop bid's status.
     */
    public function updateJunkshopBidStatus(Request $request, $ulid, $bidId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $junkshop = Junkshop::where('ulid', $ulid)->first();

        if (!$junkshop) {
            return response()->json([
                'message' => 'Junkshop not found'
            ], 404);
        }

        // Verify the user owns this junkshop
        if ($junkshop->user_id !== $user->ulid) {
            return response()->json([
                'message' => 'Unauthorized access to junkshop'
            ], 403);
        }

        // Find the bid
        $bid = Bid::where('id', $bidId)
            ->where('junkshop_id', $junkshop->ulid)
            ->first();

        if (!$bid) {
            return response()->json([
                'message' => 'Bid not found or does not belong to this junkshop'
            ], 404);
        }

        // Update status and related timestamps
        $bid->status = $request->status;
        
        if ($request->status === 'accepted') {
            $bid->accepted_at = now();
        } else if ($request->status === 'rejected') {
            $bid->rejected_at = now();
        }

        $bid->save();        // Load the item relationship        $bid->load('item');

        return response()->json($bid);
    }
}
