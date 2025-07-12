<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Laravel\Passport\Client;

// Bootstrap the application
$app = require_once 'bootstrap/app.php';

// Load configuration
$app->make('config');

// Connect to database
$app->make('db');

echo "Setting up OAuth clients for Goal Digger...\n";

try {
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

    echo "OAuth clients created successfully!\n";
    echo "\n";
    echo "You can now use these credentials in your React app:\n";
    echo "Client ID: password-grant-client\n";
    echo "Client Secret: secret\n";
    echo "\n";
    echo "Or use the test users:\n";
    echo "Email: cphillips@example.com, Password: password\n";
    echo "Email: mtpultz@example.com, Password: password\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
