<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Junkshop;
use App\Models\JunkshopItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the items for the specified Junkshop.
     */
    public function index(string $ulid): JsonResponse
    {
        // First try to find junkshop by ULID
        $junkshop = Junkshop::findByUlid($ulid);

        // If not found, try to find by user_id
        if (!$junkshop) {
            $junkshop = Junkshop::findByUserId($ulid);
        }

        if (!$junkshop) {
            return response()->json(['message' => 'Junkshop not found'], 404);
        }

        return response()->json($junkshop->items);
    }

    /**
     * Store a newly created item for the specified Junkshop.
     */
    public function store(Request $request, string $ulid): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item = Item::create(['name' => $request->name]);
        
        // Create a direct entry in the pivot table instead of using attach()
        $junkshopItem = new JunkshopItem([
            'junkshop_id' => $junkshop->ulid,
            'item_id' => $item->id
        ]);
        $junkshopItem->save();

        return response()->json([
            'id' => $junkshopItem->id,
            'name' => $item->name,
            'junkshop_id' => $junkshop->ulid,
            'item_id' => $item->id
        ]);
    }

    /**
     * Update the specified item for the specified Junkshop.
     */
    public function update(Request $request, string $ulid, int $itemId): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();
        
        // Find the pivot record directly to avoid ambiguous ID issue
        $junkshopItem = JunkshopItem::where('id', $itemId)
            ->where('junkshop_id', $junkshop->ulid)
            ->firstOrFail();
            
        // Get the associated item
        $item = Item::findOrFail($junkshopItem->item_id);
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item->update(['name' => $request->name]);

        return response()->json([
            'id' => $junkshopItem->id,
            'name' => $item->name,
            'junkshop_id' => $junkshop->ulid,
            'item_id' => $item->id
        ]);
    }

    /**
     * Remove the specified item from the specified Junkshop.
     */
    public function destroy(string $ulid, int $itemId): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();
        
        // Find and delete the pivot record directly instead of using detach
        JunkshopItem::where('id', $itemId)
            ->where('junkshop_id', $junkshop->ulid)
            ->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
