<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Metals
            'Bakal (Scrap Iron)',
            'Tanso (Copper)',
            'Aluminum',
            'Stainless Steel',
            'Lead',
            'Brass',
            // Paper Products
            'Karton (Cardboard)',
            'Dyaryo (Newspaper)',
            'White Paper',
            'Magazine Paper',
            'Books',
            // Plastics
            'PET Bottles',
            'Plastic Containers',
            'Hard Plastic',
            'Plastic Sachets',
            // Glass
            'Clear Glass Bottles',
            'Colored Glass Bottles',
            // Electronics
            'Used Cellphones',
            'Computer Parts',
            'Electric Wires',
            'Electronic Boards',
            // Appliances
            'Old Refrigerators',
            'Washing Machines',
            'Air Conditioners',
            'Electric Fans',
            // Others
            'Car Batteries',
            'Rubber',
            'Used Tires',
            'Used Cooking Oil',
        ];

        foreach ($items as $itemName) {
            Item::firstOrCreate(['name' => $itemName]);
        }
    }
}