<?php

namespace App\Http\Controllers;

use App\Models\Junkshop;
use App\Models\User;
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

        $junkshop = Junkshop::create([
            'ulid' => Str::ulid()->toBase32(),
            'name' => $validatedData['name'],
            'contact' => $validatedData['contact'],
            'description' => $validatedData['description'],
            'address' => $validatedData['address'],
            'user_id' => $owner->ulid, // Use ulid instead of id
        ]);

        return response()->json(['message' => 'Junkshop created successfully', 'junkshop' => $junkshop]);
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

        return response()->json(['message' => 'Junkshop deleted successfully']);
    }
}
