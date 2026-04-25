<?php

namespace Database\Seeders;

use App\Models\Issue;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing projects
        Issue::query()->delete();

        $projects = [
            // REBOOT CONGRESS - Main Initiative
            [
                'name' => 'REBOOT CONGRESS',
                'issue_type' => 'initiative',
                'lead' => 'Marci',
                'scope' => 'US',
                'status' => 'active',
                'children' => [
                    [
                        'name' => 'REBOOT CONGRESS REPORT',
                        'issue_type' => 'publication',
                        'status' => 'active',
                        'children' => [
                            ['name' => 'The Member Experience', 'issue_type' => 'chapter', 'lead' => 'Danielle', 'description' => 'Examines how Members are onboarded, equipped, and supported throughout their service—from new Member orientation through the tools and information systems that shape daily decision-making.'],
                            ['name' => 'The Congressional Workforce', 'issue_type' => 'chapter', 'lead' => 'Danielle', 'description' => 'Addresses the staffing crisis head-on: compensation, retention, career paths, professional development, and the knowledge management systems needed to preserve institutional memory across inevitable turnover.'],
                            ['name' => 'Constituent Engagement', 'issue_type' => 'chapter', 'lead' => 'Anne', 'description' => 'Reimagines the full stack of interactions between Congress and the public—from casework to policy input to civic education—for a digital age.'],
                            ['name' => 'Technology Governance', 'issue_type' => 'chapter', 'lead' => 'Aubrey', 'description' => 'Tackles the procurement, development, and security challenges that have left Congress technologically behind, with specific attention to responsible AI adoption.'],
                            ['name' => 'Support Agencies Transformed', 'issue_type' => 'chapter', 'lead' => 'Marci', 'description' => 'Envisions how CRS, CBO, GAO, and other congressional support offices can be integrated, modernized, and empowered to meet 21st-century demands.'],
                            ['name' => 'Oversight Reimagined', 'issue_type' => 'chapter', 'lead' => 'Marci', 'description' => 'Proposes a new model for congressional oversight—one that emphasizes post-legislative scrutiny, real-time monitoring, and data-driven accountability over episodic hearings.'],
                            ['name' => 'Budgeting That Works', 'issue_type' => 'chapter', 'lead' => 'Marci', 'description' => 'Confronts the dysfunction of the current budget process and offers reforms calibrated to an era that demands both fiscal discipline and rapid response capability.'],
                            ['name' => 'Committees and Procedure', 'issue_type' => 'chapter', 'lead' => 'Marci', 'description' => 'Examines the committee system, floor procedures, and parliamentary practices that structure how Congress does its work—and how they might evolve.'],
                            ['name' => 'The Legislative Product', 'issue_type' => 'chapter', 'lead' => 'Marci', 'description' => 'Focuses on law drafting, codification, and the legislative record—the documentary output that is Congress\'s lasting contribution to American governance.'],
                            ['name' => 'Interbranch Relations', 'issue_type' => 'chapter', 'lead' => 'Anne', 'description' => 'Addresses Congress\'s relationships with the executive and judicial branches, including the feedback loops needed to ensure laws work as intended.'],
                            ['name' => 'Global Learning', 'issue_type' => 'chapter', 'lead' => 'Aubrey', 'description' => 'Draws lessons from peer parliaments around the world that have pioneered innovations Congress can adapt.'],
                            ['name' => 'Civil Society and External Engagement', 'issue_type' => 'chapter', 'lead' => 'Anne', 'description' => 'Considers Congress\'s relationship with the broader ecosystem of organizations, experts, and citizens that support democratic governance.'],
                        ],
                    ],
                    [
                        'name' => 'REBOOT CONGRESS EVENTS',
                        'issue_type' => 'event',
                        'status' => 'planning',
                        'children' => [
                            ['name' => 'Q1 Hill Event', 'issue_type' => 'event'],
                            ['name' => 'Q2 Hill Event', 'issue_type' => 'event'],
                            ['name' => 'Q3 Hill Event', 'issue_type' => 'event'],
                            ['name' => 'Q4 Hill Event', 'issue_type' => 'event'],
                        ],
                    ],
                ],
            ],

            // REP BODIES V2
            [
                'name' => 'REP BODIES V2',
                'issue_type' => 'publication',
                'lead' => 'Marci',
                'scope' => 'Global',
                'status' => 'planning',
                'description' => 'Vol II of Rep Bodies launch',
            ],

            // CASEWORK NAVIGATOR
            [
                'name' => 'CASEWORK NAVIGATOR',
                'issue_type' => 'initiative',
                'scope' => 'US',
                'status' => 'active',
                'children' => [
                    ['name' => 'Casework Navigator Newsletter', 'issue_type' => 'newsletter'],
                    [
                        'name' => 'Casework Navigator Events',
                        'issue_type' => 'event',
                        'children' => [
                            ['name' => 'HDS Data Management Workshop', 'issue_type' => 'event'],
                            ['name' => 'District Briefing: Full-Stack Constituent Engagement', 'issue_type' => 'event'],
                        ],
                    ],
                ],
            ],

            // DEPARTURE DIALOGUES
            [
                'name' => 'DEPARTURE DIALOGUES',
                'issue_type' => 'initiative',
                'scope' => 'US',
                'status' => 'active',
                'children' => [
                    ['name' => 'DD Report', 'issue_type' => 'publication'],
                ],
            ],

            // FUTUREPROOFING
            [
                'name' => 'FUTUREPROOFING',
                'issue_type' => 'initiative',
                'scope' => 'US',
                'status' => 'active',
                'children' => [
                    ['name' => 'FutureProofing Newsletter', 'issue_type' => 'newsletter'],
                    ['name' => 'Hill Briefing: Future of Staffing', 'issue_type' => 'event'],
                ],
            ],

            // GAVEL IN
            [
                'name' => 'GAVEL IN',
                'issue_type' => 'initiative',
                'scope' => 'US',
                'status' => 'active',
                'children' => [
                    ['name' => 'Podcast', 'issue_type' => 'publication'],
                ],
            ],

            // DIGITAL PARLIAMENTS PROJECT
            [
                'name' => 'DIGITAL PARLIAMENTS PROJECT',
                'issue_type' => 'initiative',
                'scope' => 'Global',
                'status' => 'active',
                'children' => [
                    [
                        'name' => 'PARLLINK',
                        'issue_type' => 'tool',
                        'children' => [
                            ['name' => 'Hansard Transcription Tool', 'issue_type' => 'tool'],
                        ],
                    ],
                    [
                        'name' => 'AFRICA DPP',
                        'issue_type' => 'initiative',
                        'children' => [
                            ['name' => 'Uganda', 'issue_type' => 'component'],
                            ['name' => 'Ghana', 'issue_type' => 'component'],
                            ['name' => 'Pan-African Parliament', 'issue_type' => 'component'],
                        ],
                    ],
                    [
                        'name' => 'CARIBBEAN DPP',
                        'issue_type' => 'initiative',
                        'children' => [
                            ['name' => 'Bahamas', 'issue_type' => 'component'],
                            ['name' => 'Jamaica', 'issue_type' => 'component'],
                            ['name' => 'Belize', 'issue_type' => 'component'],
                            ['name' => 'Dominica', 'issue_type' => 'component'],
                            ['name' => 'Saint Lucia', 'issue_type' => 'component'],
                            ['name' => 'St Vincent & Grenadines', 'issue_type' => 'component'],
                            ['name' => 'Barbados', 'issue_type' => 'component'],
                        ],
                    ],
                ],
            ],

            // APPROPRIATIONS
            [
                'name' => 'APPROPRIATIONS',
                'issue_type' => 'initiative',
                'scope' => 'US',
                'status' => 'active',
                'children' => [
                    ['name' => 'House Requests', 'issue_type' => 'component'],
                    ['name' => 'Senate Requests', 'issue_type' => 'component'],
                    ['name' => 'ApproPRO Tool', 'issue_type' => 'tool'],
                ],
            ],

            // NEW MEMBER ORIENTATION
            [
                'name' => 'NEW MEMBER ORIENTATION',
                'issue_type' => 'initiative',
                'scope' => 'US',
                'status' => 'planning',
            ],

            // 120th RULES PACKAGE
            [
                'name' => '120th RULES PACKAGE',
                'issue_type' => 'research',
                'scope' => 'US',
                'status' => 'planning',
            ],

            // GLOBAL PARTNERSHIPS
            [
                'name' => 'GLOBAL PARTNERSHIPS',
                'issue_type' => 'initiative',
                'scope' => 'Global',
                'status' => 'active',
                'children' => [
                    ['name' => 'US/UK Fellowship WFD', 'issue_type' => 'component'],
                    ['name' => 'IPU/CPA Tech Collab', 'issue_type' => 'component'],
                    ['name' => 'Global Trainings', 'issue_type' => 'event'],
                ],
            ],

            // STATES
            [
                'name' => 'STATES',
                'issue_type' => 'initiative',
                'scope' => 'US',
                'status' => 'planning',
                'children' => [
                    ['name' => 'NCSL', 'issue_type' => 'component'],
                ],
            ],

            // RECONSTITUTION
            [
                'name' => 'RECONSTITUTION',
                'issue_type' => 'research',
                'scope' => 'US',
                'status' => 'planning',
            ],
        ];

        $sortOrder = 0;
        foreach ($projects as $projectData) {
            $sortOrder++;
            $this->createIssueWithChildren($projectData, null, $sortOrder);
        }
    }

    private function createIssueWithChildren(array $data, ?int $parentId, int &$sortOrder): void
    {
        $children = $data['children'] ?? [];
        unset($data['children']);

        $issue = Issue::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'issue_type' => $data['issue_type'] ?? $data['issue_type'] ?? 'initiative',
            'parent_issue_id' => $parentId,
            'lead' => $data['lead'] ?? null,
            'scope' => $data['scope'] ?? null,
            'status' => $data['status'] ?? 'planning',
            'sort_order' => $sortOrder,
        ]);

        foreach ($children as $childData) {
            $sortOrder++;
            $this->createIssueWithChildren($childData, $issue->id, $sortOrder);
        }
    }
}
