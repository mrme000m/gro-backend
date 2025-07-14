<?php

namespace App\Services;

use App\Model\BusinessSetting;
use App\Model\Category;
use App\Model\Product;
use App\Model\Branch;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CacheService
{
    // Cache TTL constants (in seconds)
    const CACHE_TTL_SHORT = 300;      // 5 minutes
    const CACHE_TTL_MEDIUM = 1800;    // 30 minutes  
    const CACHE_TTL_LONG = 3600;      // 1 hour
    const CACHE_TTL_VERY_LONG = 86400; // 24 hours

    // Cache key prefixes
    const PREFIX_PRODUCTS = 'products:';
    const PREFIX_CATEGORIES = 'categories:';
    const PREFIX_USERS = 'users:';
    const PREFIX_ORDERS = 'orders:';
    const PREFIX_SETTINGS = 'settings:';
    const PREFIX_API = 'api:';
    const PREFIX_DASHBOARD = 'dashboard:';

    /**
     * Get or cache business settings
     *
     * @param string $key
     * @return mixed
     */
    public function getBusinessSetting(string $key)
    {
        $cacheKey = self::PREFIX_SETTINGS . "business_setting:{$key}";

        try {
            return Cache::store('settings')->remember($cacheKey, self::CACHE_TTL_VERY_LONG, function () use ($key) {
                $setting = BusinessSetting::where('key', $key)->first();
                return $setting ? $setting->value : null;
            });
        } catch (\Exception $e) {
            // Fallback to direct database query if cache fails
            Log::warning('Cache failed for business setting, using database fallback', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            $setting = BusinessSetting::where('key', $key)->first();
            return $setting ? $setting->value : null;
        }
    }

    /**
     * Get or cache all business settings
     *
     * @return array
     */
    public function getAllBusinessSettings(): array
    {
        $cacheKey = self::PREFIX_SETTINGS . 'all_business_settings';
        
        return Cache::store('settings')->remember($cacheKey, self::CACHE_TTL_VERY_LONG, function () {
            return BusinessSetting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get or cache active categories
     *
     * @param bool $withSubcategories
     * @return \Illuminate\Support\Collection
     */
    public function getActiveCategories(bool $withSubcategories = false): \Illuminate\Support\Collection
    {
        $cacheKey = self::PREFIX_CATEGORIES . 'active_categories:' . ($withSubcategories ? 'with_sub' : 'main_only');
        
        return Cache::store('products')->remember($cacheKey, self::CACHE_TTL_LONG, function () use ($withSubcategories) {
            $query = Category::where('status', 1);
            
            if (!$withSubcategories) {
                $query->where('parent_id', 0);
            }
            
            return $query->orderBy('priority', 'desc')
                        ->orderBy('id', 'desc')
                        ->get(['id', 'name', 'image', 'parent_id', 'priority']);
        });
    }

    /**
     * Get or cache featured products
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getFeaturedProducts(int $limit = 10): \Illuminate\Support\Collection
    {
        $cacheKey = self::PREFIX_PRODUCTS . "featured_products:{$limit}";
        
        return Cache::store('products')->remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($limit) {
            return Product::active()
                ->where('is_featured', 1)
                ->with([
                    'rating' => function($query) {
                        $query->select('product_id', DB::raw('AVG(rating) as average'));
                    }
                ])
                ->withCount(['wishlist as wishlist_count'])
                ->select([
                    'id', 'name', 'description', 'image', 'price', 'discount', 
                    'discount_type', 'tax', 'tax_type', 'unit', 'total_stock',
                    'capacity', 'status', 'created_at'
                ])
                ->orderBy('id', 'desc')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get or cache daily need products
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getDailyNeedProducts(int $limit = 10): \Illuminate\Support\Collection
    {
        $cacheKey = self::PREFIX_PRODUCTS . "daily_need_products:{$limit}";
        
        return Cache::store('products')->remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($limit) {
            return Product::active()
                ->where('daily_needs', 1)
                ->with([
                    'rating' => function($query) {
                        $query->select('product_id', DB::raw('AVG(rating) as average'));
                    }
                ])
                ->withCount(['wishlist as wishlist_count'])
                ->select([
                    'id', 'name', 'description', 'image', 'price', 'discount', 
                    'discount_type', 'tax', 'tax_type', 'unit', 'total_stock',
                    'capacity', 'status', 'created_at'
                ])
                ->orderBy('id', 'desc')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get or cache product by ID with relationships
     *
     * @param int $productId
     * @return Product|null
     */
    public function getProductById(int $productId): ?Product
    {
        $cacheKey = self::PREFIX_PRODUCTS . "product_details:{$productId}";
        
        return Cache::store('products')->remember($cacheKey, self::CACHE_TTL_LONG, function () use ($productId) {
            return Product::with([
                'rating',
                'active_reviews' => function($query) {
                    $query->with('customer:id,f_name,l_name,image')
                          ->latest()
                          ->take(10);
                },
                'tags'
            ])
            ->withCount(['wishlist as wishlist_count', 'active_reviews as reviews_count'])
            ->find($productId);
        });
    }

    /**
     * Get or cache active branches
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveBranches(): \Illuminate\Support\Collection
    {
        $cacheKey = self::PREFIX_SETTINGS . 'active_branches';
        
        return Cache::store('settings')->remember($cacheKey, self::CACHE_TTL_LONG, function () {
            return Branch::where('status', 1)
                ->select(['id', 'name', 'email', 'longitude', 'latitude', 'address', 'coverage', 'status'])
                ->get();
        });
    }

    /**
     * Get or cache user profile data
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserProfile(int $userId): ?User
    {
        $cacheKey = self::PREFIX_USERS . "profile:{$userId}";
        
        return Cache::store('users')->remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($userId) {
            return User::with(['addresses'])
                ->find($userId, [
                    'id', 'f_name', 'l_name', 'email', 'phone', 'image', 
                    'loyalty_point', 'wallet_balance', 'referral_code'
                ]);
        });
    }

    /**
     * Cache API response
     *
     * @param string $endpoint
     * @param array $params
     * @param mixed $data
     * @param int $ttl
     * @return mixed
     */
    public function cacheApiResponse(string $endpoint, array $params, $data, int $ttl = self::CACHE_TTL_SHORT)
    {
        $cacheKey = self::PREFIX_API . $endpoint . ':' . md5(serialize($params));
        
        return Cache::store('api')->put($cacheKey, $data, $ttl);
    }

    /**
     * Get cached API response
     *
     * @param string $endpoint
     * @param array $params
     * @return mixed
     */
    public function getCachedApiResponse(string $endpoint, array $params)
    {
        $cacheKey = self::PREFIX_API . $endpoint . ':' . md5(serialize($params));
        
        return Cache::store('api')->get($cacheKey);
    }

    /**
     * Invalidate product-related caches
     *
     * @param int|null $productId
     * @return void
     */
    public function invalidateProductCaches(?int $productId = null): void
    {
        try {
            // Clear general product caches
            Cache::store('products')->forget(self::PREFIX_PRODUCTS . 'featured_products:10');
            Cache::store('products')->forget(self::PREFIX_PRODUCTS . 'daily_need_products:10');
            
            // Clear specific product cache if ID provided
            if ($productId) {
                Cache::store('products')->forget(self::PREFIX_PRODUCTS . "product_details:{$productId}");
            }
            
            // Clear API caches related to products
            $this->clearCacheByPattern('api', self::PREFIX_API . 'products:*');
            
            Log::info('Product caches invalidated', ['product_id' => $productId]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate product caches', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Invalidate category-related caches
     *
     * @return void
     */
    public function invalidateCategoryCaches(): void
    {
        try {
            Cache::store('products')->forget(self::PREFIX_CATEGORIES . 'active_categories:with_sub');
            Cache::store('products')->forget(self::PREFIX_CATEGORIES . 'active_categories:main_only');
            
            Log::info('Category caches invalidated');
        } catch (\Exception $e) {
            Log::error('Failed to invalidate category caches', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Invalidate user-related caches
     *
     * @param int $userId
     * @return void
     */
    public function invalidateUserCaches(int $userId): void
    {
        try {
            Cache::store('users')->forget(self::PREFIX_USERS . "profile:{$userId}");
            
            Log::info('User caches invalidated', ['user_id' => $userId]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate user caches', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Invalidate settings caches
     *
     * @param string|null $settingKey
     * @return void
     */
    public function invalidateSettingsCaches(?string $settingKey = null): void
    {
        try {
            if ($settingKey) {
                Cache::store('settings')->forget(self::PREFIX_SETTINGS . "business_setting:{$settingKey}");
            }
            
            Cache::store('settings')->forget(self::PREFIX_SETTINGS . 'all_business_settings');
            Cache::store('settings')->forget(self::PREFIX_SETTINGS . 'active_branches');
            
            Log::info('Settings caches invalidated', ['setting_key' => $settingKey]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate settings caches', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Clear all application caches
     *
     * @return void
     */
    public function clearAllCaches(): void
    {
        try {
            Cache::store('products')->flush();
            Cache::store('users')->flush();
            Cache::store('orders')->flush();
            Cache::store('settings')->flush();
            Cache::store('api')->flush();
            Cache::flush(); // Default cache store
            
            Log::info('All application caches cleared');
        } catch (\Exception $e) {
            Log::error('Failed to clear all caches', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Clear cache by pattern (Redis only)
     *
     * @param string $store
     * @param string $pattern
     * @return void
     */
    private function clearCacheByPattern(string $store, string $pattern): void
    {
        try {
            // Check if Redis is available and configured
            if (config('cache.default') !== 'redis') {
                Log::info('Pattern cache clearing skipped - Redis not configured');
                return;
            }

            $redis = Cache::store($store)->getRedis();
            $keys = $redis->keys($pattern);

            if (!empty($keys)) {
                $redis->del($keys);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache by pattern', [
                'store' => $store,
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        try {
            // Check if Redis is available
            if (config('cache.default') !== 'redis') {
                return ['message' => 'Redis not configured - cache statistics unavailable'];
            }

            $stats = [];
            $stores = ['products', 'users', 'orders', 'settings', 'api'];

            foreach ($stores as $store) {
                try {
                    $redis = Cache::store($store)->getRedis();
                    $info = $redis->info('memory');

                    $stats[$store] = [
                        'memory_used' => $info['used_memory_human'] ?? 'N/A',
                        'keys_count' => $redis->dbsize(),
                    ];
                } catch (\Exception $e) {
                    $stats[$store] = [
                        'memory_used' => 'Error',
                        'keys_count' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to retrieve cache statistics'];
        }
    }
}
