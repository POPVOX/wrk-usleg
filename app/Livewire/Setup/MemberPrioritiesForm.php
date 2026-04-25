<?php

namespace App\Livewire\Setup;

use App\Models\MemberProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Member Priorities & Interests')]
class MemberPrioritiesForm extends Component
{
    public int $currentSection = 1;
    public int $totalSections = 6;
    
    // Government level from config
    public string $governmentLevel = 'federal';

    // Section 1: Policy Priorities
    public array $top_policy_areas = [];
    public string $new_policy_area = '';
    public array $signature_issues = [];
    public string $new_signature_issue = '';
    public array $emerging_interests = [];
    public string $new_emerging_interest = '';

    // Section 2: Political Positioning
    public bool $skip_positioning = false;
    public string $governing_philosophy = '';
    public string $philosophy_description = '';
    public array $non_negotiables = [];
    public string $new_non_negotiable = '';
    public array $bipartisan_openings = [];
    public string $new_bipartisan_opening = '';

    // Section 3: District Focus
    public array $key_demographics = [];
    public string $new_demographic = '';
    public array $economic_priorities = [];
    public string $new_economic_priority = '';
    public array $constituent_concerns = [];
    public string $new_constituent_concern = '';

    // Section 4: Personal Background
    public string $professional_background = '';
    public array $formative_experiences = [];
    public string $new_formative_experience = '';
    public array $personal_connections = [];
    public string $new_personal_connection = '';

    // Section 5: Communication Style
    public string $preferred_tone = '';
    public array $key_phrases = [];
    public string $new_key_phrase = '';
    public array $topics_to_emphasize = [];
    public string $new_topic_emphasize = '';
    public array $topics_to_avoid = [];
    public string $new_topic_avoid = '';

    // Section 6: Goals & Vision
    public array $term_goals = [];
    public string $new_term_goal = '';
    public string $long_term_vision = '';
    public array $legacy_items = [];
    public string $new_legacy_item = '';

    // AI Settings
    public bool $use_in_prompts = true;
    public array $ai_context_notes = [];
    public string $new_ai_note = '';

    // State Legislature specific
    public string $session_type = '';
    public string $other_occupation = '';
    public array $state_federal_issues = [];
    public string $new_state_federal_issue = '';

    // Local Government specific
    public string $local_role_type = '';
    public string $governance_structure = '';
    public string $admin_relationship = '';
    public array $boards_commissions = [];
    public string $new_board_commission = '';

    // Policy area options by level
    public function getPolicyAreaOptionsProperty(): array
    {
        return match($this->governmentLevel) {
            'state' => [
                'Agriculture & Rural Affairs',
                'Budget & Appropriations',
                'Criminal Justice & Public Safety',
                'Economic Development',
                'Education (K-12)',
                'Education (Higher Ed)',
                'Elections & Government Reform',
                'Energy & Utilities',
                'Environment & Natural Resources',
                'Healthcare & Human Services',
                'Housing & Community Development',
                'Infrastructure & Transportation',
                'Insurance & Financial Regulation',
                'Judiciary & Civil Law',
                'Labor & Workforce',
                'Local Government Relations',
                'Medicaid & Social Services',
                'Pensions & Retirement',
                'State-Federal Relations',
                'Taxation & Revenue',
                'Technology & Innovation',
                'Veterans & Military Affairs',
                'Other',
            ],
            'local' => [
                'Budget & Finance',
                'Code Enforcement',
                'Community Development',
                'Economic Development & Jobs',
                'Emergency Services',
                'Environmental Services',
                'Housing & Affordable Housing',
                'Infrastructure (roads, bridges, utilities)',
                'Land Use & Zoning',
                'Libraries & Cultural Facilities',
                'Neighborhoods & Community Relations',
                'Parks & Recreation',
                'Permitting & Business Licensing',
                'Police & Public Safety',
                'Public Health',
                'Public Transit',
                'Schools (if locally controlled)',
                'Senior Services',
                'Sustainability & Climate',
                'Youth Programs',
                'Other',
            ],
            default => [ // federal
                'Agriculture & Food',
                'Armed Services & Defense',
                'Budget & Appropriations',
                'Civil Rights & Liberties',
                'Climate & Environment',
                'Criminal Justice',
                'Economic Development',
                'Education (K-12)',
                'Education (Higher Ed)',
                'Energy',
                'Financial Services',
                'Foreign Affairs',
                'Government Reform',
                'Healthcare',
                'Homeland Security',
                'Housing',
                'Immigration',
                'Infrastructure & Transportation',
                'Labor & Workforce',
                'Natural Resources',
                'Science & Technology',
                'Small Business',
                'Social Security & Retirement',
                'Taxation',
                'Technology & Telecommunications',
                'Trade',
                'Veterans Affairs',
                'Other',
            ],
        };
    }

