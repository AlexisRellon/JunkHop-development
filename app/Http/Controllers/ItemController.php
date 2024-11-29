<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Junkshop;
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
        $junkshop->items()->attach($item->id);

        return response()->json($item);
    }

    /**
     * Update the specified item for the specified Junkshop.
     */
    public function update(Request $request, string $ulid, int $itemId): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();
        $item = $junkshop->items()->where('id', $itemId)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item->update(['name' => $request->name]);

        return response()->json($item);
    }

    /**
     * Remove the specified item from the specified Junkshop.
     */
    public function destroy(string $ulid, int $itemId): JsonResponse
    {
        $junkshop = Junkshop::where('ulid', $ulid)->firstOrFail();
        $junkshop->items()->detach($itemId);

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
