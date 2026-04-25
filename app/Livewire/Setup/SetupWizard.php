<?php

namespace App\Livewire\Setup;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\BioguideApiService;
use App\Services\NewsSourceDetector;
use App\Services\CongressApiService;
use App\Services\MemberKnowledgeService;
use App\Models\MemberDocument;
use Illuminate\Support\Facades\File;

/**
 * Member Hub Setup Wizard
 * 
 * Guided onboarding for configuring Member Hub with automatic
 * data discovery from public APIs.
 */
#[Layout('layouts.app')]
class SetupWizard extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 5;

    // Step 1: Basic Information
    public string $level = 'federal';
    public string $first_name = '';
    public string $last_name = '';
    public string $title = 'Representative';
    public string $party = '';
    public string $state = '';
    public string $district_number = '';
    public string $search_query = '';
    public array $search_results = [];
    public bool $is_searching = false;

    // Step 2: Verified Data
    public ?array $verified_member = null;
    public ?array $district_geography = null;
    public string $dc_address = '';
    public string $dc_phone = '';
    public string $official_website = '';
    public array $district_offices = [];

    // Step 3: News Sources
    public array $suggested_sources = [];
    public array $selected_sources = [];
    public array $social_media = [
        'twitter' => '',
        'facebook' => '',
        'instagram' => '',
        'youtube' => '',
        'linkedin' => '',
        'bluesky' => '',
        'tiktok' => '',
    ];

    // Step 4: Import Options
    public bool $import_biography = true;
    public bool $import_legislation = true;
    public bool $import_statements = true;
    public bool $import_campaign = false;
    public int $import_years = 2;
    public bool $is_importing = false;
    public array $import_results = [];

    // State/Local: Legislative activity URL for scraping
    public string $legislative_url = '';
    public string $legislative_url_type = ''; // 'state_legislature', 'city_council', 'custom'

    // Step 5: Summary
    public bool $setup_complete = false;

    protected BioguideApiService $bioguideApi;
    protected NewsSourceDetector $newsDetector;
    protected CongressApiService $congressApi;

    public function boot(
        BioguideApiService $bioguideApi,
        NewsSourceDetector $newsDetector,
        CongressApiService $congressApi
    ) {
        $this->bioguideApi = $bioguideApi;
        $this->newsDetector = $newsDetector;
        $this->congressApi = $congressApi;
    }

    public function mount()
    {
        // Pre-fill with any existing config
        $this->first_name = explode(' ', config('office.member_name', ''))[0] ?? '';
        $this->last_name = explode(' ', config('office.member_name', ''))[1] ?? '';
        $this->state = config('office.member_state', '');
        $this->district_number = config('office.member_district', '');
        $this->party = config('office.member_party', '');
    }

    /**
     * Triggered when search_query is updated (live search).
     */
    public function updatedSearchQuery()
    {
        $this->searchMember();
    }

    /**
     * Search for a member by name.
     */
    public function searchMember()
    {
        $query = trim($this->search_query);

        if (strlen($query) < 2) {
            $this->search_results = [];
            return;
        }

        $this->is_searching = true;
        $this->search_results = $this->bioguideApi->searchByName($query);
        $this->is_searching = false;
    }

    /**
     * Select a member from search results.
     */
    public function selectMember(int $index)
    {
        if (!isset($this->search_results[$index])) {
            return;
        }

        $member = $this->search_results[$index];
        $this->first_name = $member['first_name'] ?? '';
        $this->last_name = $member['last_name'] ?? '';
        $this->party = $member['party'] ?? '';

        // Convert state name to abbreviation if needed
        $stateValue = $member['state'] ?? '';
        if (strlen($stateValue) > 2) {
            $stateValue = $this->getStateAbbreviation($stateValue);
        }
        $this->state = $stateValue;

        $this->district_number = (string) ($member['district'] ?? '');
        $this->title = ($member['chamber'] ?? 'House') === 'Senate' ? 'Senator' : 'Representative';

        // Clear search
        $this->search_results = [];
        $this->search_query = '';
    }

    /**
     * Convert state name to abbreviation.
     */
    protected function getStateAbbreviation(string $name): string
    {
        $stateMap = array_flip($this->states);
        return $stateMap[$name] ?? $name;
    }

    /**
     * Move to next step.
     */
    public function nextStep()
    {
        // Validate current step
        if (!$this->validateCurrentStep()) {
            return;
        }

        // Process step transitions
        if ($this->currentStep === 1) {
            $this->fetchMemberDetails();
        }

        if ($this->currentStep === 2) {
            $this->loadNewsSources();
        }

        if ($this->currentStep === 4) {
            $this->runImport();
        }

        $this->currentStep = min($this->currentStep + 1, $this->totalSteps);
    }

    /**
     * Move to previous step.
     */
    public function previousStep()
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    /**
     * Validate current step.
     */
    protected function validateCurrentStep(): bool
    {
        $rules = match ($this->currentStep) {
            1 => [
                'first_name' => 'required|string|min:2',
                'last_name' => 'required|string|min:2',
                'state' => 'required|string|size:2',
            ],
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            default => [],
        };

        if (!empty($rules)) {
            $this->validate($rules);
        }

        return true;
    }

    /**
     * Fetch member details from APIs.
     */
    protected function fetchMemberDetails()
    {
        // Only fetch from Congress.gov for federal officials
        if ($this->level !== 'federal') {
            return;
        }

        // Search by name and state/district
        $results = $this->bioguideApi->searchByName($this->first_name . ' ' . $this->last_name);

        // Find the matching member
        foreach ($results as $result) {
            if (
                strtoupper($result['state']) === strtoupper($this->state) &&
                (empty($this->district_number) || (string) $result['district'] === $this->district_number)
            ) {
                // Get full details
                $this->verified_member = $this->bioguideApi->getMemberByBioguideId($result['bioguide_id']);
                break;
            }
        }

        // If we found the member, get district geography
        if ($this->verified_member) {
            $this->district_geography = $this->bioguideApi->getDistrictGeography(
                $this->state,
                $this->district_number
            );

            // Pre-fill office info
            $this->dc_address = $this->verified_member['office_address'] ?? '';
            $this->dc_phone = $this->verified_member['office_phone'] ?? '';
            $this->official_website = $this->verified_member['official_website'] ?? '';
        }
    }

    /**
     * Load suggested news sources.
     */
    protected function loadNewsSources()
    {
        $cities = $this->district_geography['cities'] ?? [];

        $this->suggested_sources = $this->newsDetector->suggestSources(
            $this->level,
            $this->state,
            $this->district_number,
            $cities
        );

        // Auto-select suggested sources
        foreach ($this->suggested_sources as $index => $source) {
            if ($source['suggested'] ?? false) {
                $this->selected_sources[] = $index;
            }
        }
    }

    /**
     * Toggle source selection.
     */
    public function toggleSource(int $index)
    {
        if (in_array($index, $this->selected_sources)) {
            $this->selected_sources = array_values(array_diff($this->selected_sources, [$index]));
        } else {
            $this->selected_sources[] = $index;
        }
    }

    /**
     * Run data import.
     */
    protected function runImport()
    {
        $this->is_importing = true;
        $this->import_results = [];

        // For state/local officials, skip Congress.gov import
        if ($this->level !== 'federal') {
            $this->import_results['skipped'] = 'Congress.gov import not available for state/local officials';
            $this->is_importing = false;
            return;
        }

        $bioguideId = $this->verified_member['bioguide_id'] ?? null;

        if (!$bioguideId) {
            $this->import_results['error'] = 'No Bioguide ID found';
            $this->is_importing = false;
            return;
        }

        // Import biography
        if ($this->import_biography) {
            $this->importBiography();
        }

        // Import legislation
        if ($this->import_legislation) {
            $result = $this->congressApi->syncMemberBills();
            $this->import_results['legislation'] = [
                'status' => 'success',
                'sponsored' => $result['sponsored'] ?? 0,
                'cosponsored' => $result['cosponsored'] ?? 0,
            ];
        }

        $this->is_importing = false;
    }

    /**
     * Import biography document.
     */
    protected function importBiography()
    {
        if (!$this->verified_member) {
            return;
        }

        $fullName = $this->verified_member['full_name'] ?? $this->first_name . ' ' . $this->last_name;
        $party = $this->verified_member['party'] ?? $this->party;
        $state = $this->verified_member['state_name'] ?? $this->state;
        $district = $this->district_number;
        $firstElected = $this->verified_member['first_elected'] ?? 'N/A';

        $biographyContent = "# {$fullName}\n\n";
        $biographyContent .= "**{$this->title}** representing {$state}'s {$district}th Congressional District\n\n";
        $biographyContent .= "**Party:** {$party}\n";
        $biographyContent .= "**First Elected:** {$firstElected}\n\n";

        if ($this->verified_member['birth_year']) {
            $biographyContent .= "**Birth Year:** {$this->verified_member['birth_year']}\n\n";
        }

        $biographyContent .= "## Official Information\n\n";
        $biographyContent .= "- **Official Website:** {$this->official_website}\n";
        $biographyContent .= "- **Washington, DC Office:** {$this->dc_address}\n";
        $biographyContent .= "- **Phone:** {$this->dc_phone}\n";

        // Create document
        $document = MemberDocument::updateOrCreate(
            ['document_type' => 'biography', 'title' => 'Official Congressional Biography'],
            [
                'description' => 'Official biographical information from Congress.gov',
                'content' => $biographyContent,
                'document_date' => now(),
                'source' => 'Congress.gov API',
                'is_public' => true,
                'metadata' => $this->verified_member,
            ]
        );

        $this->import_results['biography'] = [
            'status' => 'success',
            'document_id' => $document->id,
        ];
    }

    /**
     * Complete setup and save configuration.
     */
    public function completeSetup()
    {
        $this->saveConfiguration();
        $this->setup_complete = true;

        return redirect()->route('member.dashboard');
    }

    /**
     * Save configuration to config file and .env.
     */
    protected function saveConfiguration()
    {
        $bioguideId = $this->verified_member['bioguide_id'] ?? '';
        $fullName = $this->first_name . ' ' . $this->last_name;

        // Build selected news sources list
        $selectedNewsSources = [];
        foreach ($this->selected_sources as $index) {
            if (isset($this->suggested_sources[$index])) {
                $selectedNewsSources[] = $this->suggested_sources[$index]['name'];
            }
        }

        // Create the config array
        $config = [
            'member_name' => $fullName,
            'member_first_name' => $this->first_name,
            'member_last_name' => $this->last_name,
            'member_title' => $this->title,
            'member_party' => $this->party,
            'member_state' => $this->state,
            'member_district' => $this->district_number,
            'member_bioguide_id' => $bioguideId,
            'member_photo_url' => $this->verified_member['photo_url'] ?? null,
            'government_level' => $this->level,
            'current_congress' => config('office.current_congress', 119),
            'chamber' => $this->title === 'Senator' ? 'Senate' : 'House',
            'first_elected' => $this->verified_member['first_elected'] ?? null,
            'dc_office' => [
                'name' => 'Washington, DC Office',
                'address' => $this->dc_address,
                'city' => 'Washington',
                'state' => 'DC',
                'zip' => $this->verified_member['office_zip'] ?? '20515',
                'phone' => $this->dc_phone,
                'timezone' => 'America/New_York',
                'lat' => 38.8899,
                'lng' => -77.0091,
            ],
            'district_offices' => $this->district_offices,
            'district_cities' => $this->district_geography['cities'] ?? [],
            'district_counties' => $this->district_geography['counties'] ?? [],
            'official_website' => $this->official_website,
            'social_media' => array_filter($this->social_media),
            'news_sources' => $selectedNewsSources,
            'congress_api' => [
                'key' => env('CONGRESS_API_KEY'),
                'base_url' => 'https://api.congress.gov/v3',
                'rate_limit' => 5000,
            ],
            'legislative_activity' => [
                'url' => $this->legislative_url,
                'type' => $this->legislative_url_type,
                'last_scraped' => null,
            ],
            'setup_completed_at' => now()->toDateTimeString(),
            'setup_version' => '1.0.0',
        ];

        // Write to config/office.php
        $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        File::put(config_path('office.php'), $configContent);

        // Clear config cache
        \Artisan::call('config:clear');
    }

    /**
     * Get step title.
     */
    public function getStepTitleProperty(): string
    {
        return match ($this->currentStep) {
            1 => 'Who is this for?',
            2 => 'Verify Information',
            3 => 'Configure News Sources',
            4 => $this->level === 'federal' ? 'Import Public Records' : 'Additional Information',
            5 => 'Review & Launch',
            default => 'Setup',
        };
    }

    /**
     * Get US states list.
     */
    public function getStatesProperty(): array
    {
        return [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];
    }

    public function render()
    {
        return view('livewire.setup.setup-wizard', [
            'stepTitle' => $this->stepTitle,
            'states' => $this->states,
        ]);
    }
}
