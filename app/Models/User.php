<?php

namespace App\Models;

use App\Models\Concerns\HasAvatarFallback;
use App\Models\Concerns\RegistersMediaConversions;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasAvatarFallback, HasFactory, HasRoles, InteractsWithMedia, Notifiable, RegistersMediaConversions, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'social_links',
        'deactivated_at',
        'deactivation_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'deactivated_at' => 'datetime',
        ];
    }

    /**
     * Scope: alleen actieve users (niet gedeactiveerd).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deactivated_at');
    }

    /**
     * Scope: alleen gedeactiveerde users.
     */
    public function scopeDeactivated(Builder $query): Builder
    {
        return $query->whereNotNull('deactivated_at');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversion('thumb', 100, $media, 'avatar');
        $this->registerWebpConversion('medium', 400, $media, 'avatar');
    }

    /**
     * URL naar de avatar-foto (avatar → thumb), of null als er geen foto is.
     */
    public function avatarUrl(): ?string
    {
        return $this->hasMedia('avatar')
            ? $this->getFirstMediaUrl('avatar', 'thumb')
            : null;
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function familyMember(): HasOne
    {
        return $this->hasOne(FamilyMember::class);
    }

    public function newsletters(): HasMany
    {
        return $this->hasMany(Newsletter::class);
    }
}
