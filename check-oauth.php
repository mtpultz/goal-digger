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

echo "Checking OAuth clients in database...\n\n";

try {
    $clients = Client::all();

    if ($clients->isEmpty()) {
        echo "No OAuth clients found in database.\n";
        echo "You need to run the OAuth client seeder.\n";
        exit(1);
    }

    echo 'Found ' . $clients->count() . " OAuth client(s):\n\n";

    foreach ($clients as $client) {
        echo 'Client ID: ' . $client->id . "\n";
        echo 'Name: ' . $client->name . "\n";
        echo 'Grant Types: ' . json_encode($client->grant_types) . "\n";
        echo 'Revoked: ' . ($client->revoked ? 'Yes' : 'No') . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
