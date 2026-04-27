<?php

namespace App\Providers;

use App\Models\MemberProfile;
use App\Models\Organization;
use App\Models\Person;
use App\Observers\OrganizationObserver;
use App\Observers\PersonObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers for automatic LinkedIn sync
        Person::observe(PersonObserver::class);
        Organization::observe(OrganizationObserver::class);

        $this->hydrateOfficeConfigFromMemberProfile();
    }

    protected function hydrateOfficeConfigFromMemberProfile(): void
    {
        try {
            if (!Schema::hasTable('member_profiles')) {
                return;
            }

            $profile = MemberProfile::query()->first();

            if (!$profile || !$profile->hasConfiguredMember()) {
                return;
            }

            config([
                'office' => array_replace_recursive(
                    config('office', []),
                    $profile->toOfficeConfigOverrides(),
                ),
            ]);
        } catch (Throwable) {
            // Ignore database/config hydration issues during bootstrap.
            // The app can still fall back to static office config values.
        }
    }
}
