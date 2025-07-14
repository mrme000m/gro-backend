<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDefaultAdmin extends Command
{
    protected $signature = 'admin:create-default';
    protected $description = 'Create default admin user for Restaurant Bazar';

    public function handle()
    {
        $this->info('🔐 Creating Default Admin User...');

        try {
            // Check if admin already exists
            $existingAdmin = DB::table('admins')->where('email', 'admin@restaurantbazar.com')->first();
            
            if ($existingAdmin) {
                $this->info('✅ Default admin already exists!');
                $this->displayCredentials();
                return 0;
            }

            // Create default admin
            $adminId = DB::table('admins')->insertGetId([
                'f_name' => 'Restaurant',
                'l_name' => 'Admin',
                'phone' => '+1234567890',
                'email' => 'admin@restaurantbazar.com',
                'image' => 'def.png',
                'password' => Hash::make('admin123'),
                'role_id' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($adminId) {
                $this->info('✅ Default admin user created successfully!');
                $this->displayCredentials();
                
                // Also create some basic business settings if they don't exist
                $this->createBasicSettings();
                
                return 0;
            } else {
                $this->error('❌ Failed to create admin user');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error creating admin: ' . $e->getMessage());
            return 1;
        }
    }

    private function displayCredentials()
    {
        $this->info('');
        $this->info('🎯 DEFAULT ADMIN CREDENTIALS:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📧 Email:    admin@restaurantbazar.com');
        $this->info('🔑 Password: admin123');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('');
        $this->info('🌐 Admin Login URL:');
        $this->info('https://your-domain.railway.app/admin');
        $this->info('');
        $this->warn('⚠️  IMPORTANT: Change the password after first login!');
        $this->info('');
    }

    private function createBasicSettings()
    {
        try {
            $basicSettings = [
                ['key' => 'restaurant_name', 'value' => 'Restaurant Bazar'],
                ['key' => 'restaurant_logo', 'value' => 'def.png'],
                ['key' => 'mail_config', 'value' => json_encode([
                    'status' => 0,
                    'name' => 'Restaurant Bazar',
                    'host' => 'smtp.gmail.com',
                    'driver' => 'smtp',
                    'port' => '587',
                    'username' => '',
                    'email_id' => 'admin@restaurantbazar.com',
                    'encryption' => 'tls',
                    'password' => ''
                ])],
                ['key' => 'currency_symbol', 'value' => '$'],
                ['key' => 'currency_symbol_position', 'value' => 'left'],
                ['key' => 'system_default_currency', 'value' => 'USD'],
                ['key' => 'decimal_point_settings', 'value' => '2'],
                ['key' => 'time_zone', 'value' => 'UTC'],
                ['key' => 'time_format', 'value' => '24'],
                ['key' => 'phone', 'value' => '+1234567890'],
                ['key' => 'email', 'value' => 'admin@restaurantbazar.com'],
                ['key' => 'address', 'value' => 'Your Restaurant Address'],
                ['key' => 'country', 'value' => 'US'],
                ['key' => 'pagination_limit', 'value' => '25'],
                ['key' => 'order_pending_message', 'value' => json_encode(['status' => 1, 'message' => 'Your order has been placed successfully'])],
                ['key' => 'order_confirmation_msg', 'value' => json_encode(['status' => 1, 'message' => 'Your order has been confirmed'])],
                ['key' => 'order_processing_message', 'value' => json_encode(['status' => 1, 'message' => 'Your order is being processed'])],
                ['key' => 'out_for_delivery_message', 'value' => json_encode(['status' => 1, 'message' => 'Your order is out for delivery'])],
                ['key' => 'order_delivered_message', 'value' => json_encode(['status' => 1, 'message' => 'Your order has been delivered'])],
                ['key' => 'delivery_boy_assign_message', 'value' => json_encode(['status' => 1, 'message' => 'A delivery boy has been assigned for your order'])],
                ['key' => 'delivery_boy_start_message', 'value' => json_encode(['status' => 1, 'message' => 'Delivery boy is on the way'])],
                ['key' => 'delivery_boy_delivered_message', 'value' => json_encode(['status' => 1, 'message' => 'Order delivered successfully'])],
                ['key' => 'terms_and_conditions', 'value' => 'Terms and conditions content here'],
                ['key' => 'privacy_policy', 'value' => 'Privacy policy content here'],
                ['key' => 'about_us', 'value' => 'About us content here']
            ];

            foreach ($basicSettings as $setting) {
                $exists = DB::table('business_settings')->where('key', $setting['key'])->exists();
                if (!$exists) {
                    DB::table('business_settings')->insert([
                        'key' => $setting['key'],
                        'value' => $setting['value'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            $this->info('✅ Basic business settings created');

        } catch (\Exception $e) {
            $this->warn('⚠️ Could not create basic settings: ' . $e->getMessage());
        }
    }
}
