<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service to fetch member information from the Biographical Directory of Congress
 * and other public sources.
 */
class BioguideApiService
{
    protected $congressApiKey;

    public function __construct()
    {
        $this->congressApiKey = config('office.congress_api.key');
    }

    /**
     * Search for a member by name using Congress.gov API.
     * Note: The API doesn't support name search, so we fetch current members
     * and filter client-side.
     */
    public function searchByName(string $name): array
    {
        if (empty($this->congressApiKey)) {
            return [];
        }

        $nameLower = strtolower(trim($name));
        if (strlen($nameLower) < 2) {
            return [];
        }

        try {
            // Fetch current members (cached for 1 hour to reduce API calls)
            $cacheKey = 'congress_current_members_all';
            $apiKey = $this->congressApiKey; // Capture for closure

            $members = Cache::remember($cacheKey, 3600, function () use ($apiKey) {
                $allMembers = [];
                $offset = 0;
                $limit = 250;

                // Fetch all pages (Congress has 535+ members)
                do {
                    $response = Http::timeout(30)->get('https://api.congress.gov/v3/member', [
                        'api_key' => $apiKey,
                        'format' => 'json',
                        'limit' => $limit,
                        'offset' => $offset,
                        'currentMember' => 'true',
                    ]);

                    if (!$response->successful()) {
                        break;
                    }

                    $data = $response->json();
                    $pageMembers = $data['members'] ?? [];
                    $allMembers = array_merge($allMembers, $pageMembers);

                    // Check if there are more pages
                    $offset += $limit;
                    $hasMore = count($pageMembers) >= $limit;

                } while ($hasMore && $offset < 600); // Safety limit

                return $allMembers;
            });

            // Prepare search terms (split by spaces to match any word)
            $searchTerms = array_filter(explode(' ', $nameLower));

            // Filter by name - match ALL search terms in any field
            $filtered = array_filter($members, function ($member) use ($searchTerms) {
                // Build searchable text from all name fields
                $searchableText = strtolower(
                    ($member['name'] ?? '') . ' ' .
                    ($member['firstName'] ?? '') . ' ' .
                    ($member['lastName'] ?? '') . ' ' .
                    ($member['directOrderName'] ?? '')
                );

                // Every search term must match somewhere
                foreach ($searchTerms as $term) {
                    if (!str_contains($searchableText, $term)) {
                        return false;
                    }
                }
                return true;
            });

            // Sort by relevance (prioritize exact matches)
            usort($filtered, function ($a, $b) use ($searchTerms, $nameLower) {
                $aFirstName = strtolower($a['firstName'] ?? '');
                $aLastName = strtolower($a['lastName'] ?? '');
                $bFirstName = strtolower($b['firstName'] ?? '');
                $bLastName = strtolower($b['lastName'] ?? '');

                $aName = $aLastName . ' ' . $aFirstName;
                $bName = $bLastName . ' ' . $bFirstName;

                // Exact full match scores highest (either "First Last" or "Last First")
                $aExact = ($aName === $nameLower || ($aFirstName . ' ' . $aLastName) === $nameLower) ? 1 : 0;
                $bExact = ($bName === $nameLower || ($bFirstName . ' ' . $bLastName) === $nameLower) ? 1 : 0;
                if ($aExact !== $bExact)
                    return $bExact - $aExact;

                // Last name exact match
                $aLastMatch = ($aLastName === ($searchTerms[0] ?? '')) ? 1 : 0;
                $bLastMatch = ($bLastName === ($searchTerms[0] ?? '')) ? 1 : 0;
                if ($aLastMatch !== $bLastMatch)
                    return $bLastMatch - $aLastMatch;

                return strcmp($aLastName, $bLastName);
            });

            // Return top 10 matches
            return array_map(
                fn($m) => $this->formatMemberSearchResult($m),
                array_slice($filtered, 0, 10)
            );
        } catch (\Exception $e) {
            Log::error("BioguideApiService: Error searching by name: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Get detailed information about a member by Bioguide ID.
     */
    public function getMemberByBioguideId(string $bioguideId): ?array
    {
        $cacheKey = "bioguide_member_{$bioguideId}";

        return Cache::remember($cacheKey, 3600, function () use ($bioguideId) {
            if (empty($this->congressApiKey)) {
                return null;
            }

            try {
                $response = Http::timeout(15)->get("https://api.congress.gov/v3/member/{$bioguideId}", [
                    'api_key' => $this->congressApiKey,
                    'format' => 'json',
                ]);

                if ($response->successful()) {
                    return $this->formatMemberDetails($response->json()['member'] ?? []);
                }
            } catch (\Exception $e) {
                Log::error("BioguideApiService: Error fetching member details: " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Search for a member by state and district.
     */
    public function searchByStateAndDistrict(string $state, string $district): ?array
    {
        if (empty($this->congressApiKey)) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get('https://api.congress.gov/v3/member', [
                'api_key' => $this->congressApiKey,
                'format' => 'json',
                'limit' => 50,
                'currentMember' => 'true',
            ]);

            if ($response->successful()) {
                $members = $response->json()['members'] ?? [];

                // Filter by state and district
                foreach ($members as $member) {
                    if (isset($member['state']) && strtoupper($member['state']) === strtoupper($state)) {
                        if (isset($member['district']) && (string) $member['district'] === (string) $district) {
                            return $this->getMemberByBioguideId($member['bioguideId']);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("BioguideApiService: Error searching by state/district: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get district geography data.
     * For now, returns known data for common districts; could integrate with Census API.
     */
    public function getDistrictGeography(string $state, string $district): array
    {
        // Known district data (expand as needed or integrate with Census API)
        $districtData = [
            'MA-6' => [
                'counties' => ['Essex', 'Middlesex'],
                'cities' => ['Salem', 'Lynn', 'Beverly', 'Peabody', 'Revere', 'Marblehead', 'Danvers', 'Saugus', 'Swampscott', 'Nahant'],
                'population' => 770000,
                'area_sq_miles' => 120,
            ],
            'MA-7' => [
                'counties' => ['Suffolk', 'Middlesex'],
                'cities' => ['Boston', 'Cambridge', 'Somerville', 'Chelsea', 'Everett'],
                'population' => 780000,
                'area_sq_miles' => 60,
            ],
            // Add more as needed...
        ];

        $key = strtoupper($state) . '-' . $district;

        return $districtData[$key] ?? [
            'counties' => [],
            'cities' => [],
            'population' => null,
            'area_sq_miles' => null,
        ];
    }

    /**
     * Format member search result.
     */
    protected function formatMemberSearchResult(array $member): array
    {
        // Parse first/last name from "Last, First" format if not provided
        $firstName = $member['firstName'] ?? '';
        $lastName = $member['lastName'] ?? '';

        if (empty($firstName) && empty($lastName) && !empty($member['name'])) {
            $parts = explode(', ', $member['name'], 2);
            $lastName = trim($parts[0] ?? '');
            $firstName = trim($parts[1] ?? '');
        }

        return [
            'bioguide_id' => $member['bioguideId'] ?? '',
            'name' => $member['name'] ?? '',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'party' => $member['partyName'] ?? '',
            'state' => $member['state'] ?? '',
            'district' => $member['district'] ?? null,
            'chamber' => $member['terms'][0]['chamber'] ?? 'House',
            'is_current' => true,
        ];
    }

    /**
     * Format full member details.
     */
    protected function formatMemberDetails(array $member): array
    {
        $currentTerm = $member['terms'][0] ?? [];

        return [
            'bioguide_id' => $member['bioguideId'] ?? '',
            'first_name' => $member['firstName'] ?? '',
            'last_name' => $member['lastName'] ?? '',
            'full_name' => ($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? ''),
            'direct_order_name' => $member['directOrderName'] ?? '',
            'inverted_order_name' => $member['invertedOrderName'] ?? '',
            'honorific_prefix' => $member['honorificName'] ?? 'Representative',
            'party' => $member['partyHistory'][0]['partyName'] ?? '',
            'party_abbr' => $member['partyHistory'][0]['partyAbbreviation'] ?? '',
            'state' => $member['state'] ?? '',
            'state_name' => $this->getStateName($member['state'] ?? ''),
            'district' => $currentTerm['district'] ?? null,
            'chamber' => $currentTerm['chamber'] ?? 'House of Representatives',
            'birth_year' => $member['birthYear'] ?? null,
            'office_address' => $member['addressInformation']['officeAddress'] ?? '',
            'office_city' => $member['addressInformation']['city'] ?? 'Washington',
            'office_state' => $member['addressInformation']['officeState'] ?? 'DC',
            'office_zip' => $member['addressInformation']['zip'] ?? '',
            'office_phone' => $member['addressInformation']['phoneNumber'] ?? '',
            'official_website' => $member['officialWebsiteUrl'] ?? '',
            'contact_form' => $member['contactFormUrl'] ?? '',
            'current_member' => $member['currentMember'] ?? true,
            'terms_count' => count($member['terms'] ?? []),
            'first_elected' => $this->getFirstElectedYear($member['terms'] ?? []),
            'photo_url' => $member['depiction']['imageUrl'] ?? null,
            'sponsored_legislation_url' => $member['sponsoredLegislation']['url'] ?? null,
            'cosponsored_legislation_url' => $member['cosponsoredLegislation']['url'] ?? null,
        ];
    }

    /**
     * Get first elected year from terms.
     */
    protected function getFirstElectedYear(array $terms): ?int
    {
        if (empty($terms)) {
            return null;
        }

        $oldest = end($terms);
        return isset($oldest['startYear']) ? (int) $oldest['startYear'] : null;
    }

    /**
     * Get state name from abbreviation.
     */
    protected function getStateName(string $abbr): string
    {
        $states = [
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
            'DC' => 'District of Columbia',
            'AS' => 'American Samoa',
            'GU' => 'Guam',
            'MP' => 'Northern Mariana Islands',
            'PR' => 'Puerto Rico',
            'VI' => 'U.S. Virgin Islands',
        ];

        return $states[strtoupper($abbr)] ?? $abbr;
    }
}
