<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BetaRequest extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'role_type',
        'official_name',
        'government_level',
        'government_level_other',
        'state',
        'district',
        'primary_interest',
        'additional_info',
        'status',
        'notes',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'invite_token',
        'invite_expires_at',
        'approved_at',
        'approved_by',
        'declined_at',
        'declined_by',
        'onboarded_at',
        'onboarded_user_id',
    ];

    protected function casts(): array
    {
        return [
            'invite_expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'declined_at' => 'datetime',
            'onboarded_at' => 'datetime',
        ];
    }

    public const ROLE_TYPES = [
        'elected_official' => 'Elected official',
        'staff_member' => 'Staff member',
        'other' => 'Other',
    ];

    public const GOVERNMENT_LEVELS = [
        'us_congress' => 'U.S. Congress',
        'state_legislature' => 'State Legislature',
        'city_municipal' => 'City / Municipal',
        'county' => 'County',
        'other' => 'Other',
    ];

    public const PRIMARY_INTERESTS = [
        'issue_tracking' => 'Issue & policy tracking',
        'meeting_prep' => 'Meeting prep & follow-up',
        'relationship_mgmt' => 'Relationship management',
        'team_coordination' => 'Team coordination',
        'knowledge_mgmt' => 'Knowledge management / institutional memory',
        'media_tracking' => 'Media tracking',
        'all_above' => 'All of the above',
        'exploring' => 'Just curious / exploring options',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'contacted' => 'Contacted',
        'onboarded' => 'Onboarded',
        'declined' => 'Declined',
    ];

    public const US_STATES = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'DC' => 'District of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii',
        'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
        'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine',
        'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
        'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska',
        'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico',
        'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
        'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island',
        'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas',
        'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington',
        'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
        'AS' => 'American Samoa', 'GU' => 'Guam', 'MP' => 'Northern Mariana Islands',
        'PR' => 'Puerto Rico', 'VI' => 'U.S. Virgin Islands',
    ];

    public function getRoleTypeLabelAttribute(): string
    {
        return self::ROLE_TYPES[$this->role_type] ?? $this->role_type;
    }

    public function getGovernmentLevelLabelAttribute(): string
    {
        return self::GOVERNMENT_LEVELS[$this->government_level] ?? $this->government_level;
    }

    public function getPrimaryInterestLabelAttribute(): string
    {
        return self::PRIMARY_INTERESTS[$this->primary_interest] ?? $this->primary_interest;
    }

    public function getStateLabelAttribute(): string
    {
        return self::US_STATES[$this->state] ?? $this->state;
    }

    /**
     * Accessor for template compatibility - maps to full_name
     */
    public function getNameAttribute(): string
    {
        return $this->full_name ?? '';
    }

    /**
     * Accessor for template compatibility - maps to role_type label
     */
    public function getTitleAttribute(): string
    {
        return $this->role_type_label;
    }

    /**
     * Accessor for template compatibility - maps to official_name
     */
    public function getElectedOfficialNameAttribute(): string
    {
        return $this->official_name ?? '';
    }

    /**
     * Accessor for template compatibility - maps to government_level
     */
    public function getLevelAttribute(): string
    {
        return match($this->government_level) {
            'us_congress' => 'federal',
            'state_legislature' => 'state',
            'city_municipal', 'county' => 'local',
            default => 'other',
        };
    }

    /**
     * Accessor for template compatibility - parses primary_interest as array
     */
    public function getInterestsAttribute(): array
    {
        if (!$this->primary_interest) {
            return [];
        }
        
        // If it's already stored as array/json, return labels
        $interest = $this->primary_interest;
        
        return [self::PRIMARY_INTERESTS[$interest] ?? $interest];
    }

    /**
     * Accessor for template compatibility - maps to additional_info
     */
    public function getNotesAttribute(): ?string
    {
        return $this->additional_info;
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function declinedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declined_by');
    }

    public function onboardedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'onboarded_user_id');
    }

    public function inviteIsActive(): bool
    {
        if ($this->status !== 'approved' || !$this->invite_token) {
            return false;
        }

        if ($this->invite_expires_at && $this->invite_expires_at->isPast()) {
            return false;
        }

        return true;
    }
}

