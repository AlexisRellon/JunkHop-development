<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\BidSubscription;
use App\Models\BidRenewalHistory;
use App\Models\MerchantSubscriptionPreference;
use App\Models\Item;
use App\Notifications\BidRenewalNotification;
use App\Notifications\RenewalUpcomingNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Get all subscriptions for the authenticated merchant.
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

        // Apply filters
        $query = BidSubscription::with(['junkshop', 'item'])
            ->where('merchant_id', $merchant->ulid);
        
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        if ($request->has('frequency')) {
            $query->where('frequency', $request->frequency);
        }
        
        $subscriptions = $query->orderBy('next_renewal_date', 'asc')->get();

        return response()->json($subscriptions);
    }

    /**
     * Store a new subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'junkshop_id' => 'required|string|exists:junkshops,ulid',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'price_per_kg' => 'required|numeric|min:0',
            'frequency' => 'required|in:weekly,biweekly,monthly',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'max_renewals' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'renewal_settings' => 'nullable|array',
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

        $startDate = Carbon::parse($request->start_date);

        // Create the subscription
        $subscription = BidSubscription::create([
            'ulid' => (string) Str::ulid(),
            'merchant_id' => $merchant->ulid,
            'junkshop_id' => $request->junkshop_id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'price_per_kg' => $request->price_per_kg,
            'frequency' => $request->frequency,
            'start_date' => $startDate,
            'next_renewal_date' => $startDate,
            'end_date' => $request->end_date,
            'max_renewals' => $request->max_renewals,
            'renewals_count' => 0,
            'is_active' => true,
            'notes' => $request->notes,
            'renewal_settings' => $request->renewal_settings,
        ]);

        // Create the initial bid
        $bid = Bid::create([
            'ulid' => (string) Str::ulid(),
            'merchant_id' => $merchant->ulid,
            'junkshop_id' => $request->junkshop_id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'price_per_kg' => $request->price_per_kg,
            'notes' => $request->notes . "\n(Created from subscription #" . $subscription->ulid . ")",
            'expiry_date' => $startDate,
            'status' => 'pending',
            'allow_auto_renewal' => true,
            'bid_subscription_id' => $subscription->id,
        ]);

        // Load relationships
        $subscription->load(['junkshop', 'item']);

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription,
            'initial_bid' => $bid,
        ], 201);
    }

    /**
     * Display the specified subscription.
     */
    public function show(string $ulid): JsonResponse
    {
        $subscription = BidSubscription::where('ulid', $ulid)
            ->with(['junkshop', 'item', 'bids', 'renewalHistory'])
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        // Only the merchant who created the subscription can view it
        if (!$merchant || $merchant->ulid !== $subscription->merchant_id) {
            return response()->json([
                'message' => 'You do not have permission to view this subscription'
            ], 403);
        }

        return response()->json($subscription);
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, string $ulid): JsonResponse
    {
        $subscription = BidSubscription::where('ulid', $ulid)->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        // Only the merchant who created the subscription can update it
        if (!$merchant || $merchant->ulid !== $subscription->merchant_id) {
            return response()->json([
                'message' => 'You do not have permission to update this subscription'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|numeric|min:0.1',
            'price_per_kg' => 'sometimes|numeric|min:0',
            'frequency' => 'sometimes|in:weekly,biweekly,monthly',
            'end_date' => 'nullable|date|after:today',
            'max_renewals' => 'nullable|integer|min:' . $subscription->renewals_count,
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'renewal_settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update the subscription
        $subscription->update($request->only([
            'quantity',
            'price_per_kg',
            'frequency',
            'end_date',
            'max_renewals',
            'is_active',
            'notes',
            'renewal_settings',
        ]));

        // Recalculate next renewal date if frequency changed
        if ($request->has('frequency') && $request->frequency !== $subscription->getOriginal('frequency')) {
            $subscription->updateNextRenewalDate();
        }

        // Load relationships
        $subscription->load(['junkshop', 'item']);

        return response()->json([
            'message' => 'Subscription updated successfully',
            'subscription' => $subscription,
        ]);
    }

    /**
     * Cancel the specified subscription.
     */
    public function cancel(string $ulid): JsonResponse
    {
        $subscription = BidSubscription::where('ulid', $ulid)->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        // Only the merchant who created the subscription can cancel it
        if (!$merchant || $merchant->ulid !== $subscription->merchant_id) {
            return response()->json([
                'message' => 'You do not have permission to cancel this subscription'
            ], 403);
        }

        // Cancel the subscription
        $subscription->is_active = false;
        $subscription->save();

        return response()->json([
            'message' => 'Subscription cancelled successfully'
        ]);
    }

    /**
     * Get subscription preferences for the current merchant.
     */
    public function getPreferences(): JsonResponse
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $preferences = MerchantSubscriptionPreference::firstOrCreate(
            ['merchant_id' => $merchant->ulid],
            [
                'default_allow_auto_renewal' => false,
                'max_price_increase_percent' => 5.00,
                'notify_before_renewal' => true,
                'renewal_notification_days' => 3,
            ]
        );

        return response()->json($preferences);
    }

    /**
     * Update subscription preferences for the current merchant.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'default_allow_auto_renewal' => 'sometimes|boolean',
            'max_price_increase_percent' => 'sometimes|numeric|min:0|max:100',
            'notify_before_renewal' => 'sometimes|boolean',
            'renewal_notification_days' => 'sometimes|integer|min:1|max:14',
            'renewal_settings' => 'nullable|array',
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

        $preferences = MerchantSubscriptionPreference::firstOrCreate(
            ['merchant_id' => $merchant->ulid]
        );

        $preferences->update($request->only([
            'default_allow_auto_renewal',
            'max_price_increase_percent',
            'notify_before_renewal',
            'renewal_notification_days',
            'renewal_settings',
        ]));

        return response()->json([
            'message' => 'Subscription preferences updated successfully',
            'preferences' => $preferences
        ]);
    }

    /**
     * Manually trigger a renewal for a subscription.
     */
    public function renewNow(string $ulid): JsonResponse
    {
        $subscription = BidSubscription::where('ulid', $ulid)->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        // Only the merchant who created the subscription can renew it
        if (!$merchant || $merchant->ulid !== $subscription->merchant_id) {
            return response()->json([
                'message' => 'You do not have permission to renew this subscription'
            ], 403);
        }

        // Check if the subscription is active
        if (!$subscription->is_active) {
            return response()->json([
                'message' => 'Cannot renew an inactive subscription'
            ], 400);
        }

        // Attempt to renew the subscription
        $result = $this->processRenewal($subscription, true);

        if ($result['success']) {
            return response()->json([
                'message' => 'Subscription renewed successfully',
                'new_bid' => $result['bid'],
                'next_renewal_date' => $subscription->next_renewal_date,
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to renew subscription: ' . $result['message']
            ], 400);
        }
    }

    /**
     * Get renewal history for a subscription.
     */
    public function getRenewalHistory(string $ulid): JsonResponse
    {
        $subscription = BidSubscription::where('ulid', $ulid)->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $user = Auth::user();
        $merchant = $user->merchant;

        // Only the merchant who created the subscription can view its history
        if (!$merchant || $merchant->ulid !== $subscription->merchant_id) {
            return response()->json([
                'message' => 'You do not have permission to view this subscription'
            ], 403);
        }

        $history = BidRenewalHistory::where('bid_subscription_id', $subscription->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }

    /**
     * Process renewals - can be called by scheduler or manually.
     */
    protected function processRenewal(BidSubscription $subscription, bool $isManual = false)
    {
        // Default result
        $result = [
            'success' => false,
            'message' => '',
            'bid' => null
        ];

        try {
            // Check if the item is still available
            $item = Item::find($subscription->item_id);
            if (!$item || !$item->is_available) {
                // Log failure and record history
                $this->recordRenewalHistory($subscription, 'failed', 'Item is no longer available', null);
                $result['message'] = 'Item is no longer available';
                return $result;
            }

            // Create the new bid
            $bid = Bid::create([
                'ulid' => (string) Str::ulid(),
                'merchant_id' => $subscription->merchant_id,
                'junkshop_id' => $subscription->junkshop_id,
                'item_id' => $subscription->item_id,
                'quantity' => $subscription->quantity,
                'price_per_kg' => $subscription->price_per_kg,
                'notes' => $subscription->notes . "\n(Auto-renewed from subscription #" . $subscription->ulid . ")",
                'expiry_date' => Carbon::now()->addDays(7), // Default 7-day expiry
                'status' => 'pending',
                'allow_auto_renewal' => true,
                'bid_subscription_id' => $subscription->id,
                'auto_renewed_at' => Carbon::now(),
            ]);

            // Update subscription info
            $subscription->incrementRenewalsCount();
            $subscription->updateNextRenewalDate();

            // Record successful renewal
            $this->recordRenewalHistory($subscription, 'success', null, $bid->ulid);

            // Send notification
            $subscription->merchant->user->notify(new BidRenewalNotification($subscription, $bid));

            $result = [
                'success' => true,
                'message' => 'Renewal successful',
                'bid' => $bid
            ];
        } catch (\Exception $e) {
            Log::error('Bid renewal failed: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
                'subscription_ulid' => $subscription->ulid,
                'exception' => $e
            ]);

            // Record failed renewal
            $this->recordRenewalHistory($subscription, 'failed', $e->getMessage(), null);

            $result['message'] = 'An error occurred during renewal';
        }

        return $result;
    }

    /**
     * Record a renewal attempt in the history.
     */
    protected function recordRenewalHistory(BidSubscription $subscription, string $status, ?string $details, ?string $bidUlid)
    {
        return BidRenewalHistory::create([
            'ulid' => (string) Str::ulid(),
            'bid_subscription_id' => $subscription->id,
            'new_bid_ulid' => $bidUlid,
            'renewal_date' => Carbon::today(),
            'status' => $status,
            'status_details' => $details,
            'renewal_data' => [
                'quantity' => $subscription->quantity,
                'price_per_kg' => $subscription->price_per_kg,
                'frequency' => $subscription->frequency,
                'renewal_count' => $subscription->renewals_count,
            ],
        ]);
    }

    /**
     * Check for upcoming renewals and send notifications.
     * This method would be called by a scheduled task.
     */
    public function checkUpcomingRenewals()
    {
        $preferences = MerchantSubscriptionPreference::where('notify_before_renewal', true)->get();

        foreach ($preferences as $preference) {
            $notificationDate = Carbon::today()->addDays($preference->renewal_notification_days);
            
            // Find subscriptions for this merchant due for renewal on the notification date
            $subscriptions = BidSubscription::where('merchant_id', $preference->merchant_id)
                ->where('is_active', true)
                ->where('next_renewal_date', $notificationDate)
                ->get();
            
            foreach ($subscriptions as $subscription) {
                // Send notification
                $subscription->merchant->user->notify(new RenewalUpcomingNotification($subscription));
            }
        }

        return response()->json([
            'message' => 'Upcoming renewal notifications processed'
        ]);
    }

    /**
     * Process all due renewals.
     * This method would be called by a scheduled task.
     */
    public function processDueRenewals()
    {
        $dueSubscriptions = BidSubscription::dueForRenewal()->get();
        $results = [
            'total' => $dueSubscriptions->count(),
            'successful' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($dueSubscriptions as $subscription) {
            $result = $this->processRenewal($subscription);
            
            if ($result['success']) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'subscription_ulid' => $subscription->ulid,
                'success' => $result['success'],
                'message' => $result['message']
            ];
        }

        return response()->json([
            'message' => 'Due renewals processed',
            'results' => $results
        ]);
    }
}
