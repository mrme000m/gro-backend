<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\QueueService;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationData;
    protected $recipients;
    protected $notificationType;

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
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param array $notificationData
     * @param array $recipients
     * @param string $notificationType
     */
    public function __construct(array $notificationData, array $recipients, string $notificationType = 'general')
    {
        $this->notificationData = $notificationData;
        $this->recipients = $recipients;
        $this->notificationType = $notificationType;
        
        // Set queue based on notification type
        $this->onQueue($this->getQueueForNotificationType($notificationType));
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
            
            Log::info('Sending push notification', [
                'recipients_count' => count($this->recipients),
                'type' => $this->notificationType,
                'title' => $this->notificationData['title'] ?? 'No title'
            ]);

            $results = $this->sendFirebaseNotification();
            
            $executionTime = microtime(true) - $startTime;
            
            Log::info('Push notification sent', [
                'recipients_count' => count($this->recipients),
                'type' => $this->notificationType,
                'success_count' => $results['success'] ?? 0,
                'failure_count' => $results['failure'] ?? 0,
                'execution_time' => round($executionTime * 1000, 2) . 'ms'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'recipients_count' => count($this->recipients),
                'type' => $this->notificationType,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Send Firebase Cloud Messaging notification
     *
     * @return array
     */
    private function sendFirebaseNotification(): array
    {
        $serverKey = config('services.firebase.server_key');
        
        if (!$serverKey) {
            throw new \Exception('Firebase server key not configured');
        }

        $results = ['success' => 0, 'failure' => 0];
        
        // Split recipients into chunks to avoid FCM limits
        $chunks = array_chunk($this->recipients, 1000);
        
        foreach ($chunks as $chunk) {
            try {
                $payload = [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $this->notificationData['title'],
                        'body' => $this->notificationData['body'],
                        'icon' => $this->notificationData['icon'] ?? null,
                        'sound' => 'default',
                        'click_action' => $this->notificationData['click_action'] ?? null,
                    ],
                    'data' => $this->notificationData['data'] ?? [],
                    'priority' => $this->getNotificationPriority(),
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', $payload);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $results['success'] += $responseData['success'] ?? 0;
                    $results['failure'] += $responseData['failure'] ?? 0;
                    
                    // Log invalid tokens for cleanup
                    if (isset($responseData['results'])) {
                        $this->logInvalidTokens($chunk, $responseData['results']);
                    }
                } else {
                    Log::warning('FCM request failed', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    $results['failure'] += count($chunk);
                }

            } catch (\Exception $e) {
                Log::error('Failed to send FCM chunk', [
                    'chunk_size' => count($chunk),
                    'error' => $e->getMessage()
                ]);
                $results['failure'] += count($chunk);
            }
        }

        return $results;
    }

    /**
     * Log invalid tokens for cleanup
     *
     * @param array $tokens
     * @param array $results
     * @return void
     */
    private function logInvalidTokens(array $tokens, array $results): void
    {
        foreach ($results as $index => $result) {
            if (isset($result['error'])) {
                $error = $result['error'];
                if (in_array($error, ['NotRegistered', 'InvalidRegistration'])) {
                    Log::info('Invalid FCM token detected', [
                        'token' => $tokens[$index] ?? 'unknown',
                        'error' => $error
                    ]);
                    
                    // You could dispatch another job to clean up invalid tokens
                    // dispatch(new CleanupInvalidTokensJob($tokens[$index]));
                }
            }
        }
    }

    /**
     * Get notification priority based on type
     *
     * @return string
     */
    private function getNotificationPriority(): string
    {
        return match ($this->notificationType) {
            'order_update', 'delivery_update', 'payment_alert' => 'high',
            'promotional', 'newsletter' => 'normal',
            default => 'normal',
        };
    }

    /**
     * Get the queue name for the notification type
     *
     * @param string $notificationType
     * @return string
     */
    private function getQueueForNotificationType(string $notificationType): string
    {
        return match ($notificationType) {
            'order_update', 'delivery_update', 'payment_alert' => QueueService::QUEUE_NOTIFICATIONS . '_high',
            'promotional', 'newsletter' => QueueService::QUEUE_NOTIFICATIONS . '_low',
            default => QueueService::QUEUE_NOTIFICATIONS,
        };
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Notification job failed permanently', [
            'recipients_count' => count($this->recipients),
            'type' => $this->notificationType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff(): array
    {
        // Exponential backoff: 15s, 45s, 135s
        return [15, 45, 135];
    }
}
