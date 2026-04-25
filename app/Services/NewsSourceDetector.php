<?php

namespace App\Services;

/**
 * Suggests relevant news sources based on location and level of government.
 */
class NewsSourceDetector
{
    /**
     * Suggest news sources based on level and location.
     */
    public function suggestSources(string $level, string $state, ?string $district = null, array $cities = []): array
    {
        $sources = [];

        // National political sources (for federal officials)
        if ($level === 'federal') {
            $sources = array_merge($sources, $this->getNationalPoliticalSources());
        }

        // State-level sources
        $sources = array_merge($sources, $this->getStateSources($state));

        // Local sources based on cities
        if (!empty($cities)) {
            $sources = array_merge($sources, $this->getLocalSources($state, $cities));
        }

        // Committee-specific trade press (for federal officials with known committee assignments)
        if ($level === 'federal') {
            $sources = array_merge($sources, $this->getTradePress());
        }

        return $sources;
    }

    /**
     * Get national political news sources.
     */
    protected function getNationalPoliticalSources(): array
    {
        return [
            ['name' => 'Politico', 'url' => 'https://www.politico.com', 'category' => 'national', 'suggested' => true],
            ['name' => 'The Hill', 'url' => 'https://thehill.com', 'category' => 'national', 'suggested' => true],
            ['name' => 'Roll Call', 'url' => 'https://rollcall.com', 'category' => 'national', 'suggested' => false],
            ['name' => 'CQ Roll Call', 'url' => 'https://www.cq.com', 'category' => 'national', 'suggested' => false],
            ['name' => 'Washington Post', 'url' => 'https://www.washingtonpost.com', 'category' => 'national', 'suggested' => false],
            ['name' => 'NPR Politics', 'url' => 'https://www.npr.org/sections/politics/', 'category' => 'national', 'suggested' => false],
        ];
    }

