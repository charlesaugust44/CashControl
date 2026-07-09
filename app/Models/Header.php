<?php

namespace App\Models;

use App\Enums\EventRule;
use App\Enums\EventType;
use App\Models\Traits\Auditable;
use Database\Factories\HeaderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Header extends Model
{
    /** @use HasFactory<HeaderFactory> */
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'description',
        'type',
        'rule',
        'default_amount',
        'start_date',
        'end_date',
        'due_day',
        'asset_id',
        'destination_asset_id',
        'unity_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type' => EventType::class,
        'rule' => EventRule::class,
        'default_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'due_day' => 'integer',
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

    public function isTransfer(): bool
    {
        return $this->type->isTransfer();
    }

    public function isExpenseWithTransfer(): bool
    {
        return $this->type === EventType::ExpenseWithTransfer;
    }

    public function isIncomeWithTransfer(): bool
    {
        return $this->type === EventType::IncomeWithTransfer;
    }
}
