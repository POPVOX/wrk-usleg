<?php

use App\Http\Controllers\GoogleAuthController;
use App\Livewire\Dashboard;
use App\Livewire\Dashboards\DashboardRouter;
use App\Livewire\Dashboards\PersonalDashboard;
use App\Livewire\Dashboards\OfficeOverview;
use App\Livewire\Meetings\MeetingCapture;
use App\Livewire\Meetings\MeetingDetail;
use App\Livewire\Meetings\MeetingList;
use App\Livewire\PlatformAdmin;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - Router decides which dashboard to show based on role/preference
    Route::get('/dashboard', DashboardRouter::class)->name('dashboard');
    Route::get('/dashboard/personal', PersonalDashboard::class)->name('dashboard.personal');
    Route::get('/dashboard/overview', OfficeOverview::class)->name('dashboard.overview');

    // Legacy dashboard route (for backwards compatibility)
    Route::get('/dashboard/legacy', Dashboard::class)->name('dashboard.legacy');

    // Member Hub Setup Wizard
    Route::get('/setup', \App\Livewire\Setup\SetupWizard::class)->name('setup.wizard');
    Route::get('/setup/priorities', \App\Livewire\Setup\MemberPrioritiesForm::class)->name('setup.priorities');

    // Onboarding
    Route::get('/onboarding', \App\Livewire\Onboarding::class)->name('onboarding');

    // Meetings
    Route::get('/meetings', MeetingList::class)->name('meetings.index');
    Route::get('/meetings/new', MeetingCapture::class)->name('meetings.create');
    Route::get('/meetings/{meeting}/edit', MeetingCapture::class)->name('meetings.edit');
    Route::get('/meetings/{meeting}', MeetingDetail::class)->name('meetings.show');

    // Issues (renamed from Projects)
    Route::get('/issues', \App\Livewire\Issues\IssueList::class)->name('issues.index');
    Route::get('/issues/create', \App\Livewire\Issues\IssueCreate::class)->name('issues.create');
    Route::get('/issues/{issue}/duplicate', \App\Livewire\Issues\IssueCreate::class)->name('issues.duplicate');
    Route::get('/issues/{issue}', \App\Livewire\Issues\IssueShow::class)->name('issues.show');

    // Organizations
    Route::get('/organizations', \App\Livewire\Organizations\OrganizationIndex::class)->name('organizations.index');
    Route::get('/organizations/{organization}', \App\Livewire\Organizations\OrganizationShow::class)->name('organizations.show');

    // People (legacy)
    Route::get('/people', \App\Livewire\People\PersonIndex::class)->name('people.index');
    Route::get('/people/{person}', \App\Livewire\People\PersonShow::class)->name('people.show');

    // Contacts (CRM-friendly alias)
    Route::get('/contacts', \App\Livewire\People\PersonIndex::class)->name('contacts.index');
    Route::get('/contacts/{person}', \App\Livewire\People\PersonShow::class)->name('contacts.show');

    // Google Calendar OAuth
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
    Route::post('/google/disconnect', [GoogleAuthController::class, 'disconnect'])->name('google.disconnect');

    // Team Hub
    Route::get('/team', \App\Livewire\Team\TeamHub::class)->name('team.hub');
    Route::get('/team/{member}', \App\Livewire\Team\TeamMemberProfile::class)->name('team.member.profile');

    // Knowledge Hub
    Route::get('/knowledge', \App\Livewire\KnowledgeHub::class)->name('knowledge.hub');

    // Knowledge Base (Org-wide)
    Route::get('/knowledge-base', \App\Livewire\KnowledgeBase::class)->name('knowledge.base');

    // Media & Press
    Route::get('/media', \App\Livewire\Media\MediaIndex::class)->name('media.index');

    // Member Hub - Comprehensive Member Context Dashboard
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/', \App\Livewire\MemberHub\MemberHub::class)->name('hub');
        Route::get('/dashboard', \App\Livewire\MemberHub\MemberHub::class)->name('dashboard');
    });

    // Management routes (CoS/LD access)
    Route::prefix('management')->name('management.')->group(function () {
        Route::get('/team', \App\Livewire\Management\TeamOverview::class)->name('team');
    });

    // Office Admin routes (for Chiefs of Staff, Office Managers, etc.)
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/staff', \App\Livewire\Admin\StaffManagement::class)->name('admin.staff');
        Route::get('/metrics', \App\Livewire\Admin\Metrics::class)->name('admin.metrics');
        Route::get('/permissions', \App\Livewire\Admin\Permissions::class)->name('admin.permissions');
        Route::get('/how-ai-works', \App\Livewire\Admin\HowAiWorks::class)->name('admin.ai');
        Route::get('/billing', \App\Livewire\Admin\Billing::class)->name('admin.billing');
        Route::get('/integrations', \App\Livewire\Admin\Integrations::class)->name('admin.integrations');
        Route::get('/settings', \App\Livewire\Admin\OfficeSettings::class)->name('admin.settings');
    });

    // API routes
    Route::get('/api/mentions/search', [\App\Http\Controllers\Api\MentionSearchController::class, 'search'])->name('api.mentions.search');
    Route::get('/api/organizations/search', [\App\Http\Controllers\Api\MentionSearchController::class, 'searchOrganizations'])->name('api.organizations.search');
    Route::get('/api/people/search', [\App\Http\Controllers\Api\MentionSearchController::class, 'searchPeople'])->name('api.people.search');
    Route::get('/api/topics/search', [\App\Http\Controllers\Api\MentionSearchController::class, 'searchTopics'])->name('api.topics.search');
    Route::get('/api/staff/search', [\App\Http\Controllers\Api\MentionSearchController::class, 'searchStaff'])->name('api.staff.search');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// =============================================================================
// Platform Admin Panel - Internal tool for platform administrators only
// Accessible only to super admins (platform team members)
// =============================================================================
Route::middleware(['auth', 'super_admin'])->prefix('platform-admin')->name('platform.')->group(function () {
    Route::get('/', PlatformAdmin\Dashboard::class)->name('dashboard');
    Route::get('/beta-requests', PlatformAdmin\BetaRequests::class)->name('beta-requests');
    Route::get('/offices', PlatformAdmin\Placeholder::class)->name('offices');
    Route::get('/metrics', PlatformAdmin\Placeholder::class)->name('metrics');
    Route::get('/feedback', PlatformAdmin\FeedbackManager::class)->name('feedback');
    Route::get('/insights', PlatformAdmin\Placeholder::class)->name('insights');
    Route::get('/outreach', PlatformAdmin\Placeholder::class)->name('outreach');
    Route::get('/settings', PlatformAdmin\Placeholder::class)->name('settings');
});

require __DIR__ . '/auth.php';
