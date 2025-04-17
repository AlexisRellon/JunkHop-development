<?php

namespace App\Http\Controllers;

use App\Models\MaterialVerification;
use App\Models\VerificationPhoto;
use App\Models\Junkshop;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialVerificationController extends Controller
{
    /**
     * Display a listing of the verifications for the merchant.
     */
    public function index()
    {
        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        $verifications = MaterialVerification::where('merchant_id', $merchant->ulid)
            ->with(['item', 'junkshop', 'photos'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($verification) {
                return [
                    'id' => $verification->id,
                    'ulid' => $verification->ulid,
                    'junkshopId' => $verification->junkshop_id,
                    'junkshopName' => $verification->junkshop->name,
                    'itemId' => $verification->item_id,
                    'itemName' => $verification->item->name,
                    'quantity' => $verification->quantity,
                    'price' => $verification->price,
                    'grade' => $verification->grade,
                    'verifiedGrade' => $verification->verified_grade,
                    'status' => $verification->status,
                    'isHighValue' => $verification->is_high_value,
                    'notes' => $verification->notes,
                    'createdAt' => $verification->created_at,
                    'photos' => $verification->photos->map(function ($photo) {
                        return $photo->url;
                    }),
                ];
            });

        return response()->json($verifications);
    }

    /**
     * Store a newly created verification in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'junkshop_id' => 'required|string|exists:junkshops,ulid',
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'grade' => 'required|string|max:10',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|max:10240', // 10MB limit per photo
        ]);

        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json([
                'message' => 'Merchant profile not found'
            ], 404);
        }

        // Create verification request
        $verification = MaterialVerification::create([
            'ulid' => Str::ulid()->toBase32(),
            'merchant_id' => $merchant->ulid,
            'junkshop_id' => $request->junkshop_id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'grade' => $request->grade,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        // Handle photo uploads if provided
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('verification-photos', 'public');
                
                VerificationPhoto::create([
                    'verification_id' => $verification->id,
                    'file_path' => $path,
                    'file_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getMimeType(),
                    'file_size' => $photo->getSize()
                ]);
            }
        }

        return response()->json([
            'message' => 'Verification request created successfully',
            'verification' => $verification->load(['item', 'junkshop', 'photos'])
        ], 201);
    }

    /**
     * Display the specified verification.
     */
    public function show(string $ulid)
    {
        $verification = MaterialVerification::where('ulid', $ulid)
            ->with(['item', 'junkshop', 'photos'])
            ->firstOrFail();

        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant || $verification->merchant_id !== $merchant->ulid) {
            return response()->json([
                'message' => 'Unauthorized access to verification'
            ], 403);
        }

        return response()->json($verification);
    }

    /**
     * Update the verification status.
     */
    public function updateStatus(Request $request, string $ulid)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'verified_grade' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $verification = MaterialVerification::where('ulid', $ulid)->firstOrFail();

        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant || $verification->merchant_id !== $merchant->ulid) {
            return response()->json([
                'message' => 'Unauthorized access to verification'
            ], 403);
        }

        $verification->update([
            'status' => $request->status,
            'verified_grade' => $request->verified_grade,
            'notes' => $request->notes ?: $verification->notes
        ]);

        return response()->json([
            'message' => 'Verification status updated successfully',
            'verification' => $verification->fresh()->load(['item', 'junkshop', 'photos'])
        ]);
    }

    /**
     * Add photos to an existing verification.
     */
    public function addPhotos(Request $request, string $ulid)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'required|image|max:10240', // 10MB limit per photo
        ]);

        $verification = MaterialVerification::where('ulid', $ulid)->firstOrFail();

        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant || $verification->merchant_id !== $merchant->ulid) {
            return response()->json([
                'message' => 'Unauthorized access to verification'
            ], 403);
        }

        $uploadedPhotos = [];
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('verification-photos', 'public');
            
            $verificationPhoto = VerificationPhoto::create([
                'verification_id' => $verification->id,
                'file_path' => $path,
                'file_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'file_size' => $photo->getSize()
            ]);

            $uploadedPhotos[] = $verificationPhoto;
        }

        return response()->json([
            'message' => 'Photos added successfully',
            'photos' => $uploadedPhotos
        ], 201);
    }

    /**
     * Remove a photo from a verification.
     */
    public function removePhoto(string $ulid, int $photoId)
    {
        $verification = MaterialVerification::where('ulid', $ulid)->firstOrFail();

        $user = Auth::user();
        $merchant = $user->merchant;

        if (!$merchant || $verification->merchant_id !== $merchant->ulid) {
            return response()->json([
                'message' => 'Unauthorized access to verification'
            ], 403);
        }

        $photo = VerificationPhoto::where('id', $photoId)
            ->where('verification_id', $verification->id)
            ->firstOrFail();
        
        // Delete the file from storage
        Storage::disk('public')->delete($photo->file_path);
        
        // Delete the database record
        $photo->delete();

        return response()->json([
            'message' => 'Photo removed successfully'
        ]);
    }
}
