<?php

namespace Tests\Feature\Auth;

use App\Models\BetaRequest;
use App\Models\User;
use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.register')
        ->assertSee('Registration Is Invite-Only');
});

test('approved beta invite can render the registration form', function () {
    $request = BetaRequest::create([
        'full_name' => 'Test User',
        'email' => 'test@example.com',
        'role_type' => 'staff_member',
        'official_name' => 'Representative Doe',
        'government_level' => 'us_congress',
        'state' => 'NY',
        'primary_interest' => 'all_above',
        'status' => 'approved',
        'invite_token' => 'test-invite-token',
        'invite_expires_at' => now()->addDays(14),
    ]);

    $response = $this->get('/register?invite=' . $request->invite_token);

    $response
        ->assertOk()
        ->assertDontSee('Registration Is Invite-Only')
        ->assertSee('Approved Email');
});

test('new users can register with an approved beta invite', function () {
    $request = BetaRequest::create([
        'full_name' => 'Test User',
        'email' => 'test@example.com',
        'role_type' => 'staff_member',
        'official_name' => 'Representative Doe',
        'government_level' => 'us_congress',
        'state' => 'NY',
        'primary_interest' => 'all_above',
        'status' => 'approved',
        'invite_token' => 'approved-invite-token',
        'invite_expires_at' => now()->addDays(14),
    ]);

    $component = Volt::test('pages.auth.register')
        ->set('invite', $request->invite_token)
        ->set('name', 'Test User')
        ->set('email', $request->email)
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    expect(User::where('email', $request->email)->exists())->toBeTrue();
    expect($request->fresh()->status)->toBe('onboarded');
    expect($request->fresh()->onboarded_user_id)->not->toBeNull();
});

test('bootstrap super admin signup is available when configured and no users exist', function () {
    config()->set('auth.bootstrap_super_admin_emails', ['marci@g.popvox.com']);

    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertDontSee('Registration Is Invite-Only')
        ->assertSee('Create Your First Platform Admin Account');
});

test('allowed bootstrap email can create the first platform super admin', function () {
    config()->set('auth.bootstrap_super_admin_emails', ['marci@g.popvox.com']);

    $component = Volt::test('pages.auth.register')
        ->set('name', 'Marci Harris')
        ->set('email', 'marci@g.popvox.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('platform.dashboard', absolute: false));

    $user = User::where('email', 'marci@g.popvox.com')->first();

    expect($user)->not->toBeNull();
    expect($user->is_super_admin)->toBeTrue();
    expect($user->is_admin)->toBeTrue();
    expect($user->access_level)->toBe('admin');
    $this->assertAuthenticatedAs($user);
});

test('non-allowlisted email cannot bootstrap the first platform admin account', function () {
    config()->set('auth.bootstrap_super_admin_emails', ['marci@g.popvox.com']);

    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register')
        ->assertHasErrors(['email']);

    expect(User::count())->toBe(0);
});
