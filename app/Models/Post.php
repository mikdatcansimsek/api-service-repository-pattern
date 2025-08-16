<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'user_id',
        'category_id',
        'published_at',
        'is_published',
    ];
    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }


    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Computed Properties - Cache'li hesaplamalar
     */
    
    /**
     * Okuma süresini hesapla ve cache'le
     */
    public function getReadingTimeAttribute(): array
    {
        return Cache::remember("post.{$this->id}.reading_time", 3600, function() {
            $wordCount = str_word_count(strip_tags($this->content ?? ''));
            $readingSpeed = 200; // Dakikada kelime
            $minutes = ceil($wordCount / $readingSpeed);

            return [
                'word_count' => $wordCount,
                'estimated_minutes' => $minutes,
                'reading_time_text' => $minutes . ' dk okuma'
            ];
        });
    }

    /**
     * Kullanıcı etkileşimlerini cache'le - Service'den set edilecek
     */
    public function getUserInteractionAttribute(): array
    {
        return $this->user_interaction_data ?? [
            'is_liked' => false,
            'is_bookmarked' => false,
            'user_rating' => null,
            'can_edit' => false,
            'can_delete' => false
        ];
    }

    /**
     * Admin bilgilerini cache'le - Service'den set edilecek  
     */
    public function getAdminDataAttribute(): array
    {
        return $this->admin_data_cache ?? [
            'is_featured' => $this->is_featured ?? false,
            'is_pinned' => $this->is_pinned ?? false,
            'moderation_status' => $this->moderation_status ?? 'approved',
            'admin_notes' => $this->admin_notes
        ];
    }

    /**
     * Post statistics cache - Service'den set edilecek
     */
    public function getStatisticsAttribute(): array
    {
        return Cache::remember("post.{$this->id}.statistics", 300, function() {
            return [
                'views_count' => $this->views_count ?? 0,
                'likes_count' => $this->likes_count ?? 0,
                'comments_count' => $this->comments_count ?? 0,
                'shares_count' => $this->shares_count ?? 0,
            ];
        });
    }

    /**
     * Cache temizleme - Post güncellendiğinde
     */
    protected static function boot()
    {
        parent::boot();
        
        static::updated(function ($post) {
            Cache::forget("post.{$post->id}.reading_time");
            Cache::forget("post.{$post->id}.statistics");
        });
        
        static::deleted(function ($post) {
            Cache::forget("post.{$post->id}.reading_time");
            Cache::forget("post.{$post->id}.statistics");
        });
    }

    /**
     * Relations for eager loading
     */
    public function likes()
    {
        return $this->hasMany(\App\Models\Like::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(\App\Models\Bookmark::class);
    }
}
