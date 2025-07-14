<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'meta_title',
        'meta_description',
        'status',
        'published_at',
        'author_id',
        'category',
        'tags',
        'view_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
        'status' => 'boolean',
        'view_count' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Scope a query to only include published blogs.
     */
    public function scopePublished($query)
    {
        return $query->where('status', true)
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include draft blogs.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', false);
    }

    /**
     * Get the author of the blog.
     */
    public function author()
    {
        return $this->belongsTo(\App\Model\Admin::class, 'author_id');
    }

    /**
     * Get the blog's featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): string
    {
        if ($this->featured_image) {
            return asset('storage/app/public/blog/' . $this->featured_image);
        }
        return asset('assets/admin/img/blog-placeholder.jpg');
    }

    /**
     * Get the blog's excerpt or truncated content.
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        return \Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get the blog's reading time estimate.
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // Assuming 200 words per minute
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Generate a unique slug for the blog.
     */
    public static function generateSlug(string $title, ?int $id = null): string
    {
        $slug = \Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($id, function ($query, $id) {
            return $query->where('id', '!=', $id);
        })->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
