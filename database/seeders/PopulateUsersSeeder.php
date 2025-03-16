<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PopulateUsersSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = base_path('populate_users.csv');
        $csvData = array_map('str_getcsv', file($csvFile));
        $headers = array_shift($csvData); // Remove headers

        foreach ($csvData as $row) {
            $email = $row[0];
            $name = $row[1];
            $password = $row[2];
            $role = $row[3];

            // Create user
            $user = User::create([
                'ulid' => Str::ulid()->toBase32(),
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Assign role
            $role = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            $user->assignRole($role);
        }
    }
}