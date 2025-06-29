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

test('user can verify email with valid hash', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email_verified_at' => null]);
    $hash = sha1($user->getEmailForVerification());

    // Act
    $response = $this->postJson('/api/auth/verify-email', [
        'id' => $user->id,
        'hash' => $hash,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Email verified successfully.',
        ]);
});

test('verify email fails with invalid hash', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email_verified_at' => null]);

    // Act
    $response = $this->postJson('/api/auth/verify-email', [
        'id' => $user->id,
        'hash' => 'invalid-hash',
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'message' => 'Invalid verification link.',
        ]);
});

test('verify email returns already verified', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $hash = sha1($user->getEmailForVerification());

    // Act
    $response = $this->postJson('/api/auth/verify-email', [
        'id' => $user->id,
        'hash' => $hash,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Email already verified.',
        ]);
});

test('resend verification email to unverified user', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email_verified_at' => null]);

    // Act
    $response = $this->postJson('/api/auth/resend-verification', [
        'email' => $user->email,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Verification email resent.',
        ]);
});

test('resend verification returns already verified', function () {
    // Arrange
    $faker = Factory::create();
    $user = User::factory()->create(['email_verified_at' => now()]);

    // Act
    $response = $this->postJson('/api/auth/resend-verification', [
        'email' => $user->email,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Email already verified.',
        ]);
});

test('resend verification fails with invalid email', function () {
    // Act
    $response = $this->postJson('/api/auth/resend-verification', [
        'email' => 'not-an-email',
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});

test('resend verification fails with non-existent email', function () {
    // Act
    $response = $this->postJson('/api/auth/resend-verification', [
        'email' => 'missing@example.com',
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});
