<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAdminDashboard extends Command
{
    protected $signature = 'admin:fix-dashboard';
    protected $description = 'Fix admin dashboard missing data issues';

    public function handle()
    {
        $this->info('🔧 Fixing Admin Dashboard Issues...');

        try {
            // Fix missing business settings that cause dashboard errors
            $this->fixBusinessSettings();
            
            // Ensure admin user has proper data
            $this->fixAdminUserData();
            
            // Create missing default data
            $this->createDefaultData();
            
            $this->info('✅ Admin dashboard issues fixed!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing dashboard: ' . $e->getMessage());
            return 1;
        }
    }

    private function fixBusinessSettings()
    {
        $this->info('📝 Fixing business settings...');
        
        $requiredSettings = [
            'restaurant_name' => 'Restaurant Bazar',
            'restaurant_logo' => 'def.png',
            'logo' => 'def.png',
            'fav_icon' => 'def.png',
            'currency_symbol' => '$',
            'currency_symbol_position' => 'left',
            'system_default_currency' => 'USD',
            'decimal_point_settings' => '2',
            'time_zone' => 'UTC',
            'time_format' => '24',
            'phone' => '+1234567890',
            'email' => 'admin@4restaurants.store',
            'address' => 'Your Restaurant Address',
            'country' => 'US',
            'pagination_limit' => '25',
            'footer_text' => 'Copyright © 2025 Restaurant Bazar. All rights reserved.',
            'app_name' => 'Restaurant Bazar',
            'company_name' => 'Restaurant Bazar',
            'terms_and_conditions' => '<p>Terms and conditions content here</p>',
            'privacy_policy' => '<p>Privacy policy content here</p>',
            'about_us' => '<p>About us content here</p>',
            'maintenance_mode' => '0',
            'language' => json_encode([['id' => 1, 'name' => 'English', 'code' => 'en', 'status' => 1, 'default' => true]]),
            'mail_config' => json_encode([
                'status' => 0,
                'name' => 'Restaurant Bazar',
                'host' => 'smtp.gmail.com',
                'driver' => 'smtp',
                'port' => '587',
                'username' => '',
                'email_id' => 'admin@4restaurants.store',
                'encryption' => 'tls',
                'password' => ''
            ])
        ];

        foreach ($requiredSettings as $key => $value) {
            $exists = DB::table('business_settings')->where('key', $key)->exists();
            if (!$exists) {
                DB::table('business_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->info("✅ Added setting: {$key}");
            }
        }
    }

    private function fixAdminUserData()
    {
        $this->info('👤 Fixing admin user data...');
        
        // Ensure admin user has all required fields
        $admin = DB::table('admins')->where('email', 'admin@4restaurants.store')->first();
        
        if ($admin) {
            $updateData = [];
            
            if (empty($admin->f_name)) {
                $updateData['f_name'] = 'Restaurant';
            }
            if (empty($admin->l_name)) {
                $updateData['l_name'] = 'Admin';
            }
            if (empty($admin->phone)) {
                $updateData['phone'] = '+1234567890';
            }
            if (empty($admin->image)) {
                $updateData['image'] = 'def.png';
            }
            if (!isset($admin->role_id) || $admin->role_id == 0) {
                $updateData['role_id'] = 1;
            }
            if (!isset($admin->status)) {
                $updateData['status'] = 1;
            }
            
            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                DB::table('admins')->where('id', $admin->id)->update($updateData);
                $this->info('✅ Updated admin user data');
            }
        }
    }

    private function createDefaultData()
    {
        $this->info('📊 Creating default data...');
        
        // Ensure admin_roles table has master admin role
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

        // Create default branch if none exists
        $branchExists = DB::table('branches')->exists();
        if (!$branchExists) {
            DB::table('branches')->insert([
                'name' => 'Main Branch',
                'email' => 'admin@4restaurants.store',
                'password' => bcrypt('admin123'),
                'latitude' => '40.7128',
                'longitude' => '-74.0060',
                'address' => 'Main Restaurant Location',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info('✅ Created default branch');
        }

        // Create default category if none exists
        $categoryExists = DB::table('categories')->exists();
        if (!$categoryExists) {
            DB::table('categories')->insert([
                'name' => 'General',
                'image' => 'def.png',
                'parent_id' => 0,
                'position' => 1,
                'status' => 1,
                'priority' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info('✅ Created default category');
        }
    }
}
