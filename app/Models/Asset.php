<?php

namespace App\Models;

use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'balance',
        'closed_up_to',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'closed_up_to' => 'date',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function headers(): HasMany
    {
        return $this->hasMany(Header::class);
    }

    public function destinationHeaders(): HasMany
    {
        return $this->hasMany(Header::class, 'destination_asset_id');
    }
}