    public function getPhilosophyOptionsProperty(): array
    {
        if ($this->governmentLevel === 'local') {
            return [
                'pro_growth' => 'Pro-Growth — Prioritize development and economic expansion',
                'managed_growth' => 'Managed Growth — Balance development with neighborhood preservation',
                'neighborhood_focused' => 'Neighborhood-Focused — Prioritize existing residents and community character',
                'fiscally_conservative' => 'Fiscally Conservative — Minimize spending, keep taxes low',
                'investment_oriented' => 'Investment-Oriented — Invest in services and infrastructure for long-term benefit',
                'reform_minded' => 'Reform-Minded — Focus on improving government efficiency and transparency',
                'constituent_service' => 'Constituent Service-Focused — Prioritize responsiveness to residents',
                'collaborative' => 'Collaborative — Build consensus across the council/board',
                'independent' => 'Independent — Issue-by-issue evaluation, no fixed ideology',
            ];
        }

        return [
            'progressive' => 'Progressive — Push for transformative change',
            'liberal' => 'Liberal — Strong social programs and civil rights focus',
            'moderate' => 'Moderate — Balance between parties, pragmatic approach',
            'bipartisan' => 'Bipartisan — Actively seek cross-party solutions',
            'conservative' => 'Conservative — Traditional values, limited government',
            'libertarian' => 'Libertarian — Individual liberty, minimal intervention',
            'pragmatic' => 'Pragmatic — Results-focused, ideology-flexible',
            'populist' => 'Populist — Working-class focused, anti-establishment',
        ];
    }

    public array $toneOptions = [
        'formal' => 'Formal & Professional — Traditional, measured language',
        'conversational' => 'Conversational — Accessible, relatable',
        'passionate' => 'Passionate — Strong convictions, emotional appeals',
        'measured' => 'Measured & Analytical — Data-driven, careful',
        'folksy' => 'Folksy — Down-to-earth, storytelling style',
        'direct' => 'Direct — Straightforward, no-nonsense',
        'diplomatic' => 'Diplomatic — Consensus-building, inclusive',
    ];

    public array $sessionTypeOptions = [
        'full_time' => 'Full-time legislature (year-round)',
        'long_session' => 'Part-time with long session (90+ days)',
        'short_session' => 'Part-time with short session (under 60 days)',
        'biennial' => 'Biennial sessions (every other year)',
    ];

    public array $localRoleOptions = [
        'council_ward' => 'City Council Member (ward/district-based)',
        'council_at_large' => 'City Council Member (at-large)',
        'county_commissioner' => 'County Commissioner / Supervisor',
        'mayor_council' => 'Mayor (with council)',
        'mayor_strong' => 'Mayor (strong mayor system)',
        'township_trustee' => 'Township Trustee',
        'school_board' => 'School Board Member',
        'other' => 'Other',
    ];

    public array $governanceStructureOptions = [
        'council_manager' => 'Council-Manager (city manager runs operations)',
        'strong_mayor' => 'Strong Mayor (mayor runs operations)',
        'commission' => 'Commission (commissioners run departments)',
        'town_meeting' => 'Town Meeting',
        'other' => 'Other',
    ];

