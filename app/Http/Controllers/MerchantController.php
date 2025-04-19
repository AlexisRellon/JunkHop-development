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
}