<?php

use App\Models\Commitment;
use App\Models\Issue;
use App\Models\Meeting;
use App\Models\MemberLocation;
use App\Models\User;
use Illuminate\Support\Carbon;

test('dashboard route redirects leadership users to the office overview', function () {
    $user = User::factory()->create([
        'title' => 'Chief of Staff',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('dashboard.overview'));
});

test('dashboard route redirects staff users to the personal dashboard', function () {
    $user = User::factory()->create([
        'title' => 'Legislative Assistant',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('dashboard.personal'));
});

test('dashboard route honors the saved dashboard preference', function () {
    $user = User::factory()->create([
        'title' => 'Legislative Assistant',
    ]);

    $this->actingAs($user)
        ->withSession(['preferred_dashboard' => 'overview'])
        ->get('/dashboard')
        ->assertRedirect(route('dashboard.overview'));
});

test('personal dashboard renders assigned issues and action items', function () {
    $user = User::factory()->create([
        'name' => 'Alex Staffer',
        'timezone' => 'America/New_York',
    ]);

    $issue = Issue::factory()->create([
        'name' => 'Veterans Benefits Expansion',
        'status' => 'active',
        'priority_level' => 'Top Priority',
    ]);
    $issue->staff()->attach($user->id, [
        'role' => 'lead',
        'added_at' => now(),
    ]);

    $meeting = Meeting::create([
        'user_id' => $user->id,
        'title' => 'Veterans Coalition Meeting',
        'meeting_date' => Carbon::now()->addDay(),
        'status' => Meeting::STATUS_PENDING,
    ]);

    Commitment::create([
        'description' => 'Draft follow-up memo',
        'status' => 'open',
        'direction' => 'from_us',
        'due_date' => Carbon::now()->addDays(2),
        'meeting_id' => $meeting->id,
        'assigned_to' => $user->id,
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get('/dashboard/personal')
        ->assertOk()
        ->assertSee('Veterans Benefits Expansion')
        ->assertSee('Draft follow-up memo');
});

test('office overview renders priority issues, deadlines, and member activity', function () {
    $user = User::factory()->create([
        'name' => 'Jordan Chief',
        'title' => 'Chief of Staff',
        'timezone' => 'America/New_York',
    ]);

    Issue::factory()->create([
        'name' => 'Port Modernization',
        'status' => 'active',
        'priority_level' => 'Top Priority',
    ]);

    $meeting = Meeting::create([
        'user_id' => $user->id,
        'title' => 'Transportation Stakeholder Briefing',
        'meeting_date' => Carbon::now()->addDays(2),
        'status' => Meeting::STATUS_PENDING,
    ]);

    Commitment::create([
        'description' => 'Prep committee packet',
        'status' => 'open',
        'direction' => 'from_us',
        'due_date' => Carbon::now()->addDays(3),
        'meeting_id' => $meeting->id,
        'assigned_to' => $user->id,
        'created_by' => $user->id,
    ]);

    MemberLocation::create([
        'location_name' => 'Washington, DC',
        'timezone' => 'America/New_York',
        'current_activity' => 'In committee hearing',
        'activity_until' => Carbon::now()->addHour(),
        'is_current' => true,
        'updated_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get('/dashboard/overview')
        ->assertOk()
        ->assertSee('Port Modernization')
        ->assertSee('Prep committee packet')
        ->assertSee('In committee hearing');
});
