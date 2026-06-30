<?php

namespace App\Models;

use App\Enums\EventType;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $fillable = [
        'header_id',
        'type',
        'name',
        'date',
        'consolidated',
        'note',
    ];

    protected $casts = [
        'type' => EventType::class,
        'date' => 'date',
        'consolidated' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(Header::class);
    }

    public function scopeConsolidated($query)
    {
        return $query->where('consolidated', true);
    }

    public function scopeUnconsolidated($query)
    {
        return $query->where('consolidated', false);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function isTransfer(): bool
    {
        return $this->type === EventType::Transfer;
    }
}
