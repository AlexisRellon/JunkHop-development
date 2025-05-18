<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bid;
use App\Models\User;
use App\Models\Item;
use App\Models\Junkshop;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class VerifiedBidsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all junkshops
        $junkshops = Junkshop::all();
        
        // Get all merchants
        $merchants = User::where('role', 'merchant')->get();
        
        // Get all items
        $items = Item::all();

        // Generate 15 verified bids
        for ($i = 0; $i < 15; $i++) {
            $junkshop = $faker->randomElement($junkshops);
            $item = $faker->randomElement($items);
            $quantity = $faker->randomFloat(2, 5, 100);
            $pricePerKg = $faker->randomFloat(2, 10, 50);
            $startingBid = $quantity * $pricePerKg;
            $currentBid = $startingBid + $faker->randomFloat(2, 1, 50);
            
            // Create bid with realistic data
            Bid::create([
                'ulid' => Str::ulid(),
                'junkshop_id' => $junkshop->ulid,
                'item_id' => $item->id,
                'quantity' => $quantity,
                'price_per_kg' => $pricePerKg,
                'starting_bid' => $startingBid,
                'current_bid' => $currentBid,
                'notes' => $faker->sentence(),
                'expiry_date' => Carbon::now()->addDays($faker->numberBetween(7, 30)),
                'start_date' => Carbon::now()->subDays($faker->numberBetween(5, 10)),
                'end_date' => Carbon::now()->addDays($faker->numberBetween(1, 7)),
                'status' => 'accepted',
                'is_bulk_order' => $faker->boolean(30),
                'is_bidding_enabled' => true,
                'bidding_processed' => true,
                'allow_auto_renewal' => $faker->boolean(20),
                'created_at' => Carbon::now()->subDays($faker->numberBetween(5, 30)),
                'updated_at' => Carbon::now(),
                'accepted_at' => Carbon::now()->subDays($faker->numberBetween(1, 4)),
            ]);
        }
    }
}
