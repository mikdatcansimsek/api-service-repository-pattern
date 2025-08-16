<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin HasApiTokens
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Computed Properties - Performance optimization
     */
    
    /**
     * Gravatar URL'sini cache'le
     */
    public function getGravatarUrlAttribute(): string
    {
        return Cache::remember("user.{$this->id}.gravatar", 3600, function() {
            $email = $this->email ?? '';
            $hash = md5(strtolower(trim($email)));
            return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=150";
        });
    }

    /**
     * Kullanıcının online durumunu cache'le
     */
    public function getIsOnlineAttribute(): bool
    {
        if (!isset($this->last_activity_at) || !$this->last_activity_at) {
            return false;
        }

        return now()->diffInMinutes($this->last_activity_at) < 5;
    }

    /**
     * Hesap durumu metni
     */
    public function getAccountStatusTextAttribute(): string
    {
        $isActive = $this->is_active ?? true;
        $isVerified = !is_null($this->email_verified_at ?? null);

        if (!$isActive) {
            return 'Hesap Devre Dışı';
        }

        if (!$isVerified) {
            return 'Email Doğrulanmamış';
        }

        return 'Aktif';
    }

    /**
     * Cache temizleme
     */
    protected static function boot()
    {
        parent::boot();
        
        static::updated(function ($user) {
            Cache::forget("user.{$user->id}.gravatar");
        });
    }
}
