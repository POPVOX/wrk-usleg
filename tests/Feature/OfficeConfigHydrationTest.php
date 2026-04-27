<?php

use App\Models\MemberProfile;
use App\Models\User;
use App\Providers\AppServiceProvider;

it('hydrates office config from the member profile during app boot', function () {
    $user = User::factory()->create();

    MemberProfile::current()->update([
        'member_name' => 'Marci Harris',
        'member_first_name' => 'Marci',
        'member_last_name' => 'Harris',
        'member_title' => 'Representative',
        'government_level' => 'federal',
        'setup_completed_at' => now(),
    ]);

    (new AppServiceProvider(app()))->boot();

    expect(config('office.member_name'))->toBe('Marci Harris');
    expect(config('office.member_title'))->toBe('Representative');

    $this->actingAs($user)
        ->get('/setup/priorities')
        ->assertOk()
        ->assertSee('Help us understand what matters most to Representative Marci Harris');
});
