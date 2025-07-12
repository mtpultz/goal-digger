<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create password grant client for the React app
        Client::create([
            'id' => 'password-grant-client',
            'owner_type' => null,
            'owner_id' => null,
            'name' => 'Goal Digger Password Grant Client',
            'secret' => 'secret',
            'provider' => null,
            'redirect_uris' => ['http://localhost'],
            'grant_types' => ['password'],
            'revoked' => false,
        ]);

        // Create personal access client
        Client::create([
            'id' => 'personal-access-client',
            'owner_type' => null,
            'owner_id' => null,
            'name' => 'Goal Digger Personal Access Client',
            'secret' => 'secret',
            'provider' => null,
            'redirect_uris' => ['http://localhost'],
            'grant_types' => ['personal_access'],
            'revoked' => false,
        ]);
    }
}
