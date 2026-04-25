<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Vote;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Congress.gov API Service
 * 
 * Fetches legislative data from the official Congress.gov API.
 * API Documentation: https://api.congress.gov/
 * 
 * Rate Limit: 5,000 requests per hour
 */
class CongressApiService
{
    protected ?string $apiKey;
    protected string $baseUrl = 'https://api.congress.gov/v3';
    protected int $timeout = 30;

    public function __construct()
    {
        $this->apiKey = config('office.congress_api.key') ?: '';
    }

    /**
     * Check if API is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Make an API request.
     */
    protected function request(string $endpoint, array $params = []): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('CongressApiService: API key not configured');
            return null;
        }

        $params['api_key'] = $this->apiKey;
        $params['format'] = 'json';

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/{$endpoint}", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("CongressApiService: API request failed", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("CongressApiService: Request exception", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch member information by Bioguide ID.
     */
    public function getMember(string $bioguideId): ?array
    {
        $data = $this->request("member/{$bioguideId}");
        return $data['member'] ?? null;
    }

    /**
     * Fetch bills sponsored by the member.
     */
    public function fetchSponsoredBills(string $bioguideId, int $congress = null, int $limit = 50): array
    {
        $congress = $congress ?? config('office.current_congress', 119);

        $data = $this->request("member/{$bioguideId}/sponsored-legislation", [
            'limit' => $limit,
        ]);

        if (!$data || !isset($data['sponsoredLegislation'])) {
            return [];
        }

        $bills = [];
        foreach ($data['sponsoredLegislation'] as $billData) {
            $bill = $this->processBillData($billData, 'sponsor');
            if ($bill) {
                $bills[] = $bill;
            }
        }

        return $bills;
    }

    /**
     * Fetch bills cosponsored by the member.
     */
    public function fetchCosponsoredBills(string $bioguideId, int $congress = null, int $limit = 100): array
    {
        $congress = $congress ?? config('office.current_congress', 119);

        $data = $this->request("member/{$bioguideId}/cosponsored-legislation", [
            'limit' => $limit,
        ]);

        if (!$data || !isset($data['cosponsoredLegislation'])) {
            return [];
        }

        $bills = [];
        foreach ($data['cosponsoredLegislation'] as $billData) {
            $bill = $this->processBillData($billData, 'cosponsor');
            if ($bill) {
                $bills[] = $bill;
            }
        }

        return $bills;
    }

    /**
     * Process bill data from API response.
     */
    protected function processBillData(array $billData, string $role): ?array
    {
        try {
            // Extract bill number and type
            $number = $billData['number'] ?? null;
            $type = strtoupper($billData['type'] ?? 'HR');

            if (!$number) {
                return null;
            }

            $billNumber = $type . $number;

            return [
                'bill_number' => $billNumber,
                'bill_type' => $type,
                'title' => $billData['title'] ?? 'Untitled',
                'sponsor_role' => $role,
                'introduced_date' => $billData['introducedDate'] ?? null,
                'status' => $billData['latestAction']['text'] ?? 'Unknown',
                'congress' => (string) ($billData['congress'] ?? config('office.current_congress')),
                'congress_api_url' => $billData['url'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::warning("CongressApiService: Error processing bill", [
                'error' => $e->getMessage(),
                'data' => $billData,
            ]);
            return null;
        }
    }

    /**
     * Store or update a bill in the database.
     */
    public function storeBill(array $billData): ?Bill
    {
        try {
            return Bill::updateOrCreate(
                ['bill_number' => $billData['bill_number']],
                [
                    'bill_type' => $billData['bill_type'],
                    'title' => $billData['title'],
                    'sponsor_role' => $billData['sponsor_role'],
                    'introduced_date' => $billData['introduced_date'],
                    'status' => $billData['status'],
                    'congress' => $billData['congress'],
                    'congress_api_url' => $billData['congress_api_url'],
                ]
            );
        } catch (\Exception $e) {
            Log::error("CongressApiService: Error storing bill", [
                'error' => $e->getMessage(),
                'bill' => $billData['bill_number'] ?? 'unknown',
            ]);
            return null;
        }
    }

    /**
     * Fetch bill details including summary.
     */
    public function getBillDetails(string $congress, string $billType, int $billNumber): ?array
    {
        $billType = strtolower($billType);
        $data = $this->request("bill/{$congress}/{$billType}/{$billNumber}");
        return $data['bill'] ?? null;
    }

    /**
     * Fetch bill summary.
     */
    public function getBillSummary(string $congress, string $billType, int $billNumber): ?string
    {
        $billType = strtolower($billType);
        $data = $this->request("bill/{$congress}/{$billType}/{$billNumber}/summaries");

        if (!$data || !isset($data['summaries']) || empty($data['summaries'])) {
            return null;
        }

        // Get the most recent summary
        $summary = end($data['summaries']);
        return $summary['text'] ?? null;
    }

    /**
     * Fetch recent House roll call votes.
     */
    public function fetchHouseVotes(int $congress = null, int $session = null, int $limit = 50): array
    {
        $congress = $congress ?? config('office.current_congress', 119);
        $session = $session ?? (now()->month <= 6 ? 1 : 2); // Session 1 = first year, Session 2 = second year

        // Note: The Congress.gov API has limited vote data
        // For comprehensive votes, you may need to use the House Clerk's XML feed
        $data = $this->request("house-vote", [
            'congress' => $congress,
            'limit' => $limit,
        ]);

        return $data['votes'] ?? [];
    }

    /**
     * Search for legislation by keyword.
     */
    public function searchLegislation(string $query, int $congress = null, int $limit = 20): array
    {
        $congress = $congress ?? config('office.current_congress', 119);

        $data = $this->request("bill", [
            'congress' => $congress,
            'limit' => $limit,
        ]);

        return $data['bills'] ?? [];
    }

    /**
     * Get committee information.
     */
    public function getCommittees(string $chamber = 'house', int $congress = null): array
    {
        $congress = $congress ?? config('office.current_congress', 119);

        $data = $this->request("committee/{$chamber}/{$congress}");

        return $data['committees'] ?? [];
    }

    /**
     * Get member's committee assignments.
     */
    public function getMemberCommittees(string $bioguideId): array
    {
        $member = $this->getMember($bioguideId);

        if (!$member) {
            return [];
        }

        return $member['terms'] ?? [];
    }

    /**
     * Sync all bills for the configured member.
     */
    public function syncMemberBills(): array
    {
        $bioguideId = config('office.member_bioguide_id');

        if (!$bioguideId || $bioguideId === 'S000XXX') {
            return [
                'success' => false,
                'message' => 'Member Bioguide ID not configured',
                'sponsored' => 0,
                'cosponsored' => 0,
            ];
        }

        $sponsoredCount = 0;
        $cosponsoredCount = 0;

        // Fetch and store sponsored bills
        $sponsoredBills = $this->fetchSponsoredBills($bioguideId);
        foreach ($sponsoredBills as $billData) {
            if ($this->storeBill($billData)) {
                $sponsoredCount++;
            }
        }

        // Fetch and store cosponsored bills
        $cosponsoredBills = $this->fetchCosponsoredBills($bioguideId);
        foreach ($cosponsoredBills as $billData) {
            if ($this->storeBill($billData)) {
                $cosponsoredCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Synced {$sponsoredCount} sponsored and {$cosponsoredCount} cosponsored bills",
            'sponsored' => $sponsoredCount,
            'cosponsored' => $cosponsoredCount,
        ];
    }

    /**
     * Get amendment information.
     */
    public function getAmendments(string $billType, int $billNumber, int $congress = null): array
    {
        $congress = $congress ?? config('office.current_congress', 119);
        $billType = strtolower($billType);

        $data = $this->request("bill/{$congress}/{$billType}/{$billNumber}/amendments");

        return $data['amendments'] ?? [];
    }

    /**
     * Get cosponsors of a specific bill.
     */
    public function getBillCosponsors(string $billType, int $billNumber, int $congress = null): array
    {
        $congress = $congress ?? config('office.current_congress', 119);
        $billType = strtolower($billType);

        $data = $this->request("bill/{$congress}/{$billType}/{$billNumber}/cosponsors");

        return $data['cosponsors'] ?? [];
    }

    /**
     * Get bill actions/history.
     */
    public function getBillActions(string $billType, int $billNumber, int $congress = null): array
    {
        $congress = $congress ?? config('office.current_congress', 119);
        $billType = strtolower($billType);

        $data = $this->request("bill/{$congress}/{$billType}/{$billNumber}/actions");

        return $data['actions'] ?? [];
    }

    /**
     * Get treaties (Senate only).
     */
    public function getTreaties(int $congress = null, int $limit = 20): array
    {
        $congress = $congress ?? config('office.current_congress', 119);

        $data = $this->request("treaty/{$congress}", [
            'limit' => $limit,
        ]);

        return $data['treaties'] ?? [];
    }

    /**
     * Get nominations.
     */
    public function getNominations(int $congress = null, int $limit = 20): array
    {
        $congress = $congress ?? config('office.current_congress', 119);

        $data = $this->request("nomination/{$congress}", [
            'limit' => $limit,
        ]);

        return $data['nominations'] ?? [];
    }
}
