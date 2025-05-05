<?php

namespace App\Http\Controllers;

use App\Models\Junkshop;
use App\Models\JunkshopItem;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JunkshopItemController extends Controller
{
    /**
     * List inventory items for a specific junkshop.
     */
    public function index(string $junkshopUlid): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $junkshopUlid)->firstOrFail();
        
        $inventoryItems = JunkshopItem::where('junkshop_id', $junkshop->id)
            ->with('item')
            ->get();
            
        return response()->json([
            'junkshop' => $junkshop->only(['ulid', 'name']),
            'inventory' => $inventoryItems
        ]);
    }

    /**
     * Add a new item to junkshop inventory.
     */
    public function store(Request $request, string $junkshopUlid): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $junkshopUlid)->firstOrFail();
        
        // Validate request data
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:0',
            'grade' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
        ]);
        
        // Check if item already exists in inventory
        $existingItem = JunkshopItem::where('junkshop_id', $junkshop->id)
            ->where('item_id', $request->item_id)
            ->first();
            
        if ($existingItem) {
            return response()->json([
                'message' => 'Item already exists in inventory. Use update method to modify quantity or price.',
                'item' => $existingItem
            ], 422);
        }
        
        // Get the item name
        $item = Item::findOrFail($request->item_id);
        
        // Create new inventory item
        $junkshopItem = JunkshopItem::create([
            'junkshop_id' => $junkshop->id,
            'item_id' => $request->item_id,
            'name' => $item->name,
            'quantity' => $request->quantity,
            'grade' => $request->grade,
            'price' => $request->price,
        ]);
        
        return response()->json($junkshopItem, 201);
    }
    
    /**
     * Update inventory item details.
     */
    public function update(Request $request, string $junkshopUlid, int $itemId): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $junkshopUlid)->firstOrFail();
        
        // Validate request data
        $request->validate([
            'quantity' => 'sometimes|integer|min:0',
            'grade' => 'nullable|string|max:50',
            'price' => 'sometimes|numeric|min:0',
        ]);
        
        // Find the inventory item
        $junkshopItem = JunkshopItem::where('junkshop_id', $junkshop->id)
            ->where('item_id', $itemId)
            ->firstOrFail();
            
        // Update inventory item
        $junkshopItem->update([
            'quantity' => $request->quantity ?? $junkshopItem->quantity,
            'grade' => $request->has('grade') ? $request->grade : $junkshopItem->grade,
            'price' => $request->price ?? $junkshopItem->price,
        ]);
        
        return response()->json($junkshopItem);
    }
    
    /**
     * Remove an item from junkshop inventory.
     */
    public function destroy(string $junkshopUlid, int $itemId): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $junkshopUlid)->firstOrFail();
        
        // Find and delete the inventory item
        $deleted = JunkshopItem::where('junkshop_id', $junkshop->id)
            ->where('item_id', $itemId)
            ->delete();
            
        if (!$deleted) {
            return response()->json([
                'message' => 'Item not found in inventory'
            ], 404);
        }
        
        return response()->json([
            'message' => 'Item removed from inventory successfully'
        ]);
    }
}
