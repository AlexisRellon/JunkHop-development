<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\Junkshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PopulateJunkshopSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users with junkshop_owner role
        $junkshopOwners = User::role('junkshop_owner')->get();
        $allItems = Item::all();

        foreach ($junkshopOwners as $owner) {
            $junkshop = Junkshop::create([
                'ulid' => Str::ulid()->toBase32(),
                'name' => $owner->name . "'s Junkshop",
                'contact' => '09' . rand(100000000, 999999999), // Random PH mobile number
                'description' => 'A reliable junkshop for all your recycling needs.',
                'address' => 'Metro Manila, Philippines', // Default address
                'user_id' => $owner->ulid,
            ]);            // Randomly select between 10 and 20 items for each junkshop
            $randomItems = $allItems->random(rand(10, 20));
            
            // Attach the selected items to the junkshop with random quantities
            foreach ($randomItems as $item) {
                // Generate random quantity as whole numbers between 10 and 1000 kg
                $quantity = rand(10, 1000);
                
                $junkshop->items()->attach($item->id, [
                    'quantity' => $quantity,
                    'price' => rand(5, 50), // Random price between 5 and 50 per kg
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}