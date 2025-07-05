<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class EmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $subject;
    public $message;
    public $priority;

    /**
     * Job maximum attempts
     */
    public $tries = 3;

    /**
     * Job timeout in seconds
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $subject, $message, $priority = 'normal')
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->priority = $priority;

        // Priority queue assignment
        $this->onQueue($priority === 'high' ? 'high' : 'default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('📧 Email Job Başlatıldı', [
            'job_id' => $this->job->getJobId(),
            'email' => $this->email,
            'subject' => $this->subject,
            'priority' => $this->priority,
            'attempt' => $this->attempts(),
            'started_at' => now()
        ]);

        // Simulate email sending process
        $this->simulateEmailSending();

        Log::info('✅ Email Job Tamamlandı', [
            'job_id' => $this->job->getJobId(),
            'email' => $this->email,
            'completed_at' => now(),
            'duration' => '~3 seconds'
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ Email Job Başarısız', [
            'job_id' => $this->job->getJobId() ?? 'unknown',
            'email' => $this->email,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'failed_at' => now()
        ]);
    }

    /**
     * Simulate email sending with random processing time
     */
    private function simulateEmailSending(): void
    {
        // Random processing time (1-5 seconds)
        $processingTime = random_int(1, 5);

        Log::info('📨 Email Gönderiliyor...', [
            'email' => $this->email,
            'estimated_time' => "{$processingTime} seconds"
        ]);

        // Simulate work
        sleep($processingTime);

        // 10% chance of failure (for testing failed jobs)
        if (random_int(1, 10) === 1 && $this->attempts() === 1) {
            throw new \Exception('Simulated email server error');
        }

        Log::info('📬 Email Gönderildi', [
            'email' => $this->email,
            'processing_time' => "{$processingTime}s"
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [10, 30, 60]; // 10s, 30s, 60s delays
    }

    /**
     * Determine the queue connection for the job.
     */

}
