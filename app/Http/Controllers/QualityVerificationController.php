<?php

namespace App\Http\Controllers;

use App\Models\QualityStandard;
use App\Models\QualityVerification;
use App\Models\VerificationImage;
use App\Models\VerificationMethod;
use App\Models\Item;
use App\Models\Bid;
use App\Notifications\VerificationRequestedNotification;
use App\Notifications\VerificationCompletedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QualityVerificationController extends Controller
{
    /**
     * Display a listing of verifications for the current merchant.
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
        $query = QualityVerification::query()
            ->with(['item', 'junkshop', 'images'])
            ->where('merchant_id', $merchant->ulid);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('junkshop_id')) {
            $query->where('junkshop_id', $request->junkshop_id);
        }
        
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        
        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        $verifications = $query->paginate($request->get('per_page', 10));

        return response()->json($verifications);
    }

    /**
     * Display a listing of verifications for a junkshop.
     */
    public function junkshopVerifications(Request $request): JsonResponse
    {
        $user = Auth::user();
        $junkshop = $user->junkshop;

        if (!$junkshop) {
            return response()->json([
                'message' => 'Junkshop profile not found'
            ], 404);
        }

        // Apply filters
        $query = QualityVerification::query()
            ->with(['item', 'merchant', 'images'])
            ->where('junkshop_id', $junkshop->ulid);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }
        
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        
        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        $verifications = $query->paginate($request->get('per_page', 10));

        return response()->json($verifications);
    }

    /**
     * Store a newly created verification request.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'junkshop_id' => 'required|string|exists:junkshops,ulid',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'grade' => 'nullable|string|max:2',
            'bid_id' => 'nullable|exists:bids,id',
            'notes' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
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

        // Create the verification request
        $verification = QualityVerification::create([
            'ulid' => (string) Str::ulid(),
            'merchant_id' => $merchant->ulid,
            'junkshop_id' => $request->junkshop_id,
            'item_id' => $request->item_id,
            'status' => 'pending',
            'quantity' => $request->quantity,
            'grade' => $request->grade,
            'bid_id' => $request->bid_id,
            'notes' => $request->notes,
        ]);

        // Process and save images if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('verification_images', 'public');
                
                $imageType = $request->input('image_types.' . $index, 'material');
                $caption = $request->input('image_captions.' . $index);
                
                $verification->images()->create([
                    'image_path' => $imagePath,
                    'image_type' => $imageType,
                    'caption' => $caption,
                ]);
            }
        }

        // Create history entry
        $verification->addToHistory('created', 'Verification request created by merchant', $user->id);

        // Notify the junkshop about the verification request
        $verification->junkshop->user->notify(new VerificationRequestedNotification($verification));

        // Load relationships
        $verification->load(['item', 'junkshop', 'images']);

        return response()->json([
            'message' => 'Verification request created successfully',
            'verification' => $verification,
        ], 201);
    }

    /**
     * Display the specified verification.
     */
    public function show(string $ulid): JsonResponse
    {
        $verification = QualityVerification::where('ulid', $ulid)
            ->with(['item', 'junkshop', 'merchant', 'images', 'history', 'bid'])
            ->first();

        if (!$verification) {
            return response()->json([
                'message' => 'Verification not found'
            ], 404);
        }

        $user = Auth::user();

        // Check permission - either the merchant who requested it or the junkshop it was sent to
        $isMerchant = $user->merchant && $user->merchant->ulid === $verification->merchant_id;
        $isJunkshop = $user->junkshop && $user->junkshop->ulid === $verification->junkshop_id;

        if (!$isMerchant && !$isJunkshop && !$user->hasRole('admin')) {
            return response()->json([
                'message' => 'You do not have permission to view this verification'
            ], 403);
        }

        // Get quality standard for the item and grade
        $qualityStandard = null;
        if ($verification->grade) {
            $qualityStandard = QualityStandard::where('item_id', $verification->item_id)
                ->where('grade', $verification->grade)
                ->first();
        }

        // Get verification methods for the item
        $verificationMethods = VerificationMethod::forItem($verification->item_id)
            ->active()
            ->get();

        return response()->json([
            'verification' => $verification,
            'quality_standard' => $qualityStandard,
            'verification_methods' => $verificationMethods,
        ]);
    }

    /**
     * Update the status of a verification (for junkshops).
     */
    public function updateStatus(Request $request, string $ulid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:in_progress,passed,failed',
            'purity_level' => 'nullable|numeric|min:0|max:100',
            'grade' => 'nullable|string|max:2',
            'verification_results' => 'nullable|array',
            'notes' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $verification = QualityVerification::where('ulid', $ulid)->first();

        if (!$verification) {
            return response()->json([
                'message' => 'Verification not found'
            ], 404);
        }

        $user = Auth::user();
        $junkshop = $user->junkshop;

        // Check permission - must be the junkshop this verification was sent to
        if (!$junkshop || $junkshop->ulid !== $verification->junkshop_id) {
            return response()->json([
                'message' => 'You do not have permission to update this verification'
            ], 403);
        }

        // Update verification status
        $status = $request->status;
        $notes = $request->notes;
        $results = $request->verification_results;

        if ($status === 'in_progress') {
            $verification->markAsInProgress($user->id, $notes);
        } elseif ($status === 'passed') {
            // Update grade and purity level if provided
            if ($request->has('grade')) {
                $verification->grade = $request->grade;
            }
            
            if ($request->has('purity_level')) {
                $verification->purity_level = $request->purity_level;
            }
            
            $verification->markAsPassed($user->id, $results, $notes);
        } elseif ($status === 'failed') {
            // Update grade and purity level if provided
            if ($request->has('grade')) {
                $verification->grade = $request->grade;
            }
            
            if ($request->has('purity_level')) {
                $verification->purity_level = $request->purity_level;
            }
            
            $verification->markAsFailed($user->id, $results, $notes);
        }

        // Process and save images if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('verification_images', 'public');
                
                $imageType = $request->input('image_types.' . $index, 'material');
                $caption = $request->input('image_captions.' . $index);
                
                $verification->images()->create([
                    'image_path' => $imagePath,
                    'image_type' => $imageType,
                    'caption' => $caption,
                ]);
            }
        }

        // Notify the merchant about the verification status change
        $verification->merchant->user->notify(new VerificationCompletedNotification($verification));

        // Load relationships
        $verification->load(['item', 'junkshop', 'merchant', 'images', 'history']);

        return response()->json([
            'message' => 'Verification status updated successfully',
            'verification' => $verification,
        ]);
    }

    /**
     * Get quality standards for an item.
     */
    public function getQualityStandards(int $itemId): JsonResponse
    {
        $standards = QualityStandard::forItem($itemId)
            ->active()
            ->get();

        return response()->json($standards);
    }

    /**
     * Get verification methods for an item.
     */
    public function getVerificationMethods(int $itemId): JsonResponse
    {
        $methods = VerificationMethod::forItem($itemId)
            ->active()
            ->get();

        return response()->json($methods);
    }
}
