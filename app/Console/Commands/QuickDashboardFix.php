<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QuickDashboardFix extends Command
{
    protected $signature = 'admin:quick-fix';
    protected $description = 'Quick fix for admin dashboard role relationship';

    public function handle()
    {
        $this->info('🔧 Quick Dashboard Fix...');

        try {
            // Ensure admin_roles table has the master admin role
            $masterRole = DB::table('admin_roles')->where('id', 1)->first();
            if (!$masterRole) {
                DB::table('admin_roles')->insert([
                    'id' => 1,
                    'name' => 'Master Admin',
                    'module_access' => null,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->info('✅ Created Master Admin role');
            }

            // Update admin user to have proper role_id
            $admin = DB::table('admins')->where('email', 'admin@4restaurants.store')->first();
            if ($admin && (!isset($admin->role_id) || $admin->role_id == 0)) {
                DB::table('admins')->where('id', $admin->id)->update([
                    'role_id' => 1,
                    'updated_at' => now()
                ]);
                $this->info('✅ Updated admin role_id');
            }

            // Clear view cache to force template recompilation
            $this->call('view:clear');
            $this->info('✅ Cleared view cache');

            $this->info('🎉 Quick fix completed! Try refreshing the admin dashboard.');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
