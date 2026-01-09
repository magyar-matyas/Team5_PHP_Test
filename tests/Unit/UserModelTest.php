<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('password is hidden when model is serialized', function () {
    $user = User::factory()->make();

    $arr = $user->toArray();

    expect(array_key_exists('password', $arr))->toBeFalse();
});

test('fillable contains expected attributes', function () {
    $user = new User();

    $fillable = $user->getFillable();

    expect($fillable)->toContain('name');
    expect($fillable)->toContain('email');
    expect($fillable)->toContain('password');
});
