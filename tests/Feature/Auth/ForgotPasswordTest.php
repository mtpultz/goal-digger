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
});

test('user can request a password reset link', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email' => $faker->unique()->safeEmail()]);

    // Act
    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => $user->email,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Password reset link sent.',
        ]);
});

test('forgot password fails with invalid email', function () {
    // Act
    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'not-an-email',
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});

test('forgot password fails with non-existent email', function () {
    // Act
    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'missing@example.com',
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});

test('user can reset password with valid token', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email' => $faker->unique()->safeEmail()]);
    $token = app('auth.password.broker')->createToken($user);
    $newPassword = $faker->password(8, 20);

    // Act
    $response = $this->postJson('/api/auth/reset-password', [
        'email' => $user->email,
        'token' => $token,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Password has been reset.',
        ]);
});

test('reset password fails with invalid token', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email' => $faker->unique()->safeEmail()]);
    $newPassword = $faker->password(8, 20);

    // Act
    $response = $this->postJson('/api/auth/reset-password', [
        'email' => $user->email,
        'token' => 'invalid-token',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'message' => 'Invalid token or email.',
        ]);
});

test('reset password fails with missing fields', function () {
    // Act
    $response = $this->postJson('/api/auth/reset-password', []);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email', 'token', 'password']);
});
