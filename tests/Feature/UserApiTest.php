<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('can list users', function () {
    User::factory()->count(3)->create();

    $response = $this->getJson('/api/users');

    $response->assertStatus(200)->assertJsonCount(3);
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

test('can show a user', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)->assertJsonFragment(['email' => $user->email]);
});

test('can update a user', function () {
    $user = User::factory()->create();

    $response = $this->putJson("/api/users/{$user->id}", ['name' => 'Updated Name']);

    $response->assertStatus(200)->assertJsonFragment(['name' => 'Updated Name']);
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
});

test('can delete a user', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
