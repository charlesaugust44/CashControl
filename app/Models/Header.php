<?php

namespace App\Models;

use App\Enums\EventType;
use Database\Factories\HeaderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Header extends Model
{
    /** @use HasFactory<HeaderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'rule',
        'default_amount',
        'start_date',
        'end_date',
        'asset_id',
        'destination_asset_id',
    ];

    protected $casts = [
        'type' => 'string',
        'rule' => 'string',
        'default_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function destinationAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'destination_asset_id');
    }

    public function isTransfer(): bool
    {
        return $this->type === EventType::Transfer->value;
    }
}
