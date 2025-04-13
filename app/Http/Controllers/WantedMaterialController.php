<?php

namespace App\Http\Controllers;

use App\Models\WantedMaterial;
use App\Models\Item;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WantedMaterialController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view marketplace')->only(['index', 'show']);
        $this->middleware('permission:create marketplace listings')->only(['store']);
        $this->middleware('permission:edit own marketplace listings')->only(['update']);
        $this->middleware('permission:delete own marketplace listings')->only(['destroy']);
    }

    /**
     * Display a listing of wanted materials.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Filter by item type if provided
        $query = WantedMaterial::with(['merchant', 'item'])
            ->where('is_active', true);
            
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        
        // Return paginated results
        return $query->paginate(10);
    }

    /**
     * Display the merchant's own wanted materials.
     *
     * @return \Illuminate\Http\Response
     */
    public function myListings()
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        
        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }
        
        return $merchant->wantedMaterials()
            ->with('item')
            ->paginate(10);
    }

    /**
     * Store a newly created wanted material in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'grade' => 'nullable|string|max:50',
            'desired_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'expiry_date' => 'nullable|date|after:today',
        ]);
        
        $user = Auth::user();
        $merchant = $user->merchant;
        
        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }
        
        $wantedMaterial = new WantedMaterial($validated);
        $wantedMaterial->merchant_id = $merchant->ulid;
        $wantedMaterial->is_active = true;
        $wantedMaterial->save();
        
        return response()->json([
            'message' => 'Wanted material listing created successfully',
            'wanted_material' => $wantedMaterial->load('item')
        ], 201);
    }

    /**
     * Display the specified wanted material.
     *
     * @param  string  $ulid
     * @return \Illuminate\Http\Response
     */
    public function show($ulid)
    {
        $wantedMaterial = WantedMaterial::with(['merchant', 'item'])
            ->where('ulid', $ulid)
            ->firstOrFail();
            
        return response()->json($wantedMaterial);
    }

    /**
     * Update the specified wanted material in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ulid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $ulid)
    {
        $wantedMaterial = WantedMaterial::where('ulid', $ulid)->firstOrFail();
        
        // Authorization: Only the merchant who created the listing can update it
        $user = Auth::user();
        $merchant = $user->merchant;
        
        if (!$merchant || $wantedMaterial->merchant_id !== $merchant->ulid) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'quantity' => 'sometimes|numeric|min:0.01',
            'grade' => 'nullable|string|max:50',
            'desired_price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'expiry_date' => 'nullable|date|after:today',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $wantedMaterial->update($validated);
        
        return response()->json([
            'message' => 'Wanted material listing updated successfully',
            'wanted_material' => $wantedMaterial->fresh()->load('item')
        ]);
    }

    /**
     * Remove the specified wanted material from storage.
     *
     * @param  string  $ulid
     * @return \Illuminate\Http\Response
     */
    public function destroy($ulid)
    {
        $wantedMaterial = WantedMaterial::where('ulid', $ulid)->firstOrFail();
        
        // Authorization: Only the merchant who created the listing can delete it
        $user = Auth::user();
        $merchant = $user->merchant;
        
        if (!$merchant || $wantedMaterial->merchant_id !== $merchant->ulid) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $wantedMaterial->delete();
        
        return response()->json(['message' => 'Wanted material listing deleted successfully']);
    }
}
