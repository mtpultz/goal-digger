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

test('user can register with valid data', function () {
    // Arrange
    $faker = Factory::create();

    $userData = [
        'name' => $faker->name(),
        'email' => $faker->unique()->safeEmail(),
        'password' => $faker->password(8, 20),
        'password_confirmation' => null,
    ];
    $userData['password_confirmation'] = $userData['password'];

    // Act
    $response = $this->postJson('/api/auth/register', $userData);

    // Assert
    $response->assertStatus(Response::HTTP_CREATED)
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
            'message' => 'User registered successfully.',
            'user' => [
                'name' => $userData['name'],
                'email' => $userData['email'],
            ],
            'token_type' => 'Bearer',
        ]);

    $this->assertDatabaseHas('users', [
        'name' => $userData['name'],
        'email' => $userData['email'],
    ]);

    $this->assertNotEmpty($response->json('access_token'));
});

test('registration fails with invalid email', function () {
    // Arrange
    $faker = Factory::create();

    $userData = [
        'name' => $faker->name(),
        'email' => 'invalid-email',
        'password' => $faker->password(8, 20),
        'password_confirmation' => null,
    ];
    $userData['password_confirmation'] = $userData['password'];

    // Act
    $response = $this->postJson('/api/auth/register', $userData);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});

test('registration fails with duplicate email', function () {
    // Arrange
    $faker = Factory::create();
    $email = $faker->unique()->safeEmail();

    User::factory()->create(['email' => $email]);

    $userData = [
        'name' => $faker->name(),
        'email' => $email,
        'password' => $faker->password(8, 20),
        'password_confirmation' => null,
    ];
    $userData['password_confirmation'] = $userData['password'];

    // Act
    $response = $this->postJson('/api/auth/register', $userData);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email'])
        ->assertJson([
            'errors' => [
                'email' => ['An account with this email address already exists.'],
            ],
        ]);
});

test('registration fails with mismatched passwords', function () {
    // Arrange
    $faker = Factory::create();

    $userData = [
        'name' => $faker->name(),
        'email' => $faker->unique()->safeEmail(),
        'password' => $faker->password(8, 20),
        'password_confirmation' => $faker->password(8, 20),
    ];

    // Act
    $response = $this->postJson('/api/auth/register', $userData);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['password'])
        ->assertJson([
            'errors' => [
                'password' => ['Password confirmation does not match.'],
            ],
        ]);
});

test('registration fails with weak password', function () {
    // Arrange
    $faker = Factory::create();

    $userData = [
        'name' => $faker->name(),
        'email' => $faker->unique()->safeEmail(),
        'password' => '123',
        'password_confirmation' => '123',
    ];

    // Act
    $response = $this->postJson('/api/auth/register', $userData);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['password']);
});

test('registration fails with missing required fields', function () {
    // Arrange
    $userData = [];

    // Act
    $response = $this->postJson('/api/auth/register', $userData);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});
