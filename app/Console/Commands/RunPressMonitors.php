<?php

namespace App\Console\Commands;

use App\Jobs\RunMediaMonitor;
use App\Models\MediaMonitor;
use App\Services\PressClipMonitorService;
use Illuminate\Console\Command;

class RunPressMonitors extends Command
{
    protected $signature = 'press:monitor
                            {--monitor= : Run a specific monitor id}
                            {--inline : Run synchronously instead of dispatching queue jobs}
                            {--force : Ignore cadence and run selected monitors immediately}';

    protected $description = 'Run automated press clip monitors and queue new clip ingestion';

    public function handle(PressClipMonitorService $service): int
    {
        $query = MediaMonitor::query()->with(['issue', 'topic']);

        if ($monitorId = $this->option('monitor')) {
            $query->whereKey($monitorId);
        } else {
            $query->active();
        }

        $monitors = $query->get();

        if (!$this->option('force')) {
            $monitors = $monitors->filter(fn(MediaMonitor $monitor) => $monitor->isDue());
        }

        if ($monitors->isEmpty()) {
            $this->info('No media monitors are due to run.');
            return self::SUCCESS;
        }

        $this->info("Running {$monitors->count()} media monitor(s)...");

        foreach ($monitors as $monitor) {
            if ($this->option('inline')) {
                $result = $service->run($monitor);
                $this->line(" - {$monitor->name}: {$result['created']} created, {$result['updated']} updated");
                continue;
            }

            RunMediaMonitor::dispatch($monitor);
            $this->line(" - {$monitor->name}: queued");
        }

        return self::SUCCESS;
    }
}
