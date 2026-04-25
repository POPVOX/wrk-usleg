<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Member location tracking
        if (!Schema::hasTable('member_locations')) {
        Schema::create('member_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name'); // "Washington, DC" or "District Office 1"
            $table->string('timezone')->default('America/New_York');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('current_activity')->nullable(); // "In committee hearing"
            $table->timestamp('activity_until')->nullable();
            $table->boolean('is_current')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('is_current');
        });
        }

        // Position evolution tracking - tracks how positions change over time
        if (!Schema::hasTable('position_evolutions')) {
        Schema::create('position_evolutions', function (Blueprint $table) {
            $table->id();
            $table->string('issue'); // "Immigration", "Healthcare", etc.
            $table->enum('event_type', [
                'initial_position',
                'constituent_input',
                'committee_learning',
                'bill_sponsorship',
                'vote',
                'statement',
                'position_shift',
                'town_hall_moment',
                'committee_hearing',
                'bipartisan_collaboration'
            ]);
            $table->date('event_date');
            $table->text('description');
            $table->text('reasoning')->nullable(); // WHY this changed things
            $table->integer('influence_weight')->default(50); // 0-100
            $table->unsignedBigInteger('related_bill_id')->nullable();
            $table->unsignedBigInteger('related_meeting_id')->nullable();
            $table->unsignedBigInteger('previous_event_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['issue', 'event_date']);
            $table->foreign('previous_event_id')->references('id')->on('position_evolutions')->nullOnDelete();
        });
        }

        // Issue relationships - how policy areas connect
        if (!Schema::hasTable('issue_relationships')) {
        Schema::create('issue_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('issue_a');
            $table->string('issue_b');
            $table->enum('relationship_type', [
                'related',
                'supports',
                'conflicts_with',
                'prerequisite_for',
                'influences'
            ]);
            $table->integer('strength')->default(50); // 0-100
            $table->text('explanation')->nullable();
            $table->timestamps();
            
            $table->unique(['issue_a', 'issue_b', 'relationship_type']);
        });
        }

        // Constituent feedback aggregation
        if (!Schema::hasTable('constituent_feedback')) {
        Schema::create('constituent_feedback', function (Blueprint $table) {
            $table->id();
            $table->enum('source', ['phone', 'email', 'town_hall', 'office_hours', 'letter', 'social_media']);
            $table->string('issue');
            $table->text('summary');
            $table->enum('sentiment', ['positive', 'neutral', 'negative']);
            $table->date('received_date');
            $table->integer('count')->default(1); // Number of similar feedbacks
            $table->foreignId('related_meeting_id')->nullable()->constrained('meetings')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['issue', 'received_date']);
        });
        }

        // AI-generated insights
        if (!Schema::hasTable('ai_insights')) {
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->id();
            $table->enum('insight_type', ['suggestion', 'pattern', 'alert', 'opportunity']);
            $table->string('title');
            $table->text('description');
            $table->text('reasoning')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->string('category')->nullable(); // 'communication', 'legislation', 'district'
            $table->json('related_items')->nullable();
            $table->boolean('dismissed')->default(false);
            $table->timestamp('dismissed_at')->nullable();
            $table->foreignId('dismissed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['insight_type', 'dismissed']);
            $table->index('priority');
        });
        }

        // Member statements/communications
        if (!Schema::hasTable('member_statements')) {
        Schema::create('member_statements', function (Blueprint $table) {
            $table->id();
            $table->enum('statement_type', ['press_release', 'floor_speech', 'op_ed', 'social_media', 'interview', 'letter']);
            $table->string('title');
            $table->text('content');
            $table->text('summary')->nullable();
            $table->date('published_date');
            $table->string('outlet')->nullable(); // For media appearances
            $table->string('url')->nullable();
            $table->json('topics')->nullable(); // Related policy topics
            $table->json('related_bills')->nullable(); // Related bill numbers
            $table->integer('media_pickups')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('published_date');
            $table->index('statement_type');
        });
        }

        // Add staff_title and office_location to users if not exists
        if (!Schema::hasColumn('users', 'staff_title')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('staff_title')->nullable()->after('email');
            });
        }
        
        if (!Schema::hasColumn('users', 'office_location')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('office_location')->nullable()->after('staff_title');
            });
        }

        // Add requires_member flag to meetings
        if (!Schema::hasColumn('meetings', 'requires_member')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->boolean('requires_member')->default(false)->after('status');
            });
        }

        // Add meeting_type to meetings if not exists
        if (!Schema::hasColumn('meetings', 'meeting_type')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->enum('meeting_type', [
                    'constituent',
                    'stakeholder', 
                    'committee_hearing',
                    'floor_vote',
                    'internal',
                    'media',
                    'district_event',
                    'other'
                ])->default('other')->after('title');
            });
        }

        // Add talking_points_prepared to meetings
        if (!Schema::hasColumn('meetings', 'talking_points_prepared')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->boolean('talking_points_prepared')->default(false)->after('requires_member');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
        Schema::dropIfExists('constituent_feedback');
        Schema::dropIfExists('issue_relationships');
        Schema::dropIfExists('position_evolutions');
        Schema::dropIfExists('member_locations');
        Schema::dropIfExists('member_statements');

        if (Schema::hasColumn('users', 'staff_title')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('staff_title');
            });
        }

        if (Schema::hasColumn('users', 'office_location')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('office_location');
            });
        }

        if (Schema::hasColumn('meetings', 'requires_member')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->dropColumn('requires_member');
            });
        }

        if (Schema::hasColumn('meetings', 'meeting_type')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->dropColumn('meeting_type');
            });
        }

        if (Schema::hasColumn('meetings', 'talking_points_prepared')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->dropColumn('talking_points_prepared');
            });
        }
    }
};

