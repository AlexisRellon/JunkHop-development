<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Mail\CollectionConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    // Other methods...

    /**
     * Store a new collection request
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'waste_type' => 'required|string',
            'address' => 'required|string',
            // Add other validation rules
        ]);

        // Create new collection
        $collection = new Collection();
        $collection->user_id = Auth::id();
        $collection->date = $validated['date'];
        $collection->time_slot = $validated['time_slot'];
        $collection->waste_type = $validated['waste_type'];
        $collection->address = $validated['address'];
        $collection->status = 'scheduled';
        $collection->save();

        // Send email confirmation
        $user = Auth::user();
        Mail::to($user->email)->send(new CollectionConfirmationMail($user, $collection));

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Collection scheduled successfully! A confirmation email has been sent.');
    }

    // Other methods...
}
