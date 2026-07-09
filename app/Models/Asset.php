<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'balance',
        'closed_up_to',
        'unity_id',
        'created_by',
        'updated_by',
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

    public function unity(): BelongsTo
    {
        return $this->belongsTo(Unity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForUnity($query, int $unityId)
    {
        return $query->where('unity_id', $unityId);
    }
}
