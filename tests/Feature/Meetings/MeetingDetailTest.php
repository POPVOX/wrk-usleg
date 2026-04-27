<?php

use App\Models\Meeting;
use App\Models\User;

it('renders meeting detail in read mode by default', function () {
    $user = User::factory()->create();

    $meeting = Meeting::create([
        'user_id' => $user->id,
        'title' => 'State broadband coalition',
        'meeting_date' => now()->toDateString(),
        'status' => Meeting::STATUS_NEW,
    ]);

    $this->actingAs($user)
        ->get(route('meetings.show', $meeting))
        ->assertOk()
        ->assertSee('State broadband coalition')
        ->assertSee('Edit')
        ->assertDontSee('Save Changes');
});
