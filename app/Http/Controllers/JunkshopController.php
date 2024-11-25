<?php

namespace App\Http\Controllers;

use App\Models\Junkshop;
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
        ]);

        $junkshop->update($request->only(['name', 'contact', 'description', 'address']));

        return response()->json(['message' => 'Junkshop updated successfully']);
    }

    /**
     * Create a new Junkshop.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
        ]);

        $junkshop = Junkshop::create([
            'ulid' => Str::ulid()->toBase32(),
            'name' => $request->name,
            'contact' => $request->contact,
            'description' => $request->description,
            'address' => $request->address,
            'user_id' => Auth::id(),
        ]);

        // Assign the 'junkshop_owner' role to the user
        $user = Auth::user();
        $user->assignRole('junkshop_owner');

        return response()->json($junkshop, 201);
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
