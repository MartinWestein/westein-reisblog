<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'approved', 'rejected', 'spam'];

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'body',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Comment $comment) {
            // Validatie: parent moet zelf top-level zijn (1 niveau diep)
            if (! is_null($comment->parent_id)) {
                $parent = Comment::find($comment->parent_id);

                if ($parent && ! is_null($parent->parent_id)) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Reacties kunnen maximaal één niveau diep genest worden.',
                    ]);
                }
            }

            // Auto-status op basis van rol van de auteur
            if (empty($comment->status)) {
                $user = $comment->user_id ? User::find($comment->user_id) : null;

                if ($user && $user->hasAnyRole(['admin', 'editor'])) {
                    $comment->status = 'approved';
                    $comment->approved_at = now();
                } else {
                    $comment->status = 'pending';
                }
            }
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function moderate(string $status): void
    {
        $this->status = $status;
        $this->approved_at = $status === 'approved' ? now() : null;
        $this->save();
    }
}
