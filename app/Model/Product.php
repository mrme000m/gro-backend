<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $casts = [
        'tax'         => 'float',
        'price'       => 'float',
        'capacity'    => 'float',
        'status'      => 'integer',
        'discount'    => 'float',
        'total_stock' => 'integer',
        'set_menu'    => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'is_featured'  => 'integer',
        'image_data'  => 'array',
    ];

    public function translations(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany('App\Model\Translation', 'translationable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function active_reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class)->where(['is_active' => 1])->latest();
    }

    public function wishlist(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wishlist::class)->latest();
    }

    public function rating(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class)
            ->where('is_active', 1)
            ->select(DB::raw('avg(rating) average, product_id'))
            ->groupBy('product_id');
    }

    public function all_rating(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class)
            ->select(DB::raw('avg(rating) average, product_id'))
            ->groupBy('product_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query){
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }

    public function order_details(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getIdentityImageFullPathAttribute()
    {
        $value = $this->image ?? [];
        $imageUrlArray = is_array($value) ? $value : json_decode($value, true);
        if (is_array($imageUrlArray)) {
            foreach ($imageUrlArray as $key => $item) {
                if (Storage::disk('public')->exists('product/' . $item)) {
                    $imageUrlArray[$key] = asset('storage/app/public/product/'. $item) ;
                } else {
                    $imageUrlArray[$key] = asset('public/assets/admin/img/160x160/2.png');
                }
            }
        }
        return $imageUrlArray;
    }

    /**
     * Get optimized image URL
     *
     * @param string $size
     * @param bool $preferWebP
     * @return string
     */
    public function getOptimizedImageUrl(string $size = 'medium', bool $preferWebP = true): string
    {
        $cdnService = app(\App\Services\CdnService::class);
        return $cdnService->getOptimizedImageUrl('product', $this->image, $size, $preferWebP);
    }

    /**
     * Get responsive image data for API
     *
     * @return array
     */
    public function getResponsiveImageData(): array
    {
        $lazyLoadingService = app(\App\Services\LazyLoadingService::class);
        return $lazyLoadingService->generateResponsiveImageData('product', $this->image, [
            'sizes' => ['thumbnail', 'small', 'medium', 'large'],
            'include_webp' => true
        ]);
    }

    /**
     * Get image URLs for all sizes
     *
     * @return array
     */
    public function getImageUrls(): array
    {
        $sizes = ['thumbnail', 'small', 'medium', 'large'];
        $urls = [];

        foreach ($sizes as $size) {
            $urls[$size] = $this->getOptimizedImageUrl($size, false);
            $urls[$size . '_webp'] = $this->getOptimizedImageUrl($size, true);
        }

        return $urls;
    }

    /**
     * Generate lazy loading HTML for product image
     *
     * @param array $options
     * @return string
     */
    public function getLazyImageHtml(array $options = []): string
    {
        $lazyLoadingService = app(\App\Services\LazyLoadingService::class);

        $defaultOptions = [
            'alt' => $this->name,
            'class' => 'product-image lazy-image',
            'sizes' => ['small', 'medium', 'large'],
            'default_size' => 'medium',
        ];

        $options = array_merge($defaultOptions, $options);

        return $lazyLoadingService->generateLazyImage('product', $this->image, $options);
    }
}
