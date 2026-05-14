<?php

namespace App\Models;

use Database\Factories\NewsletterSendFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSend extends Model
{
    /** @use HasFactory<NewsletterSendFactory> */
    use HasFactory;

    protected $fillable = [
        'newsletter_id',
        'subscriber_id',
        'sent_at',
        'failed_at',
        'error',
        'opened_at',
        'bounced_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'opened_at' => 'datetime',
            'bounced_at' => 'datetime',
        ];
    }

    public function newsletter(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}
