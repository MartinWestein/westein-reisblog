<?php

namespace App\Models;

use App\Models\Concerns\RegistersMediaConversions;
use Database\Factories\NewsletterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Newsletter extends Model implements HasMedia
{
    /** @use HasFactory<NewsletterFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use RegistersMediaConversions;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const TEMPLATE_ANNOUNCEMENT = 'announcement';

    public const TEMPLATE_DIGEST = 'digest';

    public const TEMPLATE_PLAIN = 'plain';

    public const TEMPLATES = [
        self::TEMPLATE_ANNOUNCEMENT,
        self::TEMPLATE_DIGEST,
        self::TEMPLATE_PLAIN,
    ];

    protected $fillable = [
        'user_id',
        'subject',
        'body',
        'template',
        'status',
        'scheduled_at',
        'sent_at',
        'recipients_count',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'recipients_count' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'newsletter_sends')
            ->withPivot(['sent_at', 'failed_at', 'error', 'opened_at', 'bounced_at'])
            ->withTimestamps();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSending(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENDING);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('header')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversion('thumb', 400, $media, 'header');
        $this->registerWebpConversion('medium', 800, $media, 'header');
    }
}
