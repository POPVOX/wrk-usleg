<?php

use App\Livewire\Setup\SetupWizard;
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
