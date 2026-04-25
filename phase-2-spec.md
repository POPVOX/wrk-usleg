# Phase 2: Dashboard Split - Specification & Implementation Guide

**Goal:** Create two distinct dashboard experiences - Personal Staffer Dashboard and Office Overview Dashboard

**Estimated Time:** 1 week  
**Difficulty:** Medium-High (new components, conditional routing, Member location tracking)

---

## Overview

Currently WRK has a single dashboard. Congressional offices need TWO dashboard types:

1. **Personal Staffer Dashboard** - Individual view of MY work
2. **Office Overview Dashboard** - Meta view of ALL office activity (for leadership)

**Key Decision Point:** How to route users to the right dashboard?
- **Option A:** Role-based (Chiefs of Staff/Senior Staff see Overview, others see Personal)
- **Option B:** User preference toggle
- **Option C:** Both accessible, default based on role

**Recommendation:** Option C - Let users switch between views with a toggle, but default based on role.

---

## Architecture

### New Files to Create

```
app/Livewire/
├── Dashboards/
│   ├── PersonalDashboard.php          [NEW]
│   ├── OfficeOverview.php             [NEW] (enhanced from existing Dashboard)
│   └── DashboardRouter.php            [NEW] (handles routing logic)

resources/views/livewire/dashboards/
├── personal-dashboard.blade.php       [NEW]
├── office-overview.blade.php          [RENAME from dashboard.blade.php]
└── components/
    ├── dashboard-toggle.blade.php     [NEW]
    ├── member-location-widget.blade.php   [NEW]
    └── (other widgets as needed)
```

