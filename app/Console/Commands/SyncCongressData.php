<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CongressApiService;
use App\Models\Bill;

class SyncCongressData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'congress:sync 
                            {--bills : Sync bills only}
                            {--member= : Override member Bioguide ID}
                            {--sponsored : Only sync sponsored bills}
                            {--cosponsored : Only sync cosponsored bills}
                            {--limit=100 : Limit number of bills to fetch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync legislative data from Congress.gov API';

    /**
     * Execute the console command.
     */
    public function handle(CongressApiService $congressApi): int
    {
        // Check if API is configured
        if (!$congressApi->isConfigured()) {
            $this->error('Congress.gov API key is not configured.');
            $this->line('');
            $this->line('To configure:');
            $this->line('1. Get a free API key from https://api.congress.gov/sign-up/');
            $this->line('2. Add CONGRESS_API_KEY=your_key to your .env file');
            return Command::FAILURE;
        }

        $bioguideId = $this->option('member') ?: config('office.member_bioguide_id');

        if (!$bioguideId || $bioguideId === 'S000XXX') {
            $this->error('Member Bioguide ID not configured.');
            $this->line('');
            $this->line('To configure:');
            $this->line('1. Find the Member\'s Bioguide ID at https://bioguide.congress.gov/');
            $this->line('2. Add MEMBER_BIOGUIDE_ID=XXXXX to your .env file');
            return Command::FAILURE;
        }

        $this->info("Syncing data for Member: {$bioguideId}");
        $this->line('');

        // Get member info first
        $this->info('Fetching member information...');
        $memberInfo = $congressApi->getMember($bioguideId);

        if ($memberInfo) {
            $name = $memberInfo['directOrderName'] ?? $memberInfo['invertedOrderName'] ?? 'Unknown';
            $state = $memberInfo['state'] ?? '';
            $party = $memberInfo['partyName'] ?? '';
            $this->line("Member: {$name} ({$party} - {$state})");
        } else {
            $this->warn('Could not fetch member information. Proceeding with bill sync...');
        }

        $this->line('');

        $limit = (int) $this->option('limit');
        $sponsoredOnly = $this->option('sponsored');
        $cosponsoredOnly = $this->option('cosponsored');

        $sponsoredCount = 0;
        $cosponsoredCount = 0;

        // Sync sponsored bills
        if (!$cosponsoredOnly) {
            $this->info('Fetching sponsored bills...');
            $bar = $this->output->createProgressBar();
            $bar->start();

            $sponsoredBills = $congressApi->fetchSponsoredBills($bioguideId, null, $limit);

            foreach ($sponsoredBills as $billData) {
                if ($congressApi->storeBill($billData)) {
                    $sponsoredCount++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->line("Synced {$sponsoredCount} sponsored bills");
        }

        // Sync cosponsored bills
        if (!$sponsoredOnly) {
            $this->info('Fetching cosponsored bills...');
            $bar = $this->output->createProgressBar();
            $bar->start();

            $cosponsoredBills = $congressApi->fetchCosponsoredBills($bioguideId, null, $limit);

            foreach ($cosponsoredBills as $billData) {
                if ($congressApi->storeBill($billData)) {
                    $cosponsoredCount++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->line("Synced {$cosponsoredCount} cosponsored bills");
        }

        $this->newLine();
        $this->info('Sync complete!');
        $this->line('');

        // Show summary
        $this->table(
            ['Metric', 'Count'],
            [
                ['Sponsored Bills', $sponsoredCount],
                ['Cosponsored Bills', $cosponsoredCount],
                ['Total Bills in DB', Bill::count()],
                ['This Congress', Bill::currentCongress()->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