    public array $adminRelationshipOptions = [
        'collaborative' => 'Collaborative — Work closely with administration',
        'oversight' => 'Oversight-Focused — Focus on accountability and review',
        'arms_length' => 'Arm\'s Length — Maintain professional distance',
        'mixed' => 'Mixed — Depends on the issue',
    ];

    public function mount()
    {
        // Get government level from config
        $this->governmentLevel = config('office.government_level', 'federal');
        
        $profile = MemberProfile::current();

        // Load existing data
        $this->top_policy_areas = $profile->top_policy_areas ?? [];
        $this->signature_issues = $profile->signature_issues ?? [];
        $this->emerging_interests = $profile->emerging_interests ?? [];
        $this->skip_positioning = $profile->skip_positioning ?? false;
        $this->governing_philosophy = $profile->governing_philosophy ?? '';
        $this->philosophy_description = $profile->philosophy_description ?? '';
        $this->non_negotiables = $profile->non_negotiables ?? [];
        $this->bipartisan_openings = $profile->bipartisan_openings ?? [];
        $this->key_demographics = $profile->key_demographics ?? [];
        $this->economic_priorities = $profile->economic_priorities ?? [];
        $this->constituent_concerns = $profile->constituent_concerns ?? [];
        $this->professional_background = $profile->professional_background ?? '';
        $this->formative_experiences = $profile->formative_experiences ?? [];
        $this->personal_connections = $profile->personal_connections ?? [];
        $this->preferred_tone = $profile->preferred_tone ?? '';
        $this->key_phrases = $profile->key_phrases ?? [];
        $this->topics_to_emphasize = $profile->topics_to_emphasize ?? [];
        $this->topics_to_avoid = $profile->topics_to_avoid ?? [];
        $this->term_goals = $profile->term_goals ?? [];
        $this->long_term_vision = $profile->long_term_vision ?? '';
        $this->legacy_items = $profile->legacy_items ?? [];
        $this->use_in_prompts = $profile->use_in_prompts ?? true;
        $this->ai_context_notes = $profile->ai_context_notes ?? [];
        
        // State-specific fields
        $this->session_type = $profile->session_type ?? '';
        $this->other_occupation = $profile->other_occupation ?? '';
        $this->state_federal_issues = $profile->state_federal_issues ?? [];
        
        // Local-specific fields
        $this->local_role_type = $profile->local_role_type ?? '';
        $this->governance_structure = $profile->governance_structure ?? '';
        $this->admin_relationship = $profile->admin_relationship ?? '';
        $this->boards_commissions = $profile->boards_commissions ?? [];
    }

    // Navigation
    public function nextSection()
    {
        $this->saveCurrentSection();
        $this->currentSection = min($this->currentSection + 1, $this->totalSections);
    }

    public function previousSection()
    {
        $this->saveCurrentSection();
        $this->currentSection = max($this->currentSection - 1, 1);
    }

    public function goToSection(int $section)
    {
        $this->saveCurrentSection();
        $this->currentSection = $section;
    }

    // Add item methods
    public function addPolicyArea()
    {
        if (empty($this->new_policy_area)) return;
        
        $this->top_policy_areas[] = [
            'area' => $this->new_policy_area,
            'priority_rank' => count($this->top_policy_areas) + 1,
            'notes' => '',
        ];
        $this->new_policy_area = '';
    }

    public function removePolicyArea(int $index)
    {
        unset($this->top_policy_areas[$index]);
        $this->top_policy_areas = array_values($this->top_policy_areas);
        // Re-rank
        foreach ($this->top_policy_areas as $i => &$area) {
            $area['priority_rank'] = $i + 1;
        }
    }

    public function updatePolicyAreaNotes(int $index, string $notes)
    {
        if (isset($this->top_policy_areas[$index])) {
            $this->top_policy_areas[$index]['notes'] = $notes;
        }
    }

