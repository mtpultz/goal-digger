<?php

use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create OAuth clients for Passport
    Client::create([
        'id' => 'personal-access-client',
        'owner_type' => null,
        'owner_id' => null,
        'name' => 'Test Personal Access Client',
        'secret' => 'secret',
        'provider' => null,
        'redirect_uris' => ['http://localhost'],
        'grant_types' => ['personal_access'],
        'revoked' => false,
    ]);

    Client::create([
        'id' => 'password-grant-client',
        'owner_type' => null,
        'owner_id' => null,
        'name' => 'Test Password Grant Client',
        'secret' => 'secret',
        'provider' => null,
        'redirect_uris' => ['http://localhost'],
        'grant_types' => ['password'],
        'revoked' => false,
    ]);
});

test('user can login with valid credentials', function () {
    // Arrange
    $faker = Factory::create();
    $password = $faker->password(8, 20);
    $user = User::factory()->create([
        'email' => $faker->unique()->safeEmail(),
        'password' => bcrypt($password),
    ]);

    $loginData = [
        'email' => $user->email,
        'password' => $password,
    ];

    // Act
    $response = $this->postJson('/api/auth/login', $loginData);

    // Assert
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
            ],
            'access_token',
            'token_type',
        ])
        ->assertJson([
            'message' => 'Login successful.',
            'user' => [
                'email' => $user->email,
            ],
            'token_type' => 'Bearer',
        ]);

    $this->assertNotEmpty($response->json('access_token'));
});

test('login fails with invalid credentials', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create([
        'email' => $faker->unique()->safeEmail(),
        'password' => bcrypt('correct-password'),
    ]);

    $loginData = [
        'email' => $user->email,
        'password' => 'wrong-password',
    ];

    // Act
    $response = $this->postJson('/api/auth/login', $loginData);

    // Assert
    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'message' => 'Invalid credentials.',
        ]);
});

test('login fails with missing required fields', function () {
    // Arrange
    $loginData = [];

    // Act
    $response = $this->postJson('/api/auth/login', $loginData);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email', 'password']);
});
