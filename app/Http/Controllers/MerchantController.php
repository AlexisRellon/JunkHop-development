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
        $merchant->load(['junkshops', 'items']);

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

        $merchant->update($request->all());

        return response()->json([
            'message' => 'Merchant profile updated successfully',
            'merchant' => $merchant
        ]);
    }

    /**
     * Connect with a junkshop
     */
    public function connectWithJunkshop(Request $request, $junkshopId)
    {
        $user = Auth::user();
        $merchant = Merchant::where('user_id', $user->ulid)->first();

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $junkshop = Junkshop::findByUlid($junkshopId);

        if (!$junkshop) {
            return response()->json([
                'message' => 'Junkshop not found'
            ], 404);
        }

        // Check if already connected
        if ($merchant->junkshops()->where('junkshop_id', $junkshop->ulid)->exists()) {
            // Remove the connection if it exists
            $merchant->junkshops()->detach($junkshop->ulid);
            
            return response()->json([
                'message' => 'Connection with junkshop removed',
                'status' => 'disconnected'
            ]);
        } else {
            // Create the connection
            $merchant->junkshops()->attach($junkshop->ulid);
            
            return response()->json([
                'message' => 'Connected with junkshop successfully',
                'status' => 'connected'
            ]);
        }
    }

    /**
     * Express interest in a recyclable item
     */
    public function toggleItemInterest(Request $request, $itemId)
    {
        $user = Auth::user();
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

        // Check if already interested
        if ($merchant->items()->where('item_id', $item->id)->exists()) {
            // Remove the interest
            $merchant->items()->detach($item->id);
            
            return response()->json([
                'message' => 'Interest in item removed',
                'status' => 'removed'
            ]);
        } else {
            // Add the interest
            $merchant->items()->attach($item->id);
            
            return response()->json([
                'message' => 'Interest in item added',
                'status' => 'added'
            ]);
        }
    }

    /**
     * Get junkshops the merchant is connected with
     */
    public function getConnectedJunkshops()
    {
        $user = Auth::user();
        $merchant = Merchant::where('user_id', $user->ulid)->first();

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $junkshops = $merchant->junkshops()->with('items')->get();

        return response()->json($junkshops);
    }

    /**
     * Get items the merchant is interested in
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

        $items = $merchant->items;

        return response()->json($items);
    }
}