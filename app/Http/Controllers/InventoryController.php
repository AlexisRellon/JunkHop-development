<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryUpdate;
use App\Models\Merchant;
use App\Models\MerchantNotificationPreference;
use App\Notifications\InventoryUpdated;
use App\Notifications\PriceChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class InventoryController extends Controller
{
    /**
     * Get inventory items for a specific junkshop with filters.
     */
    public function index(Request $request, string $junkshopUlid): JsonResponse
    {
        $query = Item::query()
            ->where('junkshop_id', $junkshopUlid)
            ->with(['junkshop']);
        
        // Apply filters
        if ($request->has('available')) {
            $query->where('is_available', filter_var($request->available, FILTER_VALIDATE_BOOLEAN));
        }
        
        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }
        
        if ($request->has('min_quantity')) {
            $query->where('quantity', '>=', $request->min_quantity);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);
        
        $items = $query->get();
        
        return response()->json($items);
    }

    /**
     * Get a specific inventory item with its update history.
     */
    public function show(string $junkshopUlid, int $itemId): JsonResponse
    {
        $item = Item::where('junkshop_id', $junkshopUlid)
            ->where('id', $itemId)
            ->with(['junkshop'])
            ->first();
        
        if (!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }
        
        // Get recent inventory updates
        $updates = $item->getRecentUpdates();
        
        return response()->json([
            'item' => $item,
            'recent_updates' => $updates
        ]);
    }

    /**
     * Update inventory quantity and/or price for an item.
     */
    public function update(Request $request, string $junkshopUlid, int $itemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|numeric|min:0',
            'price' => 'sometimes|numeric|min:0',
            'grade' => 'sometimes|nullable|string|max:2',
            'is_available' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $junkshop = $user->junkshop;

        if (!$junkshop || $junkshop->ulid !== $junkshopUlid) {
            return response()->json([
                'message' => 'You do not have permission to update this inventory'
            ], 403);
        }

        $item = Item::where('junkshop_id', $junkshopUlid)
            ->where('id', $itemId)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        $updateData = [];
        $updateType = 'update';
        $quantityChanged = false;
        $priceChanged = false;

        // Handle quantity update
        if ($request->has('quantity') && $request->quantity != $item->quantity) {
            $previousQuantity = $item->quantity;
            $newQuantity = $request->quantity;
            $updateData['quantity'] = $newQuantity;
            $updateData['is_available'] = $newQuantity > 0;
            $quantityChanged = true;
            
            $updateType = $newQuantity > $previousQuantity ? 'add' : 'remove';
        }

        // Handle price update
        if ($request->has('price') && $request->price != $item->price) {
            $updateData['price'] = $request->price;
            $priceChanged = true;
            
            if (!$quantityChanged) {
                $updateType = 'price_change';
            }
        }

        // Handle grade update
        if ($request->has('grade')) {
            $updateData['grade'] = $request->grade;
        }

        // Handle availability update
        if ($request->has('is_available') && $request->is_available != $item->is_available) {
            $updateData['is_available'] = $request->is_available;
            
            if (!$quantityChanged && !$priceChanged) {
                $updateType = $request->is_available ? 'restock' : 'out_of_stock';
            }
        }

        // Update the item if there are changes
        if (!empty($updateData)) {
            $updateData['inventory_updated_at'] = now();
            $item->update($updateData);
            
            // Record the inventory update
            $inventoryUpdate = InventoryUpdate::create([
                'item_id' => $item->id,
                'previous_quantity' => $quantityChanged ? $previousQuantity : $item->quantity,
                'new_quantity' => $quantityChanged ? $newQuantity : $item->quantity,
                'previous_price' => $priceChanged ? $item->getOriginal('price') : $item->price,
                'new_price' => $priceChanged ? $request->price : $item->price,
                'update_type' => $updateType,
                'source' => 'manual',
                'notes' => $request->notes,
            ]);
            
            // Send notifications to interested merchants
            $this->notifyInterestedMerchants($item, $inventoryUpdate);

            return response()->json([
                'message' => 'Inventory updated successfully',
                'item' => $item,
                'update' => $inventoryUpdate
            ]);
        }

        return response()->json([
            'message' => 'No changes made to inventory',
            'item' => $item
        ]);
    }

    /**
     * Get inventory history for a junkshop.
     */
    public function history(Request $request, string $junkshopUlid): JsonResponse
    {
        $items = Item::where('junkshop_id', $junkshopUlid)->pluck('id');
        
        $query = InventoryUpdate::whereIn('item_id', $items)
            ->with(['item']);
        
        // Apply filters
        if ($request->has('update_type')) {
            $query->where('update_type', $request->update_type);
        }
        
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Apply pagination
        $perPage = $request->get('per_page', 15);
        $updates = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return response()->json($updates);
    }

    /**
     * Get notification preferences for the current merchant.
     */
    public function getNotificationPreferences(): JsonResponse
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $preferences = MerchantNotificationPreference::firstOrCreate(
            ['merchant_id' => $merchant->ulid],
            [
                'notify_on_inventory_update' => true,
                'notify_on_price_change' => true,
                'notify_on_bid_response' => true,
                'notify_on_wanted_material_match' => true,
                'interested_item_ids' => []
            ]
        );

        return response()->json($preferences);
    }

    /**
     * Update notification preferences for the current merchant.
     */
    public function updateNotificationPreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notify_on_inventory_update' => 'sometimes|boolean',
            'notify_on_price_change' => 'sometimes|boolean',
            'notify_on_bid_response' => 'sometimes|boolean',
            'notify_on_wanted_material_match' => 'sometimes|boolean',
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

        $preferences = MerchantNotificationPreference::firstOrCreate(
            ['merchant_id' => $merchant->ulid]
        );

        $preferences->update($request->only([
            'notify_on_inventory_update',
            'notify_on_price_change',
            'notify_on_bid_response',
            'notify_on_wanted_material_match',
        ]));

        return response()->json([
            'message' => 'Notification preferences updated successfully',
            'preferences' => $preferences
        ]);
    }

    /**
     * Add an item to merchant's interested items list.
     */
    public function addInterestedItem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
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

        $preferences = MerchantNotificationPreference::firstOrCreate(
            ['merchant_id' => $merchant->ulid]
        );

        $preferences->addInterestedItem($request->item_id);

        return response()->json([
            'message' => 'Item added to interested items list',
            'interested_item_ids' => $preferences->interested_item_ids
        ]);
    }

    /**
     * Remove an item from merchant's interested items list.
     */
    public function removeInterestedItem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
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

        $preferences = MerchantNotificationPreference::firstOrCreate(
            ['merchant_id' => $merchant->ulid]
        );

        $preferences->removeInterestedItem($request->item_id);

        return response()->json([
            'message' => 'Item removed from interested items list',
            'interested_item_ids' => $preferences->interested_item_ids
        ]);
    }

    /**
     * Notify interested merchants about inventory updates.
     */
    private function notifyInterestedMerchants(Item $item, InventoryUpdate $update): void
    {
        // Get all merchants who have this item in their interested items list
        $interestedMerchants = Merchant::whereHas('notificationPreferences', function ($query) use ($item) {
            $query->whereJsonContains('interested_item_ids', $item->id);
        })->get();

        foreach ($interestedMerchants as $merchant) {
            $preferences = $merchant->notificationPreferences;
            
            // Check if merchant wants to be notified about inventory updates
            if ($update->isQuantityIncrease() && $preferences->notify_on_inventory_update) {
                $merchant->user->notify(new InventoryUpdated($item, $update));
            }
            
            // Check if merchant wants to be notified about price changes
            if ($update->isPriceDecrease() && $preferences->notify_on_price_change) {
                $merchant->user->notify(new PriceChanged($item, $update));
            }
        }
    }
}
