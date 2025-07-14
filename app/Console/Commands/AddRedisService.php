<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddRedisService extends Command
{
    protected $signature = 'redis:add-service';
    protected $description = 'Instructions for adding Redis service to Railway';

    public function handle()
    {
        $this->info('🔧 Adding Redis Service to Railway');
        $this->info('');
        
        $this->info('📋 Steps to add Redis:');
        $this->info('1. Go to your Railway project dashboard');
        $this->info('2. Click "Add Service"');
        $this->info('3. Choose "Database" → "Add Redis"');
        $this->info('4. Railway will automatically provide REDIS_URL environment variable');
        $this->info('5. Your app will auto-detect and use Redis for caching');
        $this->info('');
        
        $this->info('🎯 Benefits of adding Redis:');
        $this->info('✅ Eliminates cache warnings in logs');
        $this->info('✅ Improves application performance');
        $this->info('✅ Better session management');
        $this->info('✅ Faster business settings lookup');
        $this->info('');
        
        if (env('REDIS_URL')) {
            $this->info('✅ Redis is already configured!');
            $this->info('REDIS_URL: ' . substr(env('REDIS_URL'), 0, 20) . '...');
        } else {
            $this->warn('⚠️ Redis not yet configured');
            $this->info('Add Redis service in Railway dashboard to enable caching');
        }
        
        return 0;
    }
}
