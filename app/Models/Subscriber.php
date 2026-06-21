<?php

namespace App\Models;

use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $fillable = [
        'email',
        'name',
        'confirmation_token',
        'confirmed_at',
        'unsubscribe_token',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }

            if (empty($subscriber->confirmation_token) && empty($subscriber->confirmed_at)) {
                $subscriber->confirmation_token = Str::random(64);
            }
        });
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function scopeUnsubscribed(Builder $query): Builder
    {
        return $query->whereNotNull('unsubscribed_at');
    }

    public function isConfirmed(): bool
    {
        return ! is_null($this->confirmed_at);
    }

    public function isUnsubscribed(): bool
    {
        return ! is_null($this->unsubscribed_at);
    }

    public function status(): string
    {
        return match (true) {
            $this->isUnsubscribed() => self::STATUS_UNSUBSCRIBED,
            $this->isConfirmed() => self::STATUS_ACTIVE,
            default => self::STATUS_PENDING,
        };
    }
}
