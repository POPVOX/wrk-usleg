<?php

namespace App\Jobs;

use App\Models\MediaMonitor;
use App\Services\PressClipMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunMediaMonitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public MediaMonitor $monitor,
    ) {
    }

    public function handle(PressClipMonitorService $service): void
    {
        $service->run($this->monitor->fresh());
    }
}
