<?php

use App\Livewire\Setup\MemberPrioritiesForm;
use App\Models\User;
use Livewire\Livewire;

it('does not show the demo member name on the priorities form when config is stale', function () {
    config()->set('office.member_name', '');
    config()->set('office.member_title', 'Representative');
    config()->set('office.government_level', 'federal');

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(MemberPrioritiesForm::class)
        ->assertSee('Help us understand what matters most to this Member')
        ->assertDontSee('John Clemmons');
});
