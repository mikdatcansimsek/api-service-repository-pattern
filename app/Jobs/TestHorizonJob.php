<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TestHorizonJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Horizon Test Job çalıştı!', [
            'timestamp' => now(),
            'job_id' => $this->job->getJobId()
        ]);

        // 2 saniye bekle (işlem simülasyonu)
        sleep(2);

        \Log::info('Horizon Test Job tamamlandı!');
    }
}
