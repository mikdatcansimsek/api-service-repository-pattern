<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends CustomResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // Resource identifier (ID, type otomatik)
            ...$this->getResourceIdentifier(),

            // Genel kullanıcı bilgileri (herkese açık)
            'name' => $this->resource->name,
            'username' => $this->resource->username ?? null,

            // Email sadece kullanıcının kendisi görebilir
            'email' => $this->whenOwner($this->resource->email),

            // Profil bilgileri
            'profile' => [
                'avatar' => $this->resource->avatar_url ?? $this->getGravatarUrl(),
                'bio' => $this->resource->bio ?? null,
                'location' => $this->resource->location ?? null,
                'website' => $this->resource->website ?? null,
                'social_links' => $this->resource->social_links ?? []
            ],

            // Hesap durumu
            'account_status' => [
                'is_active' => $this->resource->is_active ?? true,
                'is_verified' => !is_null($this->resource->email_verified_at),
                'verification_date' => $this->when(
                    $this->resource->email_verified_at,
                    $this->formatDate($this->resource->email_verified_at)
                ),
                'status_text' => $this->getAccountStatusText()
            ],

            // Kullanıcı istatistikleri (eğer relation yüklenmişse)
            'statistics' => [
                'posts_count' => $this->resource->posts_count ?? 0,
                'products_count' => $this->resource->products_count ?? 0,
                'comments_count' => $this->resource->comments_count ?? 0,
                'followers_count' => $this->resource->followers_count ?? 0,
                'following_count' => $this->resource->following_count ?? 0
            ],

            // Özel bilgiler (sadece kullanıcının kendisi görebilir)
            'private_info' => $this->whenOwner([
                'phone' => $this->resource->phone ?? null,
                'date_of_birth' => $this->when(
                    $this->resource->date_of_birth,
                    $this->formatDate($this->resource->date_of_birth)['date'] ?? null
                ),
                'address' => $this->resource->address ?? null,
                'preferences' => $this->resource->preferences ?? [],
                'notification_settings' => $this->resource->notification_settings ?? []
            ]),

            // Admin bilgileri (sadece admin görebilir)
            'admin_info' => $this->whenAuth([
                'last_login_at' => $this->when(
                    $this->resource->last_login_at,
                    $this->formatDate($this->resource->last_login_at)
                ),
                'login_count' => $this->resource->login_count ?? 0,
                'ip_address' => $this->resource->last_login_ip ?? null,
                'user_agent' => $this->resource->last_user_agent ?? null,
                'roles' => $this->when(
                    $this->resource->relationLoaded('roles'),
                    $this->resource->roles->pluck('name') ?? []
                )
            ]),

            // Son aktivite
            'activity' => [
                'last_activity' => $this->when(
                    $this->resource->last_activity_at,
                    $this->formatDate($this->resource->last_activity_at)
                ),
                'is_online' => $this->isUserOnline(),
                'status' => $this->getUserActivityStatus()
            ],

            // API Links
            'links' => [
                'self' => route('api.users.show', $this->resource->id),
                'posts' => route('api.users.posts', $this->resource->id) ?? '#',
                'products' => route('api.users.products', $this->resource->id) ?? '#',
                'public_profile' => route('users.profile', $this->resource->username ?? $this->resource->id) ?? '#'
            ],

            // Timestamps (formatlanmış)
            ...$this->getTimestamps(),
        ];
    }

    /**
     * Gravatar URL oluştur
     */
    private function getGravatarUrl(): string
    {
        $email = $this->resource->email ?? '';
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=150";
    }

    /**
     * Hesap durumu metni
     */
    private function getAccountStatusText(): string
    {
        $isActive = $this->resource->is_active ?? true;
        $isVerified = !is_null($this->resource->email_verified_at ?? null);

        if (!$isActive) {
            return 'Hesap Devre Dışı';
        }

        if (!$isVerified) {
            return 'Email Doğrulanmamış';
        }

        return 'Aktif';
    }

    /**
     * Kullanıcının online olup olmadığını kontrol et
     */
    private function isUserOnline(): bool
    {
        if (!isset($this->resource->last_activity_at) || !$this->resource->last_activity_at) {
            return false;
        }

        return now()->diffInMinutes($this->resource->last_activity_at) < 5;
    }

    /**
     * Kullanıcı aktivite durumu
     */
    private function getUserActivityStatus(): string
    {
        if ($this->isUserOnline()) {
            return 'online';
        }

        if (!isset($this->resource->last_activity_at) || !$this->resource->last_activity_at) {
            return 'never_active';
        }

        $minutesAgo = now()->diffInMinutes($this->resource->last_activity_at);

        if ($minutesAgo < 60) {
            return 'recently_active';
        }

        if ($minutesAgo < 1440) { // 24 hours
            return 'today';
        }

        return 'offline';
    }

    /**
     * User için özel meta bilgileri
     */
    public function with(Request $request): array
    {
        return array_merge(parent::with($request), [
            'user_meta' => [
                'type' => 'user',
                'can_follow' => $this->isAuthenticated() && $this->getAuthUserId() !== $this->resource->id,
                'privacy_settings' => [
                    'show_email' => false,
                    'show_activity' => true,
                    'show_statistics' => true
                ]
            ]
        ]);
    }
}
