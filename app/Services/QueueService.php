<?php

namespace App\Services;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QueueService
{
    /**
     * Queue priorities
     */
    const PRIORITY_HIGH = 'high';
    const PRIORITY_NORMAL = 'default';
    const PRIORITY_LOW = 'low';

    /**
     * Queue names for different types of jobs
     */
    const QUEUE_EMAIL = 'emails';
    const QUEUE_NOTIFICATIONS = 'notifications';
    const QUEUE_REPORTS = 'reports';
    const QUEUE_IMAGE_PROCESSING = 'images';
    const QUEUE_DATA_PROCESSING = 'data';
    const QUEUE_EXPORTS = 'exports';
    const QUEUE_IMPORTS = 'imports';

    /**
     * Dispatch a job to the appropriate queue
     *
     * @param mixed $job
     * @param string $queue
     * @param string $priority
     * @param int $delay
     * @return mixed
     */
    public function dispatch($job, string $queue = self::QUEUE_NORMAL, string $priority = self::PRIORITY_NORMAL, int $delay = 0)
    {
        try {
            $queueName = $this->getQueueName($queue, $priority);
            
            if ($delay > 0) {
                return $job->onQueue($queueName)->delay(now()->addSeconds($delay))->dispatch();
            }
            
            return $job->onQueue($queueName)->dispatch();
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch job', [
                'job' => get_class($job),
                'queue' => $queue,
                'priority' => $priority,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Dispatch a job with high priority
     *
     * @param mixed $job
     * @param string $queue
     * @return mixed
     */
    public function dispatchHighPriority($job, string $queue = self::QUEUE_NORMAL)
    {
        return $this->dispatch($job, $queue, self::PRIORITY_HIGH);
    }

    /**
     * Dispatch a job with low priority
     *
     * @param mixed $job
     * @param string $queue
     * @return mixed
     */
    public function dispatchLowPriority($job, string $queue = self::QUEUE_NORMAL)
    {
        return $this->dispatch($job, $queue, self::PRIORITY_LOW);
    }

    /**
     * Dispatch a delayed job
     *
     * @param mixed $job
     * @param int $delaySeconds
     * @param string $queue
     * @param string $priority
     * @return mixed
     */
    public function dispatchDelayed($job, int $delaySeconds, string $queue = self::QUEUE_NORMAL, string $priority = self::PRIORITY_NORMAL)
    {
        return $this->dispatch($job, $queue, $priority, $delaySeconds);
    }

    /**
     * Dispatch multiple jobs as a batch
     *
     * @param array $jobs
     * @param string $queue
     * @param string $priority
     * @return void
     */
    public function dispatchBatch(array $jobs, string $queue = self::QUEUE_NORMAL, string $priority = self::PRIORITY_NORMAL): void
    {
        $queueName = $this->getQueueName($queue, $priority);
        
        foreach ($jobs as $job) {
            try {
                $job->onQueue($queueName)->dispatch();
            } catch (\Exception $e) {
                Log::error('Failed to dispatch batch job', [
                    'job' => get_class($job),
                    'queue' => $queue,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get queue statistics
     *
     * @return array
     */
    public function getQueueStats(): array
    {
        $stats = [];
        
        try {
            // Database queue stats
            if (config('queue.default') === 'database') {
                $stats['database'] = $this->getDatabaseQueueStats();
            }
            
            // Redis queue stats
            if (config('queue.default') === 'redis') {
                $stats['redis'] = $this->getRedisQueueStats();
            }
            
            // Failed jobs stats
            $stats['failed_jobs'] = $this->getFailedJobsStats();
            
            // Processing stats
            $stats['processing'] = $this->getProcessingStats();
            
        } catch (\Exception $e) {
            Log::error('Failed to get queue stats', ['error' => $e->getMessage()]);
            $stats['error'] = 'Failed to retrieve queue statistics';
        }
        
        return $stats;
    }

    /**
     * Get failed jobs count and details
     *
     * @return array
     */
    public function getFailedJobsStats(): array
    {
        try {
            $totalFailed = DB::table('failed_jobs')->count();
            $recentFailed = DB::table('failed_jobs')
                ->where('failed_at', '>=', Carbon::now()->subHours(24))
                ->count();
            
            $failedByQueue = DB::table('failed_jobs')
                ->select(DB::raw('JSON_EXTRACT(payload, "$.displayName") as job_name, COUNT(*) as count'))
                ->groupBy('job_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();
            
            return [
                'total_failed' => $totalFailed,
                'failed_last_24h' => $recentFailed,
                'failed_by_job' => $failedByQueue,
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get failed jobs stats', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to retrieve failed jobs statistics'];
        }
    }

    /**
     * Retry failed jobs
     *
     * @param array $jobIds
     * @return array
     */
    public function retryFailedJobs(array $jobIds = []): array
    {
        $results = ['success' => 0, 'failed' => 0];
        
        try {
            if (empty($jobIds)) {
                // Retry all failed jobs
                $failedJobs = DB::table('failed_jobs')->get();
            } else {
                // Retry specific jobs
                $failedJobs = DB::table('failed_jobs')->whereIn('id', $jobIds)->get();
            }
            
            foreach ($failedJobs as $failedJob) {
                try {
                    $payload = json_decode($failedJob->payload, true);
                    $job = unserialize($payload['data']['command']);
                    
                    // Dispatch the job again
                    dispatch($job);
                    
                    // Remove from failed jobs table
                    DB::table('failed_jobs')->where('id', $failedJob->id)->delete();
                    
                    $results['success']++;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to retry job', [
                        'job_id' => $failedJob->id,
                        'error' => $e->getMessage()
                    ]);
                    $results['failed']++;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to retry failed jobs', ['error' => $e->getMessage()]);
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Clear failed jobs
     *
     * @param array $jobIds
     * @return int
     */
    public function clearFailedJobs(array $jobIds = []): int
    {
        try {
            if (empty($jobIds)) {
                return DB::table('failed_jobs')->delete();
            } else {
                return DB::table('failed_jobs')->whereIn('id', $jobIds)->delete();
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear failed jobs', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get queue name with priority
     *
     * @param string $queue
     * @param string $priority
     * @return string
     */
    private function getQueueName(string $queue, string $priority): string
    {
        if ($priority === self::PRIORITY_HIGH) {
            return $queue . '_high';
        } elseif ($priority === self::PRIORITY_LOW) {
            return $queue . '_low';
        }
        
        return $queue;
    }

    /**
     * Get database queue statistics
     *
     * @return array
     */
    private function getDatabaseQueueStats(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $processing = DB::table('jobs')->where('reserved_at', '!=', null)->count();
            
            $queueCounts = DB::table('jobs')
                ->select('queue', DB::raw('COUNT(*) as count'))
                ->groupBy('queue')
                ->get()
                ->pluck('count', 'queue')
                ->toArray();
            
            return [
                'pending' => $pending,
                'processing' => $processing,
                'by_queue' => $queueCounts,
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get database queue stats', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to retrieve database queue statistics'];
        }
    }

    /**
     * Get Redis queue statistics
     *
     * @return array
     */
    private function getRedisQueueStats(): array
    {
        try {
            $redis = Redis::connection();
            $queues = ['default', 'high', 'low', 'emails', 'notifications', 'reports', 'images', 'data'];
            
            $stats = [];
            foreach ($queues as $queue) {
                $queueKey = config('queue.connections.redis.queue', 'queues') . ':' . $queue;
                $stats[$queue] = $redis->llen($queueKey);
            }
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error('Failed to get Redis queue stats', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to retrieve Redis queue statistics'];
        }
    }

    /**
     * Get processing statistics
     *
     * @return array
     */
    private function getProcessingStats(): array
    {
        try {
            // This would typically integrate with a monitoring system
            // For now, we'll provide basic stats
            
            $stats = [
                'workers_active' => $this->getActiveWorkerCount(),
                'average_processing_time' => $this->getAverageProcessingTime(),
                'jobs_processed_today' => $this->getJobsProcessedToday(),
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error('Failed to get processing stats', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to retrieve processing statistics'];
        }
    }

    /**
     * Get active worker count (placeholder implementation)
     *
     * @return int
     */
    private function getActiveWorkerCount(): int
    {
        // This would typically check process list or monitoring system
        // For now, return a default value
        return 1;
    }

    /**
     * Get average processing time (placeholder implementation)
     *
     * @return float
     */
    private function getAverageProcessingTime(): float
    {
        // This would typically be tracked in a monitoring system
        // For now, return a default value
        return 2.5; // seconds
    }

    /**
     * Get jobs processed today (placeholder implementation)
     *
     * @return int
     */
    private function getJobsProcessedToday(): int
    {
        // This would typically be tracked in logs or monitoring system
        // For now, return a default value
        return 0;
    }
}
