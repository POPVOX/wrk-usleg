<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Phase 1: Congressional Office OS Migration
     * 
     * 1. Rename existing "issues" table to "topics" (it was used for meeting tags)
     * 2. Rename "projects" table to "issues" (the main feature)
     * 3. Update all pivot tables and foreign keys
     * 4. Add new legislative fields to issues table
     * 5. Remove grants-related tables
     */
    public function up(): void
    {
        // Step 1: Rename the existing "issues" table to "topics"
        // This table was used as simple tags for meetings
        Schema::rename('issues', 'topics');

        // Step 2: Rename pivot table meeting_issue to meeting_topic
        Schema::rename('meeting_issue', 'meeting_topic');

        // Step 3: Update the meeting_topic table's foreign key column name
        Schema::table('meeting_topic', function (Blueprint $table) {
            $table->renameColumn('issue_id', 'topic_id');
        });

        if (Schema::hasTable('press_clip_issue') && !Schema::hasTable('press_clip_topic')) {
            Schema::rename('press_clip_issue', 'press_clip_topic');

            Schema::table('press_clip_topic', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'topic_id');
            });
        }

        if (Schema::hasTable('pitch_issue') && !Schema::hasTable('pitch_topic')) {
            Schema::rename('pitch_issue', 'pitch_topic');

            Schema::table('pitch_topic', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'topic_id');
            });
        }

        if (Schema::hasTable('inquiry_issue') && !Schema::hasTable('inquiry_topic')) {
            Schema::rename('inquiry_issue', 'inquiry_topic');

            Schema::table('inquiry_topic', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'topic_id');
            });
        }

        // Step 4: Rename project_issue pivot to project_topic (will be issue_topic later)
        if (Schema::hasTable('project_issue')) {
            Schema::rename('project_issue', 'project_topic');
            
            Schema::table('project_topic', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'topic_id');
            });
        }

        // Step 5: Now rename "projects" table to "issues"
        Schema::rename('projects', 'issues');

        // Step 6: Rename the project_topic pivot table to issue_topic
        if (Schema::hasTable('project_topic')) {
            Schema::rename('project_topic', 'issue_topic');
            
            Schema::table('issue_topic', function (Blueprint $table) {
                $table->renameColumn('project_id', 'issue_id');
            });
        }

        // Step 7: Update meeting_project pivot table
        if (Schema::hasTable('meeting_project')) {
            Schema::table('meeting_project', function (Blueprint $table) {
                $table->renameColumn('project_id', 'issue_id');
            });
            Schema::rename('meeting_project', 'issue_meeting');
        }

        // Step 8: Update project_organization pivot table
        if (Schema::hasTable('project_organization')) {
            Schema::table('project_organization', function (Blueprint $table) {
                $table->renameColumn('project_id', 'issue_id');
            });
            Schema::rename('project_organization', 'issue_organization');
        }

        // Step 9: Update project_person pivot table
        if (Schema::hasTable('project_person')) {
            Schema::table('project_person', function (Blueprint $table) {
                $table->renameColumn('project_id', 'issue_id');
            });
            Schema::rename('project_person', 'issue_person');
        }

        // Step 10: Update project_staff pivot table
        if (Schema::hasTable('project_staff')) {
            Schema::table('project_staff', function (Blueprint $table) {
                $table->renameColumn('project_id', 'issue_id');
            });
            Schema::rename('project_staff', 'issue_staff');
        }

        if (Schema::hasTable('press_clip_project') && !Schema::hasTable('press_clip_issue')) {
            Schema::rename('press_clip_project', 'press_clip_issue');

            Schema::table('press_clip_issue', function (Blueprint $table) {
                $table->renameColumn('project_id', 'issue_id');
            });
        }

        foreach (['decisions', 'commitments', 'pitches', 'inquiries'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'project_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('project_id', 'issue_id');
                });
            }
        }

        // Step 11: Update project-related tables (rename project_id to issue_id)
        $projectRelatedTables = [
            'project_decisions' => 'issue_decisions',
            'project_milestones' => 'issue_milestones',
            'project_questions' => 'issue_questions',
            'project_documents' => 'issue_documents',
            'project_notes' => 'issue_notes',
            'project_workstreams' => 'issue_workstreams',
            'project_publications' => 'issue_publications',
            'project_events' => 'issue_events',
            'project_chat_messages' => 'issue_chat_messages',
        ];

        foreach ($projectRelatedTables as $oldTable => $newTable) {
            if (Schema::hasTable($oldTable)) {
                Schema::table($oldTable, function (Blueprint $table) {
                    $table->renameColumn('project_id', 'issue_id');
                });
                Schema::rename($oldTable, $newTable);
            }
        }

        // Step 12: Update self-referential parent column in issues
        if (Schema::hasColumn('issues', 'parent_project_id')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->renameColumn('parent_project_id', 'parent_issue_id');
            });
        }

        // Step 13: Update project_type column to issue_type
        if (Schema::hasColumn('issues', 'project_type')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->renameColumn('project_type', 'issue_type');
            });
        }

        // Step 14: Update project_path column if exists
        if (Schema::hasColumn('issues', 'project_path')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->renameColumn('project_path', 'issue_path');
            });
        }

        // Step 15: Add new congressional fields to issues table
        Schema::table('issues', function (Blueprint $table) {
            $table->string('committee_relevance')->nullable()->after('issue_type');
            $table->string('legislative_vehicle')->nullable()->after('committee_relevance');
            $table->string('priority_level')->default('Tracking')->after('scope');
        });

        // Step 16: Drop grant-related tables
        Schema::dropIfExists('grant_project'); // pivot table
        Schema::dropIfExists('reporting_requirements');
        Schema::dropIfExists('grant_documents');
        Schema::dropIfExists('grants');

        // Remove grant foreign key from issues if it exists
        if (Schema::hasColumn('issues', 'grant_id')) {
            Schema::table('issues', function (Blueprint $table) {
                // Try to drop foreign key constraint if it exists
                try {
                    $table->dropForeign(['grant_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('grant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This is a complex migration - full rollback would need to recreate grants tables
        // For simplicity, we'll just reverse the renaming

        // Remove new congressional fields
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn(['committee_relevance', 'legislative_vehicle', 'priority_level']);
        });

        // Reverse parent_issue_id
        if (Schema::hasColumn('issues', 'parent_issue_id')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->renameColumn('parent_issue_id', 'parent_project_id');
            });
        }

        // Reverse issue_type to project_type
        if (Schema::hasColumn('issues', 'issue_type')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->renameColumn('issue_type', 'project_type');
            });
        }

        // Reverse issue_path to project_path
        if (Schema::hasColumn('issues', 'issue_path')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->renameColumn('issue_path', 'project_path');
            });
        }

        // Reverse table renames - related tables
        $tableReverses = [
            'issue_decisions' => 'project_decisions',
            'issue_milestones' => 'project_milestones',
            'issue_questions' => 'project_questions',
            'issue_documents' => 'project_documents',
            'issue_notes' => 'project_notes',
            'issue_workstreams' => 'project_workstreams',
            'issue_publications' => 'project_publications',
            'issue_events' => 'project_events',
            'issue_chat_messages' => 'project_chat_messages',
        ];

        foreach ($tableReverses as $currentTable => $oldTable) {
            if (Schema::hasTable($currentTable)) {
                Schema::table($currentTable, function (Blueprint $table) {
                    $table->renameColumn('issue_id', 'project_id');
                });
                Schema::rename($currentTable, $oldTable);
            }
        }

        // Reverse pivot tables
        if (Schema::hasTable('issue_staff')) {
            Schema::table('issue_staff', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'project_id');
            });
            Schema::rename('issue_staff', 'project_staff');
        }

        if (Schema::hasTable('issue_person')) {
            Schema::table('issue_person', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'project_id');
            });
            Schema::rename('issue_person', 'project_person');
        }

        if (Schema::hasTable('issue_organization')) {
            Schema::table('issue_organization', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'project_id');
            });
            Schema::rename('issue_organization', 'project_organization');
        }

        if (Schema::hasTable('press_clip_issue')) {
            Schema::table('press_clip_issue', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'project_id');
            });
            Schema::rename('press_clip_issue', 'press_clip_project');
        }

        if (Schema::hasTable('press_clip_topic')) {
            Schema::table('press_clip_topic', function (Blueprint $table) {
                $table->renameColumn('topic_id', 'issue_id');
            });
            Schema::rename('press_clip_topic', 'press_clip_issue');
        }

        if (Schema::hasTable('pitch_topic')) {
            Schema::table('pitch_topic', function (Blueprint $table) {
                $table->renameColumn('topic_id', 'issue_id');
            });
            Schema::rename('pitch_topic', 'pitch_issue');
        }

        if (Schema::hasTable('inquiry_topic')) {
            Schema::table('inquiry_topic', function (Blueprint $table) {
                $table->renameColumn('topic_id', 'issue_id');
            });
            Schema::rename('inquiry_topic', 'inquiry_issue');
        }

        foreach (['decisions', 'commitments', 'pitches', 'inquiries'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'issue_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('issue_id', 'project_id');
                });
            }
        }

        if (Schema::hasTable('issue_meeting')) {
            Schema::table('issue_meeting', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'project_id');
            });
            Schema::rename('issue_meeting', 'meeting_project');
        }

        // Rename issues back to projects
        Schema::rename('issues', 'projects');

        // Rename issue_topic back to project_topic then project_issue
        if (Schema::hasTable('issue_topic')) {
            Schema::table('issue_topic', function (Blueprint $table) {
                $table->renameColumn('issue_id', 'project_id');
            });
            Schema::rename('issue_topic', 'project_issue');
            
            Schema::table('project_issue', function (Blueprint $table) {
                $table->renameColumn('topic_id', 'issue_id');
            });
        }

        // Rename meeting_topic back to meeting_issue
        Schema::table('meeting_topic', function (Blueprint $table) {
            $table->renameColumn('topic_id', 'issue_id');
        });
        Schema::rename('meeting_topic', 'meeting_issue');

        // Rename topics back to issues
        Schema::rename('topics', 'issues');
    }
};


