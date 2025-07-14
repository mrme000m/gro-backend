<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\QueueService;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailable;
    protected $recipient;
    protected $emailType;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @param mixed $mailable
     * @param string $recipient
     * @param string $emailType
     */
    public function __construct($mailable, string $recipient, string $emailType = 'general')
    {
        $this->mailable = $mailable;
        $this->recipient = $recipient;
        $this->emailType = $emailType;
        
        // Set queue based on email type
        $this->onQueue($this->getQueueForEmailType($emailType));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $startTime = microtime(true);
            
            Log::info('Sending email', [
                'recipient' => $this->recipient,
                'type' => $this->emailType,
                'mailable' => get_class($this->mailable)
            ]);

            Mail::to($this->recipient)->send($this->mailable);
            
            $executionTime = microtime(true) - $startTime;
            
            Log::info('Email sent successfully', [
                'recipient' => $this->recipient,
                'type' => $this->emailType,
                'execution_time' => round($executionTime * 1000, 2) . 'ms'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'recipient' => $this->recipient,
                'type' => $this->emailType,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // If this is the last attempt, log as critical
            if ($this->attempts() >= $this->tries) {
                Log::critical('Email sending failed after all retries', [
                    'recipient' => $this->recipient,
                    'type' => $this->emailType,
                    'error' => $e->getMessage()
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Email job failed permanently', [
            'recipient' => $this->recipient,
            'type' => $this->emailType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // You could send an alert to administrators here
        // or store the failure in a separate table for monitoring
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function backoff()
    {
        // Exponential backoff: 10s, 30s, 90s
        return [10, 30, 90];
    }

    /**
     * Get the queue name for the email type
     *
     * @param string $emailType
     * @return string
     */
    private function getQueueForEmailType(string $emailType): string
    {
        return match ($emailType) {
            'order_confirmation', 'password_reset', 'verification' => QueueService::QUEUE_EMAIL . '_high',
            'newsletter', 'promotional' => QueueService::QUEUE_EMAIL . '_low',
            default => QueueService::QUEUE_EMAIL,
        };
    }
}
