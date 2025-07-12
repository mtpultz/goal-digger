<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create OAuth clients first
        $this->call(OAuthClientSeeder::class);

        User::factory()->create([
            'name' => 'Chris Phillips',
            'email' => 'cphillips@example.com',
            'password' => Hash::make('password'),
        ]);
        User::factory()->create([
            'name' => 'Martin Pultz',
            'email' => 'mtpultz@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