### Route Changes

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {
    // Default dashboard route - router decides which to show
    Route::get('/dashboard', \App\Livewire\Dashboards\DashboardRouter::class)
        ->name('dashboard');
    
    // Explicit routes for each dashboard type
    Route::get('/dashboard/personal', \App\Livewire\Dashboards\PersonalDashboard::class)
        ->name('dashboard.personal');
    
    Route::get('/dashboard/overview', \App\Livewire\Dashboards\OfficeOverview::class)
        ->name('dashboard.overview');
});
```

---

## Part 1: Personal Staffer Dashboard

### Purpose
Individual staffer sees ONLY their own work - their meetings, their issues, their tasks.

### Widgets to Include

#### 1. **My Upcoming Meetings** (Next 7 Days)
- Show only meetings where `user_id = current user` OR attendee includes current user
- Display: Date/time, title, organization, location/video link
- Click → goes to meeting detail
- "View All My Meetings" link

#### 2. **My Assigned Issues**
- Issues where current user is assigned
- Show: Issue name, status, priority level
- Filter: Active issues only
- Click → goes to issue detail

#### 3. **My Action Items**
- Commitments where `assigned_to = current user` and not completed
- Show: Task description, due date, related meeting
- Sort by: Due date (overdue highlighted)
- Quick complete checkbox

#### 4. **Member's Schedule** (Read-Only Widget)
- Mini view of Member's schedule for today + next 2 days
- Shows: Time, event title, location
- "See Full Schedule" link → goes to Member Scheduling page
- This helps staff coordinate around Member's availability

#### 5. **My Constituent Meetings** (if applicable)
- Meetings flagged as constituent meetings where user is organizer
- Upcoming only
- Shows prep status (notes ready, talking points prepared)

#### 6. **My Recent Activity**
- Last 10 actions by this user
- Examples: "Added notes to Meeting X", "Updated Issue Y status", "Completed action item Z"
- Timestamp each item

### Layout

```
┌─────────────────────────────────────────────────────────┐
│  Personal Dashboard                    [Switch to Overview] │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  ┌──────────────────┐  ┌──────────────────┐             │
│  │ My Upcoming      │  │ Member Schedule  │             │
│  │ Meetings         │  │ (Today + 2 days) │             │
│  │ (Next 7 Days)    │  │                  │             │
│  │                  │  │ Read-only widget │             │
│  └──────────────────┘  └──────────────────┘             │
│                                                           │
│  ┌──────────────────┐  ┌──────────────────┐             │
│  │ My Assigned      │  │ My Action Items  │             │
│  │ Issues           │  │                  │             │
│  │ (Active)         │  │ Sorted by due    │             │
│  │                  │  │ date             │             │
│  └──────────────────┘  └──────────────────┘             │
│                                                           │
│  ┌─────────────────────────────────────────┐             │
│  │ My Recent Activity                      │             │
│  │ (Last 10 actions)                       │             │
│  └─────────────────────────────────────────┘             │
│                                                           │
└─────────────────────────────────────────────────────────┘
```

### Implementation: PersonalDashboard.php

```php
<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;
use App\Models\Meeting;
use App\Models\Issue;
use App\Models\Commitment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PersonalDashboard extends Component
{
    public function getMyUpcomingMeetingsProperty()
    {
        $userId = Auth::id();
        
        return Meeting::where('user_id', $userId)
            ->where('meeting_date', '>=', Carbon::now())
            ->where('meeting_date', '<=', Carbon::now()->addDays(7))
            ->orderBy('meeting_date')
            ->take(10)
            ->get();
    }
    
    public function getMyAssignedIssuesProperty()
    {
        $userId = Auth::id();
        
        return Issue::whereHas('team', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('status', '!=', 'Completed')
        ->orderBy('priority_level')
        ->take(8)
        ->get();
    }
    
    public function getMyActionItemsProperty()
    {
        $userId = Auth::id();
        
        return Commitment::where('assigned_to', $userId)
            ->where('status', '!=', 'completed')
            ->orderByRaw("CASE WHEN due_date < ? THEN 0 ELSE 1 END", [Carbon::now()])
            ->orderBy('due_date')
            ->take(10)
            ->get();
    }
    
    public function getMemberScheduleProperty()
    {
        // This will be a mini view of the Member's schedule
        // We'll implement full Member scheduling in Phase 5
        // For now, just return placeholder or top 3 meetings
        
        return Meeting::where('requires_member', true)
            ->where('meeting_date', '>=', Carbon::now())
            ->where('meeting_date', '<=', Carbon::now()->addDays(2))
            ->orderBy('meeting_date')
            ->take(5)
            ->get();
    }
    
    public function getMyRecentActivityProperty()
    {
        // This would query an activity log table if you have one
        // For now, we can show recent meetings/issues updated by this user
        
        $userId = Auth::id();
        
        $recentMeetings = Meeting::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($meeting) {
                return [
                    'type' => 'meeting',
                    'description' => "Updated meeting: {$meeting->title}",
                    'timestamp' => $meeting->updated_at,
                    'url' => route('meetings.show', $meeting)
                ];
            });
        
        $recentIssues = Issue::whereHas('team', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get()
        ->map(function($issue) {
            return [
                'type' => 'issue',
                'description' => "Updated issue: {$issue->name}",
                'timestamp' => $issue->updated_at,
                'url' => route('issues.show', $issue)
            ];
        });
        
        return $recentMeetings->merge($recentIssues)
            ->sortByDesc('timestamp')
            ->take(10);
    }
    
    public function render()
    {
        return view('livewire.dashboards.personal-dashboard', [
            'upcomingMeetings' => $this->myUpcomingMeetings,
            'assignedIssues' => $this->myAssignedIssues,
            'actionItems' => $this->myActionItems,
            'memberSchedule' => $this->memberSchedule,
            'recentActivity' => $this->myRecentActivity,
        ]);
    }
}
```

---

## Part 2: Office Overview Dashboard

### Purpose
Leadership sees meta view of ALL office activity - used by Chief of Staff, Legislative Director, etc.

### Critical Widget: Member Location & Time

This is the MOST IMPORTANT new widget.

```
┌─────────────────────────────────────┐
│  Member Location                     │
│  📍 Washington, DC                   │
│  🕐 EST (Eastern Time)               │
│  🗓️  In committee until 3:30 PM     │
│                                      │
│  Last updated: 2 minutes ago         │
│  [Update Location]                   │
└─────────────────────────────────────┘
```

**Data Model for Member Location:**

```php
// New model: MemberLocation
// Create migration:
php artisan make:model MemberLocation -m

// Migration:
Schema::create('member_locations', function (Blueprint $table) {
    $table->id();
    $table->string('location_name'); // "Washington, DC" or "District Office 1"
    $table->string('timezone'); // "America/New_York"
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->string('current_activity')->nullable(); // "In committee"
    $table->timestamp('activity_until')->nullable();
    $table->boolean('is_current')->default(false); // Only one should be true
    $table->integer('updated_by'); // Staff member who updated
    $table->timestamps();
});
```

**How to Update Location:**
- Manual entry by scheduler or chief of staff
- Could sync from Member's calendar (Phase 5 enhancement)
- Simple form: Location dropdown, timezone (auto-detected), current activity, until when

### Other Widgets

#### 1. **Office-Wide Metrics**
- Total active issues
- Meetings this week (all staff)
- Pending action items (all staff)
- Priority issues status

#### 2. **Member's Schedule** (Detailed View)
- Today + next 3 days
- All events, not just highlights
- Color-coded by event type
- Shows conflicts and prep status

#### 3. **District Office Activity**
- Meetings happening in district offices
- Constituent services requests
- Local press activity

#### 4. **Priority Issues Status**
- Issues marked as "Member Priority" or "Office Priority"
- Status dashboard with progress indicators
- Next deadlines/milestones

#### 5. **Upcoming Deadlines**
- Next 10 deadlines across all issues
- Reports due to leadership
- Committee hearings Member must attend

#### 6. **Media Attention**
- Recent clips mentioning Member
- Pending media inquiries
- Scheduled interviews

#### 7. **Active Relationships** (from existing WRK)
- Most engaged organizations this month
- Upcoming stakeholder meetings

#### 8. **Map: Office Locations & Member Travel**
- Visual map showing DC office, district offices, Member current location
- Optional: Recent travel history

### Layout

```
┌─────────────────────────────────────────────────────────────────┐
│  Office Overview                       [Switch to Personal View] │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────┐  │
│  │ Member Location  │  │ Office Metrics   │  │ Priority      │  │
│  │ & Timezone       │  │ (All Staff)      │  │ Issues        │  │
│  │                  │  │                  │  │               │  │
│  │ *** KEY WIDGET   │  │                  │  │               │  │
│  └──────────────────┘  └──────────────────┘  └──────────────┘  │
│                                                                   │
│  ┌────────────────────────────────────────┐  ┌──────────────┐  │
│  │ Member's Schedule                       │  │ District     │  │
│  │ (Today + 3 days)                        │  │ Activity     │  │
│  │ Detailed view with conflicts            │  │              │  │
│  │                                         │  │              │  │
│  └────────────────────────────────────────┘  └──────────────┘  │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────┐  │
│  │ Upcoming         │  │ Media Attention  │  │ Active       │  │
│  │ Deadlines        │  │ (This Week)      │  │ Relationships│  │
│  │ (Next 10)        │  │                  │  │              │  │
│  └──────────────────┘  └──────────────────┘  └──────────────┘  │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ Office Locations Map                                    │    │
│  │ (DC + District Offices + Member Current Location)       │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

### Implementation: OfficeOverview.php

```php
<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;
use App\Models\Meeting;
use App\Models\Issue;
use App\Models\Commitment;
use App\Models\PressClip;
use App\Models\MemberLocation;
use Carbon\Carbon;

class OfficeOverview extends Component
{
    public function getMemberLocationProperty()
    {
        return MemberLocation::where('is_current', true)
            ->latest()
            ->first();
    }
    
    public function getOfficeMetricsProperty()
    {
        return [
            'active_issues' => Issue::where('status', 'Active')->count(),
            'meetings_this_week' => Meeting::whereBetween('meeting_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'pending_actions' => Commitment::where('status', '!=', 'completed')->count(),
            'priority_issues' => Issue::whereIn('priority_level', ['Member Priority', 'Office Priority'])->count(),
        ];
    }
    
    public function getMemberScheduleProperty()
    {
        return Meeting::where('meeting_date', '>=', Carbon::now())
            ->where('meeting_date', '<=', Carbon::now()->addDays(3))
            ->orderBy('meeting_date')
            ->get();
    }
    
    public function getPriorityIssuesProperty()
    {
        return Issue::whereIn('priority_level', ['Member Priority', 'Office Priority'])
            ->where('status', '!=', 'Completed')
            ->orderByRaw("CASE 
                WHEN priority_level = 'Member Priority' THEN 1 
                WHEN priority_level = 'Office Priority' THEN 2 
                ELSE 3 END")
            ->take(10)
            ->get();
    }
    
    public function getUpcomingDeadlinesProperty()
    {
        // Get commitments and issue deadlines
        $commitments = Commitment::where('due_date', '>=', Carbon::now())
            ->orderBy('due_date')
            ->take(10)
            ->get()
            ->map(function($c) {
                return [
                    'type' => 'commitment',
                    'description' => $c->commitment,
                    'due_date' => $c->due_date,
                    'assigned_to' => $c->assignedTo->name ?? 'Unassigned',
                ];
            });
        
        // Could also pull from issue milestones if you have them
        
        return $commitments->sortBy('due_date')->take(10);
    }
    
    public function getMediaAttentionProperty()
    {
        return PressClip::where('publish_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('publish_date', 'desc')
            ->take(5)
            ->get();
    }
    
    public function getDistrictActivityProperty()
    {
        // Meetings in district offices this week
        return Meeting::whereHas('user', function($query) {
            $query->whereNotNull('office_location')
                  ->where('office_location', 'like', '%District%');
        })
        ->whereBetween('meeting_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])
        ->count();
    }
    
    public function render()
    {
        return view('livewire.dashboards.office-overview', [
            'memberLocation' => $this->memberLocation,
            'metrics' => $this->officeMetrics,
            'memberSchedule' => $this->memberSchedule,
            'priorityIssues' => $this->priorityIssues,
            'upcomingDeadlines' => $this->upcomingDeadlines,
            'recentClips' => $this->mediaAttention,
            'districtActivity' => $this->districtActivity,
        ]);
    }
}
```

---

## Part 3: Dashboard Router Component

Routes users to the appropriate dashboard based on role/preference.

```php
<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DashboardRouter extends Component
{
    public function mount()
    {
        $user = Auth::user();
        
        // Check user preference if they've set one
        if (session()->has('preferred_dashboard')) {
            $preference = session('preferred_dashboard');
            return redirect()->route("dashboard.{$preference}");
        }
        
        // Default based on role
        // Chiefs of Staff, Legislative Directors, Senior Staff → Office Overview
        // Everyone else → Personal Dashboard
        
        $leadershipRoles = ['Chief of Staff', 'Legislative Director', 'Deputy Chief', 'Office Manager'];
        
        if (in_array($user->staff_title, $leadershipRoles) || $user->is_admin) {
            return redirect()->route('dashboard.overview');
        }
        
        return redirect()->route('dashboard.personal');
    }
    
    public function render()
    {
        return view('livewire.dashboards.dashboard-router');
    }
}
```

---

## Part 4: Dashboard Toggle Component

Allows users to switch between Personal and Office views.

```php
<?php

namespace App\Livewire\Components;

use Livewire\Component;

class DashboardToggle extends Component
{
    public $currentView = 'personal'; // or 'overview'
    
    public function mount()
    {
        // Detect which dashboard we're on
        $this->currentView = request()->route()->getName() === 'dashboard.overview' 
            ? 'overview' 
            : 'personal';
    }
    
    public function switchTo($view)
    {
        session(['preferred_dashboard' => $view]);
        
        return redirect()->route("dashboard.{$view}");
    }
    
    public function render()
    {
        return view('livewire.components.dashboard-toggle');
    }
}
```

**View: dashboard-toggle.blade.php**

```blade
<div class="flex items-center space-x-2 bg-gray-100 rounded-lg p-1">
    <button 
        wire:click="switchTo('personal')"
        class="px-4 py-2 rounded {{ $currentView === 'personal' ? 'bg-white shadow' : 'text-gray-600' }}"
    >
        My Dashboard
    </button>
    <button 
        wire:click="switchTo('overview')"
        class="px-4 py-2 rounded {{ $currentView === 'overview' ? 'bg-white shadow' : 'text-gray-600' }}"
    >
        Office Overview
    </button>
</div>
```

---

## Part 5: Member Location Widget

### Component: MemberLocationWidget.php

```php
<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\MemberLocation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MemberLocationWidget extends Component
{
    public $showUpdateForm = false;
    
    public $location_name;
    public $timezone;
    public $current_activity;
    public $activity_until;
    
    public function mount()
    {
        $current = MemberLocation::where('is_current', true)->first();
        
        if ($current) {
            $this->location_name = $current->location_name;
            $this->timezone = $current->timezone;
            $this->current_activity = $current->current_activity;
            $this->activity_until = $current->activity_until;
        }
    }
    
    public function toggleUpdateForm()
    {
        $this->showUpdateForm = !$this->showUpdateForm;
    }
    
    public function updateLocation()
    {
        $this->validate([
            'location_name' => 'required|string',
            'timezone' => 'required|string',
        ]);
        
        // Set all previous locations to not current
        MemberLocation::where('is_current', true)->update(['is_current' => false]);
        
        // Create new current location
        MemberLocation::create([
            'location_name' => $this->location_name,
            'timezone' => $this->timezone,
            'current_activity' => $this->current_activity,
            'activity_until' => $this->activity_until,
            'is_current' => true,
            'updated_by' => Auth::id(),
        ]);
        
        $this->showUpdateForm = false;
        
        session()->flash('message', 'Member location updated successfully.');
    }
    
    public function getCurrentLocationProperty()
    {
        return MemberLocation::where('is_current', true)->first();
    }
    
    public function getCurrentTimeProperty()
    {
        $location = $this->currentLocation;
        
        if (!$location) {
            return Carbon::now()->timezone('America/New_York')->format('g:i A T');
        }
        
        return Carbon::now()->timezone($location->timezone)->format('g:i A T');
    }
    
    public function render()
    {
        return view('livewire.components.member-location-widget', [
            'currentLocation' => $this->currentLocation,
            'currentTime' => $this->currentTime,
        ]);
    }
}
```

### View: member-location-widget.blade.php

```blade
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Member Location</h3>
    
    @if($currentLocation)
        <div class="space-y-3">
            <div class="flex items-center space-x-2">
                <span class="text-2xl">📍</span>
                <div>
                    <p class="font-medium text-lg">{{ $currentLocation->location_name }}</p>
                    <p class="text-sm text-gray-500">Updated {{ $currentLocation->updated_at->diffForHumans() }}</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🕐</span>
                <p class="text-lg">{{ $currentTime }}</p>
            </div>
            
            @if($currentLocation->current_activity)
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🗓️</span>
                    <div>
                        <p class="font-medium">{{ $currentLocation->current_activity }}</p>
                        @if($currentLocation->activity_until)
                            <p class="text-sm text-gray-500">
                                Until {{ Carbon\Carbon::parse($currentLocation->activity_until)->format('g:i A') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @else
        <p class="text-gray-500">Member location not set</p>
    @endif
    
    @if(!$showUpdateForm)
        <button 
            wire:click="toggleUpdateForm"
            class="mt-4 text-blue-600 hover:text-blue-800 text-sm"
        >
            Update Location
        </button>
    @endif
    
    @if($showUpdateForm)
        <form wire:submit.prevent="updateLocation" class="mt-4 space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <select wire:model="location_name" class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="">Select location</option>
                    <option value="Washington, DC">Washington, DC</option>
                    <option value="District Office 1">District Office 1</option>
                    <option value="District Office 2">District Office 2</option>
                    <option value="Traveling">Traveling</option>
                    <option value="District">In District</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Timezone</label>
                <select wire:model="timezone" class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="America/New_York">Eastern</option>
                    <option value="America/Chicago">Central</option>
                    <option value="America/Denver">Mountain</option>
                    <option value="America/Los_Angeles">Pacific</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Current Activity (optional)</label>
                <input 
                    type="text" 
                    wire:model="current_activity" 
                    placeholder="e.g., In committee hearing"
                    class="mt-1 block w-full rounded-md border-gray-300"
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Until (optional)</label>
                <input 
                    type="datetime-local" 
                    wire:model="activity_until" 
                    class="mt-1 block w-full rounded-md border-gray-300"
                >
            </div>
            
            <div class="flex space-x-2">
                <button 
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Update
                </button>
                <button 
                    type="button"
                    wire:click="toggleUpdateForm"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                >
                    Cancel
                </button>
            </div>
        </form>
    @endif
</div>
```

---

## Implementation Checklist

### Database Setup
- [ ] Create `member_locations` table migration
- [ ] Run migration: `php artisan migrate`

### Create New Components
- [ ] Create `app/Livewire/Dashboards/DashboardRouter.php`
- [ ] Create `app/Livewire/Dashboards/PersonalDashboard.php`
- [ ] Create `app/Livewire/Dashboards/OfficeOverview.php` (or rename existing Dashboard.php)
- [ ] Create `app/Livewire/Components/DashboardToggle.php`
- [ ] Create `app/Livewire/Components/MemberLocationWidget.php`
- [ ] Create `app/Models/MemberLocation.php`

### Create Views
- [ ] Create `resources/views/livewire/dashboards/dashboard-router.blade.php`
- [ ] Create `resources/views/livewire/dashboards/personal-dashboard.blade.php`
- [ ] Rename `resources/views/livewire/dashboard.blade.php` to `office-overview.blade.php`
- [ ] Create `resources/views/livewire/components/dashboard-toggle.blade.php`
- [ ] Create `resources/views/livewire/components/member-location-widget.blade.php`

### Update Routes
- [ ] Update `routes/web.php` with new dashboard routes
- [ ] Test routing logic

### Add User Profile Fields (if not already added in Phase 1)
- [ ] Ensure `users` table has `staff_title` field
- [ ] Ensure `users` table has `office_location` field

### Testing
- [ ] Test router logic with different user roles
- [ ] Test Personal Dashboard displays only current user's data
- [ ] Test Office Overview displays all office data
- [ ] Test dashboard toggle switch
- [ ] Test Member location widget update
- [ ] Test timezone display in Member location
- [ ] Test with users in different offices (DC vs District)

### Polish
- [ ] Make responsive for mobile
- [ ] Add loading states to widgets
- [ ] Add error handling
- [ ] Add empty states ("No upcoming meetings")
- [ ] Test print view of dashboards

---

## Optional Enhancements (Phase 2.5)

These can be added after core Phase 2 is complete:

### Map Widget
- Integrate Google Maps API or Leaflet
- Show DC office, district offices, Member current location
- Show staff distribution across offices

### Real-Time Updates
- Use Livewire polling to update Member location every 2 minutes
- Live update of Member's current activity countdown

### Staff Availability Dashboard
- Show which staff are in which office today
- Contact info for staff by office

### Performance Metrics
- Average response time to constituent inquiries
- Issue completion rates
- Meeting prep completion rates

---

## Testing Scenarios

### Scenario 1: New Legislative Assistant
**User:** Jane, Legislative Assistant  
**Expected:** Lands on Personal Dashboard, sees only her assigned issues and meetings  
**Test:** 
1. Log in as Jane
2. Should redirect to `/dashboard/personal`
3. Verify only Jane's data appears

### Scenario 2: Chief of Staff
**User:** John, Chief of Staff  
**Expected:** Lands on Office Overview, sees all office activity  
**Test:**
1. Log in as John
2. Should redirect to `/dashboard/overview`
3. Verify all staff data appears
4. Can see Member location widget

### Scenario 3: Dashboard Switch
**User:** Any user  
**Expected:** Can toggle between views  
**Test:**
1. Log in
2. Click toggle to switch views
3. Preference should persist across sessions

### Scenario 4: Member Location Update
**User:** Scheduler or Chief of Staff  
**Expected:** Can update Member's location  
**Test:**
1. Go to Office Overview
2. Click "Update Location" on Member widget
3. Change location and activity
4. Verify update saves and displays correctly
5. Verify timezone displays correctly

---

## Success Criteria

Phase 2 is complete when:
- ✅ Users are routed to appropriate dashboard by default
- ✅ Personal Dashboard shows only user's own data
- ✅ Office Overview shows all office data
- ✅ Member Location widget displays and can be updated
- ✅ Dashboard toggle works smoothly
- ✅ Both dashboards are mobile-responsive
- ✅ No errors in browser console
- ✅ All widgets load with proper data

---

## Next Phase Preview

**Phase 3: Meetings & People Enhancements**
- Add meeting types (Constituent, Stakeholder, Committee, Internal)
- Add `requires_member` flag to meetings
- Add constituent/lobbyist flags to People
- Update filters and views
- Enhance Meeting Prep AI with new context

---

**Ready to begin Phase 2? Start with the database migration for `member_locations`!**
