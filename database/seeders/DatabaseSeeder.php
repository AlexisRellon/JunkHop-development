<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PopulateUsersSeeder::class,
            ItemSeeder::class,           // Add items first
            PopulateJunkshopSeeder::class, // Then create junkshops with items
            VerifiedBidsSeeder::class,   // Add verified bids
        ]);

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign existing permissions
        $roles = [
            'admin',
            'user',
            'junkshop_owner',
            'baranggay_admin',
            'merchant'  // Adding merchant role for Phase 2
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Force database to commit roles before continuing
        DB::statement('SELECT 1');

        // create admin user
        $user = \App\Models\User::factory()->create([
            'name' => 'Super',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $user->ulid = Str::ulid()->toBase32();
        $user->email_verified_at = now();
        $user->save(['timestamps' => false]);

        // Check if role exists before assigning
        if(Role::where('name', 'admin')->where('guard_name', 'web')->exists()) {
            $user->assignRole('admin');
        }
    }
}
