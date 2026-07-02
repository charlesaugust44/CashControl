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
        'due_day',
        'consolidated',
        'transfer_consolidated',
        'note',
    ];

    protected $casts = [
        'type' => EventType::class,
        'date' => 'date',
        'due_day' => 'integer',
        'consolidated' => 'boolean',
        'transfer_consolidated' => 'boolean',
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

    public function isExpenseWithTransfer(): bool
    {
        return $this->type === EventType::ExpenseWithTransfer;
    }

    public function isIncomeWithTransfer(): bool
    {
        return $this->type === EventType::IncomeWithTransfer;
    }

    public function isComposite(): bool
    {
        return $this->isExpenseWithTransfer() || $this->isIncomeWithTransfer();
    }

    public function isPartiallyConsolidated(): bool
    {
        if (!$this->isComposite()) {
            return false;
        }

        return $this->consolidated !== $this->transfer_consolidated;
    }

    public function isFullyConsolidated(): bool
    {
        if ($this->isComposite()) {
            return $this->consolidated && $this->transfer_consolidated;
        }

        return $this->consolidated;
    }

    public function isPending(): bool
    {
        if ($this->isComposite()) {
            return !$this->consolidated && !$this->transfer_consolidated;
        }

        return !$this->consolidated;
    }

    public function getTransferEntryIndices(): array
    {
        if ($this->isExpenseWithTransfer()) {
            return [0, 1];
        }

        if ($this->isIncomeWithTransfer()) {
            return [1, 2];
        }

        return [];
    }

    public function getIncomeExpenseEntryIndices(): array
    {
        if ($this->isExpenseWithTransfer()) {
            return [2];
        }

        if ($this->isIncomeWithTransfer()) {
            return [0];
        }

        return [];
    }
}
