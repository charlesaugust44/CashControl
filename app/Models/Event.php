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

    public function isComposite(): bool
    {
        return $this->type->isComposite();
    }

    public function isPartiallyConsolidated(): bool
    {
        if (! $this->isComposite()) {
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
            return ! $this->consolidated && ! $this->transfer_consolidated;
        }

        return ! $this->consolidated;
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

    public function getSourceEntry(): ?Entry
    {
        return $this->entries->first(fn ($e) => $e->amount < 0);
    }

    public function getDestEntry(): ?Entry
    {
        return $this->entries->first(fn ($e) => $e->amount > 0);
    }

    public function getLastPositiveEntry(): ?Entry
    {
        return $this->entries->last(fn ($e) => $e->amount > 0);
    }

    public function getExpenseEntry(): ?Entry
    {
        if (! $this->isExpenseWithTransfer()) {
            return null;
        }

        return $this->entries->last();
    }

    public function getIncomeEntry(): ?Entry
    {
        if (! $this->isIncomeWithTransfer()) {
            return null;
        }

        return $this->entries->first(fn ($e) => $e->amount > 0);
    }

    public function getTransferAmount(): float
    {
        $destEntry = $this->getDestEntry();

        return $destEntry ? abs($destEntry->amount) : 0;
    }

    public function getDisplayAmount(): float
    {
        return match (true) {
            $this->isTransfer() => $this->getTransferAmount(),
            $this->isExpenseWithTransfer() => abs($this->getExpenseEntry()?->amount ?? 0),
            $this->isIncomeWithTransfer() => abs($this->getIncomeEntry()?->amount ?? 0),
            $this->type === EventType::Expense => abs($this->entries->sum('amount')),
            $this->type === EventType::Income => $this->entries->where('amount', '>', 0)->sum('amount'),
            default => 0,
        };
    }

    public function getIncomeAmount(): float
    {
        if ($this->isIncomeWithTransfer()) {
            return abs($this->getIncomeEntry()?->amount ?? 0);
        }

        if ($this->type === EventType::Income) {
            return $this->entries->where('amount', '>', 0)->sum('amount');
        }

        return 0;
    }

    public function getExpenseAmount(): float
    {
        if ($this->isExpenseWithTransfer()) {
            return abs($this->getExpenseEntry()?->amount ?? 0);
        }

        if ($this->type === EventType::Expense) {
            return abs($this->entries->sum('amount'));
        }

        return 0;
    }

    public function detailUrl(): string
    {
        $isVirtual = $this->id === 0 || $this->id === null;

        if ($isVirtual) {
            return url('/entries/virtual/'.$this->header_id.'/'.$this->date->format('Y').'/'.$this->date->format('m'));
        }

        return url('/entries/'.$this->id);
    }
}