    public function movePolicyAreaUp(int $index)
    {
        if ($index > 0) {
            $temp = $this->top_policy_areas[$index - 1];
            $this->top_policy_areas[$index - 1] = $this->top_policy_areas[$index];
            $this->top_policy_areas[$index] = $temp;
            // Update ranks
            foreach ($this->top_policy_areas as $i => &$area) {
                $area['priority_rank'] = $i + 1;
            }
        }
    }

    public function movePolicyAreaDown(int $index)
    {
        if ($index < count($this->top_policy_areas) - 1) {
            $temp = $this->top_policy_areas[$index + 1];
            $this->top_policy_areas[$index + 1] = $this->top_policy_areas[$index];
            $this->top_policy_areas[$index] = $temp;
            // Update ranks
            foreach ($this->top_policy_areas as $i => &$area) {
                $area['priority_rank'] = $i + 1;
            }
        }
    }

    // Generic add/remove for simple arrays
    public function addItem(string $property, string $inputProperty)
    {
        $value = trim($this->$inputProperty);
        if (empty($value)) return;
        
        $array = $this->$property;
        $array[] = $value;
        $this->$property = $array;
        $this->$inputProperty = '';
    }

    public function removeItem(string $property, int $index)
    {
        $array = $this->$property;
        unset($array[$index]);
        $this->$property = array_values($array);
    }

    // Save methods
    protected function saveCurrentSection()
    {
        $profile = MemberProfile::current();
        
        $data = [
            'top_policy_areas' => $this->top_policy_areas,
            'signature_issues' => $this->signature_issues,
            'emerging_interests' => $this->emerging_interests,
            'skip_positioning' => $this->skip_positioning,
            'governing_philosophy' => $this->governing_philosophy,
            'philosophy_description' => $this->philosophy_description,
            'non_negotiables' => $this->non_negotiables,
            'bipartisan_openings' => $this->bipartisan_openings,
            'key_demographics' => $this->key_demographics,
            'economic_priorities' => $this->economic_priorities,
            'constituent_concerns' => $this->constituent_concerns,
            'professional_background' => $this->professional_background,
            'formative_experiences' => $this->formative_experiences,
            'personal_connections' => $this->personal_connections,
            'preferred_tone' => $this->preferred_tone,
            'key_phrases' => $this->key_phrases,
            'topics_to_emphasize' => $this->topics_to_emphasize,
            'topics_to_avoid' => $this->topics_to_avoid,
            'term_goals' => $this->term_goals,
            'long_term_vision' => $this->long_term_vision,
            'legacy_items' => $this->legacy_items,
            'use_in_prompts' => $this->use_in_prompts,
            'ai_context_notes' => $this->ai_context_notes,
            'last_updated_by' => Auth::id(),
        ];
        
        // State-specific fields
        if ($this->governmentLevel === 'state') {
            $data['session_type'] = $this->session_type ?: null;
            $data['other_occupation'] = $this->other_occupation ?: null;
            $data['state_federal_issues'] = $this->state_federal_issues ?: null;
        }
        
        // Local-specific fields
        if ($this->governmentLevel === 'local') {
            $data['local_role_type'] = $this->local_role_type ?: null;
            $data['governance_structure'] = $this->governance_structure ?: null;
            $data['admin_relationship'] = $this->admin_relationship ?: null;
            $data['boards_commissions'] = $this->boards_commissions ?: null;
        }
        
        $profile->update($data);
    }

    public function saveAndContinue()
    {
        $this->saveCurrentSection();
        session()->flash('message', 'Progress saved!');
        
        if ($this->currentSection < $this->totalSections) {
            $this->currentSection++;
        }
    }

    public function completeForm()
    {
        $this->saveCurrentSection();
        
        $profile = MemberProfile::current();
        $profile->update(['last_reviewed_at' => now()]);

        session()->flash('message', 'Member priorities saved successfully!');
        return redirect()->route('member.dashboard');
    }

    public function getSectionTitleProperty(): string
    {
        return match($this->currentSection) {
            1 => 'Policy Priorities',
            2 => 'Political Positioning',
            3 => $this->governmentLevel === 'local' 
                ? 'Community & Constituent Focus' 
                : 'District & Constituent Focus',
            4 => 'Personal Background',
            5 => 'Communication Style',
            6 => 'Goals & AI Settings',
            default => 'Member Priorities',
        };
    }

