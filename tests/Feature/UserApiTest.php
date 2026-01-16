<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(\Tests\TestCase::class)->in('Feature');

beforeEach(function () {
    $this->artisan('migrate');
});

test('can list users', function () {
    User::factory()->count(3)->create();

    $response = $this->getJson('/api/users');

    $response->assertStatus(200)->assertJsonCount(3);
});

test('can show a user', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)->assertJsonFragment(['email' => $user->email]);
});

test('can create a user', function () {
    $payload = [
        'name' => 'Test User',
        'email' => 'test.user@example.com',
        'password' => 'secret123',
    ];

    $response = $this->postJson('/api/users', $payload);

    $response->assertStatus(201)->assertJsonFragment(['email' => 'test.user@example.com']);
    $this->assertDatabaseHas('users', ['email' => 'test.user@example.com']);
});

test('can update a user', function () {
    $user = User::factory()->create();

    $response = $this->putJson("/api/users/{$user->id}", ['name' => 'Updated Name']);

    $response->assertStatus(200)->assertJsonFragment(['name' => 'Updated Name']);
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
});

test('can delete a user', function () {
    $user = User::factory()->create();
    $this->actingAs($user); // Felhasználó hitelesítése

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('cannot delete a non-existent user', function () {
    $user = User::factory()->create();
    $this->actingAs($user); // Hitelesítés

    $response = $this->deleteJson('/api/users/99999');

    $response->assertStatus(404);
});

test('cannot create a user with invalid data', function () {
    $payload = [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
    ];

    $response = $this->postJson('/api/users', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('cannot update a user with invalid data', function () {
    $user = User::factory()->create();

    $payload = [
        'email' => 'not-an-email',
    ];

    $response = $this->putJson("/api/users/{$user->id}", $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('cannot show a non-existent user', function () {
    $response = $this->getJson('/api/users/99999');

    $response->assertStatus(404);
});

test('index method exists in UserController', function () {
    $this->assertTrue(method_exists(App\Http\Controllers\UserController::class, 'index'));
});

test('index returns JSON response', function () {
    $response = $this->getJson('/api/users');

    $response->assertHeader('Content-Type', 'application/json');
});

test('index returns empty array when no users exist', function () {
    $response = $this->getJson('/api/users');

    $response->assertStatus(200)->assertJsonCount(0);
});

test('show method exists in UserController', function () {
    $this->assertTrue(method_exists(App\Http\Controllers\UserController::class, 'show'));
});

test('show returns JSON response', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");

    $response->assertHeader('Content-Type', 'application/json');
});

test('store method exists in UserController', function () {
    $this->assertTrue(method_exists(App\Http\Controllers\UserController::class, 'store'));
});

test('store response contains created user data', function () {
    $payload = [
        'name' => 'Test User',
        'email' => 'test.user@example.com',
        'password' => 'secret123',
    ];

    $response = $this->postJson('/api/users', $payload);

    $response->assertStatus(201)->assertJsonFragment(['email' => 'test.user@example.com']);
});

test('store fails with duplicate email', function () {
    $existingUser = User::factory()->create(['email' => 'duplicate@example.com']);

    $payload = [
        'name' => 'New User',
        'email' => 'duplicate@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/users', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('update method exists in UserController', function () {
    $this->assertTrue(method_exists(App\Http\Controllers\UserController::class, 'update'));
});

test('update response contains updated user data', function () {
    $user = User::factory()->create();

    $response = $this->putJson("/api/users/{$user->id}", ['name' => 'Updated Name']);

    $response->assertStatus(200)->assertJsonFragment(['name' => 'Updated Name']);
});

test('update fails with missing parameters', function () {
    $user = User::factory()->create();

    $payload = [];

    $response = $this->putJson("/api/users/{$user->id}", $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('destroy method exists in UserController', function () {
    $this->assertTrue(method_exists(App\Http\Controllers\UserController::class, 'destroy'));
});

test('destroy returns 204 status code', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum'); // Hitelesítés Sanctummal

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(204);
});

test('destroy fails for unauthorized user', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/users/{$user->id}", [], ['Authorization' => '']);

    $response->assertStatus(401);
});
