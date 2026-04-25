<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            
            // Policy Priorities (JSON arrays for flexibility)
            $table->json('top_policy_areas')->nullable(); // Array of {area, priority_rank, notes}
            $table->json('signature_issues')->nullable(); // Issues they want to be known for
            $table->json('emerging_interests')->nullable(); // New areas of focus
            
            // Political Positioning
            $table->string('governing_philosophy')->nullable(); // bipartisan, progressive, conservative, moderate, pragmatic
            $table->text('philosophy_description')->nullable(); // Free text description
            $table->json('party_differentiators')->nullable(); // How they differ from party mainstream
            $table->json('non_negotiables')->nullable(); // Red line issues
            $table->json('bipartisan_openings')->nullable(); // Areas open to cross-aisle work
            
            // District/Constituent Focus
            $table->json('key_demographics')->nullable(); // Important constituency groups
            $table->json('economic_priorities')->nullable(); // Jobs, industries, development
            $table->json('geographic_focuses')->nullable(); // Specific areas within district
            $table->json('constituent_concerns')->nullable(); // Top issues constituents raise
            
            // Personal Background (policy-relevant)
            $table->text('professional_background')->nullable();
            $table->json('formative_experiences')->nullable(); // Experiences that shape views
            $table->json('personal_connections')->nullable(); // Personal ties to issues
            
            // Communication Preferences
            $table->string('preferred_tone')->nullable(); // formal, conversational, passionate, measured
            $table->json('key_phrases')->nullable(); // Language they like to use
            $table->json('talking_points_style')->nullable(); // How they like briefings
            $table->json('topics_to_emphasize')->nullable();
            $table->json('topics_to_avoid')->nullable();
            
            // Goals & Vision
            $table->json('term_goals')->nullable(); // This term priorities
            $table->text('long_term_vision')->nullable();
            $table->json('legacy_items')->nullable(); // What they want to be remembered for
            $table->json('committee_priorities')->nullable(); // Focus within committees
            
            // AI/System Preferences
            $table->json('ai_context_notes')->nullable(); // Notes to include in AI prompts
            $table->boolean('use_in_prompts')->default(true);
            
            // Section skip flags
            $table->boolean('skip_positioning')->default(false);
            
            // State Legislature specific
            $table->string('session_type')->nullable(); // full_time, long_session, short_session, biennial
            $table->string('other_occupation')->nullable(); // For part-time legislators
            $table->json('state_federal_issues')->nullable(); // Federal issues relevant to state work
            
            // Local Government specific
            $table->string('local_role_type')->nullable(); // council_ward, mayor, etc.
            $table->string('governance_structure')->nullable(); // council_manager, strong_mayor, etc.
            $table->string('admin_relationship')->nullable(); // collaborative, oversight, etc.
            $table->json('boards_commissions')->nullable(); // Boards/commissions served on
            
            // Metadata
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};