    public function getSectionDescriptionProperty(): string
    {
        $memberTerm = $this->getMemberTerm();
        
        return match($this->currentSection) {
            1 => "What policy areas matter most to the {$memberTerm}? These help prioritize briefings and suggestions.",
            2 => "How does the {$memberTerm} approach governance? This helps tailor recommendations and identify opportunities.",
            3 => $this->governmentLevel === 'local' 
                ? 'Who are the key community groups and what matters to them? This informs meeting prep and outreach.'
                : 'Who are the key groups in the district and what matters to them? This informs meeting prep and outreach.',
            4 => "What experiences shape the {$memberTerm}'s perspective? This provides context for AI-generated content.",
            5 => "How does the {$memberTerm} prefer to communicate? This guides drafting and talking points.",
            6 => "What are the {$memberTerm}'s goals, and how should we use this information?",
            default => '',
        };
    }

    /**
     * Get the appropriate term for the official based on government level
     */
    public function getMemberTerm(): string
    {
        return match($this->governmentLevel) {
            'local' => 'official',
            'state' => 'legislator',
            default => 'Member',
        };
    }

    /**
     * Get level-specific placeholder text
     */
    public function getPlaceholder(string $field): string
    {
        $placeholders = [
            'signature_issues' => match($this->governmentLevel) {
                'state' => 'e.g., Rural broadband access, Veterans services, Workforce training',
                'local' => 'e.g., Road maintenance, Parks improvements, Streamlining permits',
                default => 'e.g., Veterans mental health, Port infrastructure',
            },
            'emerging_interests' => match($this->governmentLevel) {
                'state' => 'e.g., Telehealth policy, Cybersecurity, Workforce credentials',
                'local' => 'e.g., Electric vehicle infrastructure, Emergency preparedness, Civic technology',
                default => 'e.g., AI regulation, Housing affordability',
            },
            'non_negotiables' => match($this->governmentLevel) {
                'state' => 'e.g., Protecting the state pension system, No cuts to veterans programs',
                'local' => 'e.g., Maintaining fire department response times, Preserving park land',
                default => 'e.g., No cuts to veterans benefits',
            },
            'bipartisan_openings' => match($this->governmentLevel) {
                'state' => 'e.g., Pension reform, Workforce training, Rural healthcare',
                'local' => 'e.g., Infrastructure bonds, Public safety initiatives, Parks funding',
                default => 'e.g., Infrastructure, Veterans affairs',
            },
            'key_demographics' => match($this->governmentLevel) {
                'state' => 'e.g., Teachers, Farmers, Healthcare workers, Retirees',
                'local' => 'e.g., Neighborhood associations, Local business owners, Renters, Senior residents',
                default => 'e.g., Working-class families, Military veterans, Small business owners',
            },
            'economic_priorities' => match($this->governmentLevel) {
                'state' => 'e.g., Agriculture, Healthcare systems, Higher education, Tourism',
                'local' => 'e.g., Downtown retail, Restaurant/hospitality sector, Small business retention',
                default => 'e.g., Port economy, Tech sector jobs, Manufacturing',
            },
            'constituent_concerns' => match($this->governmentLevel) {
                'state' => 'e.g., Road conditions, School funding, Cost of living, Traffic',
                'local' => 'e.g., Road conditions, Permit wait times, Service request response times, Parking',
                default => 'e.g., Healthcare costs, Housing affordability, Traffic congestion',
            },
            'professional_background' => match($this->governmentLevel) {
                'state' => "e.g., 'Public school teacher for 15 years, then school board member. Small business owner...'",
                'local' => "e.g., 'Ran a local restaurant for 20 years. Active in the chamber of commerce...'",
                default => "e.g., 'Four tours in Iraq as a Marine Corps officer (2001-2008)...'",
            },
            'formative_experiences' => match($this->governmentLevel) {
                'state' => 'e.g., Parents lost farm in the 80s crisis, First in family to attend college',
                'local' => 'e.g., Grew up in this neighborhood, Saw the mill close and town struggle',
                default => 'e.g., Lost friends to combat, Grew up in working-class family',
            },
            'personal_connections' => match($this->governmentLevel) {
                'state' => 'e.g., Child in public schools → education funding, Parent needed Medicaid → healthcare access',
                'local' => 'e.g., Flooded basement twice → stormwater infrastructure, Kids walk to school → traffic safety',
                default => 'e.g., Family member with disability → ADA advocacy',
            },
            'key_phrases' => match($this->governmentLevel) {
                'state' => "e.g., 'Putting our state first', 'Common-sense solutions', 'Taxpayer dollars'",
                'local' => "e.g., 'This is your money', 'Neighborhood-first', 'Results, not rhetoric'",
                default => "e.g., 'We need to cut through the noise', 'Working families'",
            },
            'topics_emphasize' => match($this->governmentLevel) {
                'state' => 'e.g., Work in the district, Local schools, State pride',
                'local' => 'e.g., Neighborhood roots, Local business relationships, Community involvement',
                default => 'e.g., Military service, Local business success stories',
            },
            'term_goals' => match($this->governmentLevel) {
                'state' => 'e.g., Pass broadband expansion bill, Complete pension system review',
                'local' => 'e.g., Complete infrastructure assessment, Improve permit turnaround time',
                default => 'e.g., Pass veterans mental health bill, Secure port funding',
            },
            'long_term_vision' => match($this->governmentLevel) {
                'state' => "e.g., 'Become the leading voice on education in the state, potentially pursue statewide office...'",
                'local' => "e.g., 'Help this city become a model for sustainable growth, possibly run for mayor...'",
                default => "e.g., 'Build a legacy of bipartisan leadership on national security...'",
            },
            'legacy_items' => match($this->governmentLevel) {
                'state' => 'e.g., Modernizing the state\'s workforce training system',
                'local' => 'e.g., Modernized city services, Improved infrastructure, Responsive local government',
                default => 'e.g., Reforming veterans mental health care',
            },
        ];
        
        return $placeholders[$field] ?? '';
    }

