<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    protected $fillable = [
        'member_name',
        'member_first_name',
        'member_last_name',
        'member_title',
        'member_party',
        'member_state',
        'member_district',
        'member_bioguide_id',
        'member_photo_url',
        'government_level',
        'chamber',
        'first_elected',
        'official_website',
        'social_media',
        'dc_office',
        'district_offices',
        'district_cities',
        'district_counties',
        'news_sources',
        'legislative_activity',
        'setup_completed_at',
        'top_policy_areas',
        'signature_issues',
        'emerging_interests',
        'governing_philosophy',
        'philosophy_description',
        'party_differentiators',
        'non_negotiables',
        'bipartisan_openings',
        'key_demographics',
        'economic_priorities',
        'geographic_focuses',
        'constituent_concerns',
        'professional_background',
        'formative_experiences',
        'personal_connections',
        'preferred_tone',
        'key_phrases',
        'talking_points_style',
        'topics_to_emphasize',
        'topics_to_avoid',
        'term_goals',
        'long_term_vision',
        'legacy_items',
        'committee_priorities',
        'ai_context_notes',
        'use_in_prompts',
        'skip_positioning',
        // State Legislature specific
        'session_type',
        'other_occupation',
        'state_federal_issues',
        // Local Government specific
        'local_role_type',
        'governance_structure',
        'admin_relationship',
        'boards_commissions',
        // Metadata
        'last_updated_by',
        'last_reviewed_at',
    ];

    protected $casts = [
        'social_media' => 'array',
        'dc_office' => 'array',
        'district_offices' => 'array',
        'district_cities' => 'array',
        'district_counties' => 'array',
        'news_sources' => 'array',
        'legislative_activity' => 'array',
        'setup_completed_at' => 'datetime',
        'top_policy_areas' => 'array',
        'signature_issues' => 'array',
        'emerging_interests' => 'array',
        'party_differentiators' => 'array',
        'non_negotiables' => 'array',
        'bipartisan_openings' => 'array',
        'key_demographics' => 'array',
        'economic_priorities' => 'array',
        'geographic_focuses' => 'array',
        'constituent_concerns' => 'array',
        'formative_experiences' => 'array',
        'personal_connections' => 'array',
        'key_phrases' => 'array',
        'talking_points_style' => 'array',
        'topics_to_emphasize' => 'array',
        'topics_to_avoid' => 'array',
        'term_goals' => 'array',
        'legacy_items' => 'array',
        'committee_priorities' => 'array',
        'ai_context_notes' => 'array',
        'use_in_prompts' => 'boolean',
        'skip_positioning' => 'boolean',
        'state_federal_issues' => 'array',
        'boards_commissions' => 'array',
        'last_reviewed_at' => 'datetime',
    ];

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * Get or create the singleton profile
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'member_name' => '',
            'top_policy_areas' => [],
            'signature_issues' => [],
            'use_in_prompts' => true,
        ]);
    }

    public function hasConfiguredMember(): bool
    {
        return trim((string) $this->member_name) !== '';
    }

    public function toOfficeConfigOverrides(): array
    {
        if (!$this->hasConfiguredMember()) {
            return [];
        }

        return array_filter(
            [
                'member_name' => $this->member_name,
                'member_first_name' => $this->member_first_name ?: '',
                'member_last_name' => $this->member_last_name ?: '',
                'member_title' => $this->member_title ?: 'Representative',
                'member_party' => $this->member_party ?: '',
                'member_state' => $this->member_state ?: '',
                'member_district' => $this->member_district ?: '',
                'member_bioguide_id' => $this->member_bioguide_id ?: '',
                'member_photo_url' => $this->member_photo_url,
                'government_level' => $this->government_level ?: 'federal',
                'chamber' => $this->chamber ?: 'House',
                'first_elected' => $this->first_elected,
                'official_website' => $this->official_website ?: '',
                'social_media' => $this->social_media ?: [],
                'dc_office' => $this->dc_office ?: [],
                'district_offices' => $this->district_offices ?: [],
                'district_cities' => $this->district_cities ?: [],
                'district_counties' => $this->district_counties ?: [],
                'news_sources' => $this->news_sources ?: [],
                'legislative_activity' => $this->legislative_activity ?: [],
                'setup_completed_at' => optional($this->setup_completed_at)?->toDateTimeString(),
                'setup_completed' => $this->setup_completed_at !== null,
            ],
            static fn ($value, string $key) => !($value === null && $key !== 'setup_completed'),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * Get a summary for AI context
     */
    public function getAiContextSummary(): string
    {
        if (!$this->use_in_prompts) {
            return '';
        }

        $parts = [];

        // Policy priorities
        if (!empty($this->top_policy_areas)) {
            $areas = collect($this->top_policy_areas)
                ->sortBy('priority_rank')
                ->pluck('area')
                ->take(5)
                ->implode(', ');
            $parts[] = "Top policy priorities: {$areas}";
        }

        // Philosophy
        if ($this->governing_philosophy) {
            $parts[] = "Governing approach: {$this->governing_philosophy}";
        }

        // Signature issues
        if (!empty($this->signature_issues)) {
            $issues = implode(', ', array_slice($this->signature_issues, 0, 3));
            $parts[] = "Signature issues: {$issues}";
        }

        // Non-negotiables
        if (!empty($this->non_negotiables)) {
            $redLines = implode(', ', array_slice($this->non_negotiables, 0, 3));
            $parts[] = "Non-negotiable positions: {$redLines}";
        }

        // Communication tone
        if ($this->preferred_tone) {
            $parts[] = "Preferred communication tone: {$this->preferred_tone}";
        }

        // State-specific context
        $governmentLevel = config('office.government_level', 'federal');
        
        if ($governmentLevel === 'state') {
            if ($this->session_type) {
                $sessionLabels = [
                    'full_time' => 'Full-time legislature',
                    'long_session' => 'Part-time with long session',
                    'short_session' => 'Part-time with short session',
                    'biennial' => 'Biennial sessions',
                ];
                $parts[] = "Legislative session: " . ($sessionLabels[$this->session_type] ?? $this->session_type);
            }
            
            if ($this->other_occupation) {
                $parts[] = "Primary occupation: {$this->other_occupation}";
            }
            
            if (!empty($this->state_federal_issues)) {
                $federalIssues = implode(', ', array_slice($this->state_federal_issues, 0, 3));
                $parts[] = "State-federal coordination areas: {$federalIssues}";
            }
        }
        
        // Local-specific context
        if ($governmentLevel === 'local') {
            if ($this->local_role_type) {
                $roleLabels = [
                    'council_ward' => 'City Council Member (ward-based)',
                    'council_at_large' => 'City Council Member (at-large)',
                    'county_commissioner' => 'County Commissioner',
                    'mayor_council' => 'Mayor (with council)',
                    'mayor_strong' => 'Mayor (strong mayor system)',
                    'township_trustee' => 'Township Trustee',
                    'school_board' => 'School Board Member',
                ];
                $parts[] = "Role: " . ($roleLabels[$this->local_role_type] ?? $this->local_role_type);
            }
            
            if ($this->governance_structure) {
                $structureLabels = [
                    'council_manager' => 'Council-Manager government',
                    'strong_mayor' => 'Strong Mayor system',
                    'commission' => 'Commission government',
                    'town_meeting' => 'Town Meeting government',
                ];
                $parts[] = "Structure: " . ($structureLabels[$this->governance_structure] ?? $this->governance_structure);
            }
            
            if (!empty($this->boards_commissions)) {
                $boards = implode(', ', array_slice($this->boards_commissions, 0, 3));
                $parts[] = "Serves on: {$boards}";
            }
        }

        // Custom AI notes
        if (!empty($this->ai_context_notes)) {
            $parts[] = "Additional context: " . implode('; ', $this->ai_context_notes);
        }

        return implode("\n", $parts);
    }

    /**
     * Get policy areas as a simple list
     */
    public function getPolicyAreasListAttribute(): array
    {
        return collect($this->top_policy_areas ?? [])
            ->sortBy('priority_rank')
            ->pluck('area')
            ->toArray();
    }
}
