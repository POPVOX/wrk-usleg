<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('member_profiles')) {
            return;
        }

        Schema::table('member_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('member_profiles', 'member_name')) {
                $table->string('member_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('member_profiles', 'member_first_name')) {
                $table->string('member_first_name')->nullable()->after('member_name');
            }
            if (!Schema::hasColumn('member_profiles', 'member_last_name')) {
                $table->string('member_last_name')->nullable()->after('member_first_name');
            }
            if (!Schema::hasColumn('member_profiles', 'member_title')) {
                $table->string('member_title')->nullable()->after('member_last_name');
            }
            if (!Schema::hasColumn('member_profiles', 'member_party')) {
                $table->string('member_party')->nullable()->after('member_title');
            }
            if (!Schema::hasColumn('member_profiles', 'member_state')) {
                $table->string('member_state', 10)->nullable()->after('member_party');
            }
            if (!Schema::hasColumn('member_profiles', 'member_district')) {
                $table->string('member_district', 20)->nullable()->after('member_state');
            }
            if (!Schema::hasColumn('member_profiles', 'member_bioguide_id')) {
                $table->string('member_bioguide_id')->nullable()->after('member_district');
            }
            if (!Schema::hasColumn('member_profiles', 'member_photo_url')) {
                $table->string('member_photo_url')->nullable()->after('member_bioguide_id');
            }
            if (!Schema::hasColumn('member_profiles', 'government_level')) {
                $table->string('government_level')->nullable()->after('member_photo_url');
            }
            if (!Schema::hasColumn('member_profiles', 'chamber')) {
                $table->string('chamber')->nullable()->after('government_level');
            }
            if (!Schema::hasColumn('member_profiles', 'first_elected')) {
                $table->string('first_elected')->nullable()->after('chamber');
            }
            if (!Schema::hasColumn('member_profiles', 'official_website')) {
                $table->string('official_website')->nullable()->after('first_elected');
            }
            if (!Schema::hasColumn('member_profiles', 'social_media')) {
                $table->json('social_media')->nullable()->after('official_website');
            }
            if (!Schema::hasColumn('member_profiles', 'dc_office')) {
                $table->json('dc_office')->nullable()->after('social_media');
            }
            if (!Schema::hasColumn('member_profiles', 'district_offices')) {
                $table->json('district_offices')->nullable()->after('dc_office');
            }
            if (!Schema::hasColumn('member_profiles', 'district_cities')) {
                $table->json('district_cities')->nullable()->after('district_offices');
            }
            if (!Schema::hasColumn('member_profiles', 'district_counties')) {
                $table->json('district_counties')->nullable()->after('district_cities');
            }
            if (!Schema::hasColumn('member_profiles', 'news_sources')) {
                $table->json('news_sources')->nullable()->after('district_counties');
            }
            if (!Schema::hasColumn('member_profiles', 'legislative_activity')) {
                $table->json('legislative_activity')->nullable()->after('news_sources');
            }
            if (!Schema::hasColumn('member_profiles', 'setup_completed_at')) {
                $table->timestamp('setup_completed_at')->nullable()->after('legislative_activity');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('member_profiles')) {
            return;
        }

        Schema::table('member_profiles', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('member_profiles', 'member_name') ? 'member_name' : null,
                Schema::hasColumn('member_profiles', 'member_first_name') ? 'member_first_name' : null,
                Schema::hasColumn('member_profiles', 'member_last_name') ? 'member_last_name' : null,
                Schema::hasColumn('member_profiles', 'member_title') ? 'member_title' : null,
                Schema::hasColumn('member_profiles', 'member_party') ? 'member_party' : null,
                Schema::hasColumn('member_profiles', 'member_state') ? 'member_state' : null,
                Schema::hasColumn('member_profiles', 'member_district') ? 'member_district' : null,
                Schema::hasColumn('member_profiles', 'member_bioguide_id') ? 'member_bioguide_id' : null,
                Schema::hasColumn('member_profiles', 'member_photo_url') ? 'member_photo_url' : null,
                Schema::hasColumn('member_profiles', 'government_level') ? 'government_level' : null,
                Schema::hasColumn('member_profiles', 'chamber') ? 'chamber' : null,
                Schema::hasColumn('member_profiles', 'first_elected') ? 'first_elected' : null,
                Schema::hasColumn('member_profiles', 'official_website') ? 'official_website' : null,
                Schema::hasColumn('member_profiles', 'social_media') ? 'social_media' : null,
                Schema::hasColumn('member_profiles', 'dc_office') ? 'dc_office' : null,
                Schema::hasColumn('member_profiles', 'district_offices') ? 'district_offices' : null,
                Schema::hasColumn('member_profiles', 'district_cities') ? 'district_cities' : null,
                Schema::hasColumn('member_profiles', 'district_counties') ? 'district_counties' : null,
                Schema::hasColumn('member_profiles', 'news_sources') ? 'news_sources' : null,
                Schema::hasColumn('member_profiles', 'legislative_activity') ? 'legislative_activity' : null,
                Schema::hasColumn('member_profiles', 'setup_completed_at') ? 'setup_completed_at' : null,
            ]);

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
