<?php

namespace App\Models;

use Database\Factories\RouteWaypointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteWaypoint extends Model
{
    /** @use HasFactory<RouteWaypointFactory> */
    use HasFactory;

    protected $fillable = [
        'route_id',
        'location_id',
        'order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
