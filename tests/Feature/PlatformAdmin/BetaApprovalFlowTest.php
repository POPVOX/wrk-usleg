<?php

use App\Livewire\PlatformAdmin\BetaRequests;
use App\Models\BetaRequest as BetaRequestModel;
use App\Models\User;
use Livewire\Livewire;

it('super admins can approve beta requests and generate invite links', function () {
    $admin = User::factory()->create([
        'is_super_admin' => true,
    ]);

    $request = BetaRequestModel::create([
        'full_name' => 'Marci Harris',
        'email' => 'marci@example.com',
        'role_type' => 'staff_member',
        'official_name' => 'Representative Example',
        'government_level' => 'us_congress',
        'state' => 'VA',
        'primary_interest' => 'meeting_prep',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(BetaRequests::class)
        ->call('approve', $request->id)
        ->assertSet('generatedInviteRequestId', $request->id)
        ->assertSet('generatedInviteUrl', route('register', ['invite' => $request->fresh()->invite_token], absolute: true));

    $request->refresh();

    expect($request->status)->toBe('approved');
    expect($request->invite_token)->not->toBeNull();
    expect($request->invite_expires_at)->not->toBeNull();
    expect($request->approved_by)->toBe($admin->id);
});

it('declining a beta request clears any active invite', function () {
    $admin = User::factory()->create([
        'is_super_admin' => true,
    ]);

    $request = BetaRequestModel::create([
        'full_name' => 'Pat Staffer',
        'email' => 'pat@example.com',
        'role_type' => 'staff_member',
        'official_name' => 'Senator Sample',
        'government_level' => 'us_congress',
        'state' => 'CA',
        'primary_interest' => 'knowledge_mgmt',
        'status' => 'approved',
        'invite_token' => 'existing-token',
        'invite_expires_at' => now()->addDays(14),
    ]);

    $this->actingAs($admin);

    Livewire::test(BetaRequests::class)
        ->call('decline', $request->id);

    $request->refresh();

    expect($request->status)->toBe('declined');
    expect($request->invite_token)->toBeNull();
    expect($request->declined_by)->toBe($admin->id);
});

it('super admins can reveal an existing invite link for an approved request', function () {
    $admin = User::factory()->create([
        'is_super_admin' => true,
    ]);

    $request = BetaRequestModel::create([
        'full_name' => 'Taylor Example',
        'email' => 'taylor@example.com',
        'role_type' => 'staff_member',
        'official_name' => 'Representative Example',
        'government_level' => 'us_congress',
        'state' => 'VA',
        'primary_interest' => 'media_tracking',
        'status' => 'approved',
        'invite_token' => 'existing-token',
        'invite_expires_at' => now()->addDays(14),
    ]);

    $this->actingAs($admin);

    Livewire::test(BetaRequests::class)
        ->call('showInvite', $request->id)
        ->assertSet('generatedInviteRequestId', $request->id)
        ->assertSet('generatedInviteUrl', route('register', ['invite' => 'existing-token'], absolute: true));
});
