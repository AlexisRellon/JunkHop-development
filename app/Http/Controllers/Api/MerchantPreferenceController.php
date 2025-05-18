<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantPreferenceController extends Controller
{
    /**
     * Get merchant's preferences
     */
    public function index(Request $request)
    {
        return MerchantPreference::where('merchant_id', $request->user()->ulid)
            ->with('item')
            ->get();
    }

    /**
     * Save merchant's preferences
     */
    public function store(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*.item_id' => 'required|exists:items,id',
            'preferences.*.min_price' => 'required|numeric|min:0',
            'preferences.*.max_price' => 'required|numeric|min:0|gte:preferences.*.min_price',
        ]);

        try {
            DB::beginTransaction();

            // Delete existing preferences
            MerchantPreference::where('merchant_id', $request->user()->ulid)->delete();

            // Create new preferences
            foreach ($request->preferences as $preference) {
                MerchantPreference::create([
                    'merchant_id' => $request->user()->ulid,
                    'item_id' => $preference['item_id'],
                    'min_price' => $preference['min_price'],
                    'max_price' => $preference['max_price'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Preferences saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save preferences'], 500);
        }
    }
}
