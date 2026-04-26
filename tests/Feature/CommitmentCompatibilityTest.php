<?php

use App\Models\Commitment;
use App\Models\User;

test('commitment legacy dashboard aliases remain available', function () {
    $user = User::factory()->create();

    $commitment = Commitment::create([
        'description' => 'Prep committee packet',
        'direction' => 'from_us',
        'status' => 'open',
        'assigned_to' => $user->id,
        'created_by' => $user->id,
    ]);

    expect($commitment->commitment)->toBe('Prep committee packet');
    expect($commitment->assignedTo)->not->toBeNull();
    expect($commitment->assignedTo->is($user))->toBeTrue();
});
