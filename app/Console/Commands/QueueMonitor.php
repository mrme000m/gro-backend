<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueueService;
use Illuminate\Support\Facades\DB;

class QueueMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor 
                            {--stats : Show queue statistics}
                            {--failed : Show failed jobs}
                            {--retry=* : Retry failed jobs by ID}
                            {--clear-failed : Clear all failed jobs}
                            {--workers : Show worker status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor and manage queue system';

    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        parent::__construct();
        $this->queueService = $queueService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('stats')) {
            $this->showQueueStats();
        } elseif ($this->option('failed')) {
            $this->showFailedJobs();
        } elseif ($this->option('retry')) {
            $this->retryFailedJobs();
        } elseif ($this->option('clear-failed')) {
            $this->clearFailedJobs();
        } elseif ($this->option('workers')) {
            $this->showWorkerStatus();
        } else {
            $this->showOverview();
        }

        return 0;
    }

    /**
     * Show queue statistics
     */
    private function showQueueStats(): void
    {
        $this->info('Queue Statistics');
        $this->line('==================');

        $stats = $this->queueService->getQueueStats();

        if (isset($stats['database'])) {
            $this->line('Database Queue:');
            $this->line('  Pending: ' . ($stats['database']['pending'] ?? 0));
            $this->line('  Processing: ' . ($stats['database']['processing'] ?? 0));
            
            if (isset($stats['database']['by_queue'])) {
                $this->line('  By Queue:');
                foreach ($stats['database']['by_queue'] as $queue => $count) {
                    $this->line("    {$queue}: {$count}");
                }
            }
            $this->newLine();
        }

        if (isset($stats['redis'])) {
            $this->line('Redis Queue:');
            foreach ($stats['redis'] as $queue => $count) {
                $this->line("  {$queue}: {$count}");
            }
            $this->newLine();
        }

        if (isset($stats['failed_jobs'])) {
            $this->line('Failed Jobs:');
            $this->line('  Total: ' . ($stats['failed_jobs']['total_failed'] ?? 0));
            $this->line('  Last 24h: ' . ($stats['failed_jobs']['failed_last_24h'] ?? 0));
            $this->newLine();
        }

        if (isset($stats['processing'])) {
            $this->line('Processing Stats:');
            $this->line('  Active Workers: ' . ($stats['processing']['workers_active'] ?? 0));
            $this->line('  Avg Processing Time: ' . ($stats['processing']['average_processing_time'] ?? 0) . 's');
            $this->line('  Jobs Processed Today: ' . ($stats['processing']['jobs_processed_today'] ?? 0));
        }
    }

    /**
     * Show failed jobs
     */
    private function showFailedJobs(): void
    {
        $this->info('Failed Jobs');
        $this->line('============');

        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->info('No failed jobs found.');
            return;
        }

        $headers = ['ID', 'Queue', 'Job', 'Failed At', 'Exception'];
        $rows = [];

        foreach ($failedJobs as $job) {
            $payload = json_decode($job->payload, true);
            $jobName = $payload['displayName'] ?? 'Unknown';
            $exception = substr($job->exception, 0, 100) . '...';

            $rows[] = [
                $job->id,
                $job->queue ?? 'default',
                $jobName,
                $job->failed_at,
                $exception
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->line('Use --retry=<id> to retry specific jobs or --clear-failed to clear all failed jobs.');
    }

    /**
     * Retry failed jobs
     */
    private function retryFailedJobs(): void
    {
        $jobIds = $this->option('retry');

        if (empty($jobIds)) {
            $this->error('No job IDs provided for retry.');
            return;
        }

        $this->info('Retrying failed jobs...');

        $results = $this->queueService->retryFailedJobs($jobIds);

        $this->line("Successfully retried: {$results['success']}");
        $this->line("Failed to retry: {$results['failed']}");

        if (isset($results['error'])) {
            $this->error("Error: {$results['error']}");
        }
    }

    /**
     * Clear failed jobs
     */
    private function clearFailedJobs(): void
    {
        if (!$this->confirm('Are you sure you want to clear all failed jobs?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Clearing failed jobs...');

        $cleared = $this->queueService->clearFailedJobs();

        $this->info("Cleared {$cleared} failed jobs.");
    }

    /**
     * Show worker status
     */
    private function showWorkerStatus(): void
    {
        $this->info('Worker Status');
        $this->line('=============');

        // Check if queue workers are running
        $processes = $this->getQueueWorkerProcesses();

        if (empty($processes)) {
            $this->warn('No queue workers are currently running.');
            $this->newLine();
            $this->line('To start queue workers:');
            $this->line('  php artisan queue:work --daemon');
            $this->line('  php artisan queue:work --queue=high,default,low');
            return;
        }

        $headers = ['PID', 'Command', 'CPU %', 'Memory', 'Started'];
        $rows = [];

        foreach ($processes as $process) {
            $rows[] = [
                $process['pid'] ?? 'N/A',
                $process['command'] ?? 'N/A',
                $process['cpu'] ?? 'N/A',
                $process['memory'] ?? 'N/A',
                $process['started'] ?? 'N/A'
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->line('Queue Configuration:');
        $this->line('  Driver: ' . config('queue.default'));
        $this->line('  Connection: ' . config('queue.connections.' . config('queue.default') . '.connection', 'N/A'));
    }

    /**
     * Show overview
     */
    private function showOverview(): void
    {
        $this->info('Queue System Overview');
        $this->line('=====================');

        // Quick stats
        $stats = $this->queueService->getQueueStats();
        
        $pendingJobs = 0;
        $failedJobs = $stats['failed_jobs']['total_failed'] ?? 0;

        if (isset($stats['database']['pending'])) {
            $pendingJobs += $stats['database']['pending'];
        }

        if (isset($stats['redis'])) {
            $pendingJobs += array_sum($stats['redis']);
        }

        $this->line("Pending Jobs: {$pendingJobs}");
        $this->line("Failed Jobs: {$failedJobs}");
        $this->line("Queue Driver: " . config('queue.default'));

        $this->newLine();
        $this->line('Available Commands:');
        $this->line('  --stats          Show detailed queue statistics');
        $this->line('  --failed         Show failed jobs');
        $this->line('  --retry=<id>     Retry specific failed jobs');
        $this->line('  --clear-failed   Clear all failed jobs');
        $this->line('  --workers        Show worker status');

        $this->newLine();
        $this->line('Queue Management:');
        $this->line('  Start worker:    php artisan queue:work');
        $this->line('  Restart workers: php artisan queue:restart');
        $this->line('  Clear jobs:      php artisan queue:clear');
    }

    /**
     * Get queue worker processes (simplified implementation)
     *
     * @return array
     */
    private function getQueueWorkerProcesses(): array
    {
        // This is a simplified implementation
        // In a real environment, you might use system commands or monitoring tools
        
        try {
            $output = shell_exec('ps aux | grep "queue:work" | grep -v grep');
            
            if (!$output) {
                return [];
            }

            $lines = explode("\n", trim($output));
            $processes = [];

            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                $parts = preg_split('/\s+/', $line);
                
                if (count($parts) >= 11) {
                    $processes[] = [
                        'pid' => $parts[1],
                        'cpu' => $parts[2] . '%',
                        'memory' => $parts[3] . '%',
                        'started' => $parts[8],
                        'command' => implode(' ', array_slice($parts, 10))
                    ];
                }
            }

            return $processes;

        } catch (\Exception $e) {
            return [];
        }
    }
}
