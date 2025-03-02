<?php

namespace App\Http\Controllers;

use App\Models\Junkshop;
use App\Models\User;
use App\Models\JunkshopItem;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JunkshopController extends Controller
{
    /**
     * Display the specified Junkshop.
     */
    public function show(string $ulid): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();
        return response()->json($junkshop);
    }

    /**
     * Update the specified Junkshop.
     */
    public function update(Request $request, string $ulid): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'owner_ulid' => 'required|string|exists:users,ulid',
        ]);

        $owner = User::where('ulid', $request->owner_ulid)->firstOrFail();

        $junkshop->update([
            'name' => $request->name,
            'contact' => $request->contact,
            'description' => $request->description,
            'address' => $request->address,
            'user_id' => $owner->ulid, // Use ulid instead of id
        ]);

        return response()->json($junkshop);
    }

    /**
     * Create a new Junkshop.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'owner_ulid' => 'required|string|exists:users,ulid',
        ]);

        $owner = User::where('ulid', $validatedData['owner_ulid'])->firstOrFail();

        // Generate ULID for the junkshop
        $ulid = Str::ulid()->toBase32();

        // Create junkshop with proper field mapping
        $junkshop = new Junkshop();
        $junkshop->ulid = $ulid;
        $junkshop->name = $validatedData['name'];
        $junkshop->contact = $validatedData['contact'];
        $junkshop->description = $validatedData['description'] ?? '';
        $junkshop->address = $validatedData['address'];
        $junkshop->user_id = $owner->ulid;
        $junkshop->save();

        return response()->json($junkshop);
    }

    /**
     * Display a listing of the Junkshops.
     */
    public function index(): JsonResponse
    {
        $junkshops = Junkshop::all();
        return response()->json($junkshops);
    }

    /**
     * Remove the specified Junkshop.
     */
    public function destroy(string $ulid): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();
        $junkshop->delete();

        return response()->json(['message' => 'Junkshop deleted successfully'], 200);
    }

    /**
     * Display the items of the specified Junkshop.
     */
    public function items($ulid)
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();

        // Try a different approach using query builder with proper joins
        $items = \DB::table('junkshop_items')
            ->join('items', 'junkshop_items.item_id', '=', 'items.id')
            ->select(
                'junkshop_items.id',
                'items.name',
                'junkshop_items.junkshop_id',
                'junkshop_items.item_id',
                'junkshop_items.created_at',
                'junkshop_items.updated_at'
            )
            ->where('junkshop_items.junkshop_id', $junkshop->ulid)
            ->get();

        // Debug info
        \Log::info('Junkshop items query', [
            'junkshop_id' => $junkshop->ulid,
            'items_count' => $items->count(),
            'first_item' => $items->first(),
            'sql' => \DB::getQueryLog()
        ]);

        return response()->json($items);
    }

    /**
     * Add a new item to the junkshop
     */
    public function addItem(Request $request, $ulid)
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();

        // First create or find the item
        $item = Item::firstOrCreate(['name' => $request->name]);

        // Then create the pivot record
        $junkshopItem = JunkshopItem::create([
            'junkshop_id' => $junkshop->ulid,
            'item_id' => $item->id,
        ]);

        // Return the full item with name for the frontend
        return response()->json([
            'id' => $junkshopItem->id,
            'name' => $item->name,
            'junkshop_id' => $junkshop->ulid,
            'item_id' => $item->id,
        ]);
    }

    /**
     * Update an existing item
     */
    public function updateItem(Request $request, $ulid, $itemId)
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();

        // Find the pivot record
        $junkshopItem = JunkshopItem::where('id', $itemId)
            ->where('junkshop_id', $junkshop->ulid)
            ->firstOrFail();

        // Find and update the item name
        $item = Item::findOrFail($junkshopItem->item_id);
        $item->update(['name' => $request->name]);

        // Return the updated item with name
        return response()->json([
            'id' => $junkshopItem->id,
            'name' => $item->name,
            'junkshop_id' => $junkshop->ulid,
            'item_id' => $item->id,
        ]);
    }

    /**
     * Delete an item from the junkshop
     */
    public function deleteItem($ulid, $itemId)
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();

        // Find and delete the item
        $junkshopItem = JunkshopItem::where('id', $itemId)
            ->where('junkshop_id', $junkshop->ulid)
            ->firstOrFail();
        $junkshopItem->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}