    /**
     * Get state-level news sources.
     */
    protected function getStateSources(string $state): array
    {
        $stateSourceMap = [
            'MA' => [
                ['name' => 'The Boston Globe', 'url' => 'https://www.bostonglobe.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Boston Herald', 'url' => 'https://www.bostonherald.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'MassLive', 'url' => 'https://www.masslive.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'CommonWealth Magazine', 'url' => 'https://commonwealthmagazine.org', 'category' => 'state', 'suggested' => true],
                ['name' => 'WBUR (NPR Boston)', 'url' => 'https://www.wbur.org', 'category' => 'state', 'suggested' => false],
                ['name' => 'WCVB-TV (ABC 5)', 'url' => 'https://www.wcvb.com', 'category' => 'state', 'suggested' => false],
                ['name' => 'WBZ-TV (CBS 4)', 'url' => 'https://www.cbsnews.com/boston/', 'category' => 'state', 'suggested' => false],
            ],
            'CA' => [
                ['name' => 'Los Angeles Times', 'url' => 'https://www.latimes.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'San Francisco Chronicle', 'url' => 'https://www.sfchronicle.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'CalMatters', 'url' => 'https://calmatters.org', 'category' => 'state', 'suggested' => true],
                ['name' => 'Sacramento Bee', 'url' => 'https://www.sacbee.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'San Diego Union-Tribune', 'url' => 'https://www.sandiegouniontribune.com', 'category' => 'state', 'suggested' => false],
            ],
            'NY' => [
                ['name' => 'New York Times', 'url' => 'https://www.nytimes.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'New York Post', 'url' => 'https://nypost.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Gothamist', 'url' => 'https://gothamist.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'City & State NY', 'url' => 'https://www.cityandstateny.com', 'category' => 'state', 'suggested' => true],
            ],
            'TX' => [
                ['name' => 'Houston Chronicle', 'url' => 'https://www.houstonchronicle.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Dallas Morning News', 'url' => 'https://www.dallasnews.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Texas Tribune', 'url' => 'https://www.texastribune.org', 'category' => 'state', 'suggested' => true],
                ['name' => 'San Antonio Express-News', 'url' => 'https://www.expressnews.com', 'category' => 'state', 'suggested' => false],
            ],
            'FL' => [
                ['name' => 'Miami Herald', 'url' => 'https://www.miamiherald.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Tampa Bay Times', 'url' => 'https://www.tampabay.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Orlando Sentinel', 'url' => 'https://www.orlandosentinel.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Sun Sentinel', 'url' => 'https://www.sun-sentinel.com', 'category' => 'state', 'suggested' => false],
            ],
            'PA' => [
                ['name' => 'Philadelphia Inquirer', 'url' => 'https://www.inquirer.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Pittsburgh Post-Gazette', 'url' => 'https://www.post-gazette.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Pennsylvania Capital-Star', 'url' => 'https://www.penncapital-star.com', 'category' => 'state', 'suggested' => true],
            ],
            'OH' => [
                ['name' => 'Cleveland Plain Dealer', 'url' => 'https://www.cleveland.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Columbus Dispatch', 'url' => 'https://www.dispatch.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Cincinnati Enquirer', 'url' => 'https://www.cincinnati.com', 'category' => 'state', 'suggested' => true],
            ],
            'IL' => [
                ['name' => 'Chicago Tribune', 'url' => 'https://www.chicagotribune.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Chicago Sun-Times', 'url' => 'https://chicago.suntimes.com', 'category' => 'state', 'suggested' => true],
                ['name' => 'Capitol Fax', 'url' => 'https://capitolfax.com', 'category' => 'state', 'suggested' => true],
            ],
        ];

        return $stateSourceMap[strtoupper($state)] ?? [];
    }

    /**
     * Get local news sources based on cities.
     */
    protected function getLocalSources(string $state, array $cities): array
    {
        // Massachusetts local sources
        $localSourceMap = [
            'MA' => [
                'Salem' => [
                    ['name' => 'Salem News', 'url' => 'https://www.salemnews.com', 'category' => 'local', 'suggested' => true],
                    ['name' => 'Salem Patch', 'url' => 'https://patch.com/massachusetts/salem', 'category' => 'local', 'suggested' => false],
                ],
                'Lynn' => [
                    ['name' => 'The Daily Item', 'url' => 'https://www.itemlive.com', 'category' => 'local', 'suggested' => true],
                    ['name' => 'Lynn Patch', 'url' => 'https://patch.com/massachusetts/lynn', 'category' => 'local', 'suggested' => false],
                ],
                'Beverly' => [
                    ['name' => 'Beverly Citizen', 'url' => 'https://www.wickedlocal.com/beverly/', 'category' => 'local', 'suggested' => true],
                ],
                'Peabody' => [
                    ['name' => 'Peabody Patch', 'url' => 'https://patch.com/massachusetts/peabody', 'category' => 'local', 'suggested' => true],
                ],
                'Marblehead' => [
                    ['name' => 'Marblehead Reporter', 'url' => 'https://www.wickedlocal.com/marblehead/', 'category' => 'local', 'suggested' => false],
                ],
                'Danvers' => [
                    ['name' => 'Danvers Herald', 'url' => 'https://www.wickedlocal.com/danvers/', 'category' => 'local', 'suggested' => false],
                ],
                'Saugus' => [
                    ['name' => 'Saugus Advertiser', 'url' => 'https://www.wickedlocal.com/saugus/', 'category' => 'local', 'suggested' => false],
                ],
                'Revere' => [
                    ['name' => 'Revere Journal', 'url' => 'https://www.reverejournal.com', 'category' => 'local', 'suggested' => true],
                ],
            ],
            // Add more states/cities as needed...
        ];

        $sources = [];
        $stateLocal = $localSourceMap[strtoupper($state)] ?? [];

        foreach ($cities as $city) {
            $cityNormalized = ucfirst(strtolower($city));
            if (isset($stateLocal[$cityNormalized])) {
                $sources = array_merge($sources, $stateLocal[$cityNormalized]);
            }
        }

        // Add Wicked Local as a general catch-all for MA
        if (strtoupper($state) === 'MA' && !empty($cities)) {
            $sources[] = ['name' => 'Wicked Local North', 'url' => 'https://www.wickedlocal.com', 'category' => 'local', 'suggested' => false];
        }

        return $sources;
    }

    /**
     * Get trade press sources (for committee-relevant coverage).
     */
    protected function getTradePress(): array
    {
        return [
            ['name' => 'Defense News', 'url' => 'https://www.defensenews.com', 'category' => 'trade', 'suggested' => false, 'committees' => ['Armed Services']],
            ['name' => 'Inside Defense', 'url' => 'https://insidedefense.com', 'category' => 'trade', 'suggested' => false, 'committees' => ['Armed Services']],
            ['name' => 'E&E News', 'url' => 'https://www.eenews.net', 'category' => 'trade', 'suggested' => false, 'committees' => ['Energy and Commerce', 'Natural Resources']],
            ['name' => 'Modern Healthcare', 'url' => 'https://www.modernhealthcare.com', 'category' => 'trade', 'suggested' => false, 'committees' => ['Energy and Commerce', 'Ways and Means']],
            ['name' => 'Transport Topics', 'url' => 'https://www.ttnews.com', 'category' => 'trade', 'suggested' => false, 'committees' => ['Transportation and Infrastructure']],
            ['name' => 'American Banker', 'url' => 'https://www.americanbanker.com', 'category' => 'trade', 'suggested' => false, 'committees' => ['Financial Services']],
        ];
    }

    /**
     * Filter trade press by committees.
     */
    public function filterTradePressByCommittees(array $committees): array
    {
        $tradePress = $this->getTradePress();

        return array_filter($tradePress, function ($source) use ($committees) {
            if (!isset($source['committees'])) {
                return false;
            }
            return !empty(array_intersect($source['committees'], $committees));
        });
    }
}
