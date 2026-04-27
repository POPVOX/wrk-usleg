<?php

use App\Livewire\Setup\SetupWizard;
use App\Models\MemberProfile;
use App\Models\User;
use Livewire\Livewire;

it('does not preload stale office config into the setup wizard', function () {
    config()->set('office.member_name', 'John Clemmons');
    config()->set('office.member_party', 'Democratic');
    config()->set('office.member_state', 'TN');
    config()->set('office.member_district', '55');

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(SetupWizard::class)
        ->assertSet('first_name', '')
        ->assertSet('last_name', '')
        ->assertSet('party', '')
        ->assertSet('state', '')
        ->assertSet('district_number', '')
        ->assertSet('search_query', '');
});

it('saves office identity to the member profile singleton', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(SetupWizard::class)
        ->set('level', 'federal')
        ->set('first_name', 'Marci')
        ->set('last_name', 'Harris')
        ->set('title', 'Representative')
        ->set('party', 'Independent')
        ->set('state', 'VA')
        ->set('district_number', '8')
        ->set('official_website', 'https://example.com')
        ->call('completeSetup');

    $profile = MemberProfile::current();

    expect($profile->member_name)->toBe('Marci Harris');
    expect($profile->member_first_name)->toBe('Marci');
    expect($profile->member_last_name)->toBe('Harris');
    expect($profile->member_title)->toBe('Representative');
    expect($profile->member_party)->toBe('Independent');
    expect($profile->member_state)->toBe('VA');
    expect($profile->member_district)->toBe('8');
    expect($profile->official_website)->toBe('https://example.com');
    expect($profile->setup_completed_at)->not->toBeNull();
});