    /**
     * Get level-specific labels
     */
    public function getLabel(string $field): string
    {
        $labels = [
            'bipartisan_label' => match($this->governmentLevel) {
                'state' => 'Open to Bipartisan/Cross-Aisle Work On',
                'local' => 'Open to Coalition-Building On',
                default => 'Open to Bipartisan Work On',
            },
            'bipartisan_helper' => match($this->governmentLevel) {
                'state' => 'Areas where the legislator seeks collaboration across party lines',
                'local' => 'Areas where the official seeks broad support across the council/board',
                default => 'Areas where the Member actively seeks cross-aisle collaboration',
            },
            'demographics_label' => match($this->governmentLevel) {
                'local' => 'Key Community Groups',
                default => 'Key Constituency Groups',
            },
            'demographics_helper' => match($this->governmentLevel) {
                'local' => 'Important groups in your ward/district/city',
                default => 'Important groups in the district',
            },
            'economic_label' => match($this->governmentLevel) {
                'local' => 'Local Economic Priorities',
                default => 'District Economic Priorities',
            },
            'term_goals_helper' => match($this->governmentLevel) {
                'state' => 'Key priorities for this session/term',
                'local' => 'Key priorities for this term',
                default => 'Key priorities for this Congress',
            },
        ];
        
        return $labels[$field] ?? '';
    }

    public function skipPositioningSection()
    {
        $this->skip_positioning = true;
        $this->nextSection();
    }

    public function render()
    {
        return view('livewire.setup.member-priorities-form', [
            'sectionTitle' => $this->sectionTitle,
            'sectionDescription' => $this->sectionDescription,
            'policyAreaOptions' => $this->policyAreaOptions,
            'philosophyOptions' => $this->philosophyOptions,
            'memberTerm' => $this->getMemberTerm(),
        ]);
    }
}

