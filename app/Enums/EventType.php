<?php

namespace App\Enums;

enum EventType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';
    case ExpenseWithTransfer = 'expense_with_transfer';
    case IncomeWithTransfer = 'income_with_transfer';

    public static function filterTypes(string $filter): array
    {
        return match ($filter) {
            'income' => [self::Income->value, self::IncomeWithTransfer->value],
            'expense' => [self::Expense->value, self::ExpenseWithTransfer->value],
            'transfer' => [self::Transfer->value, self::IncomeWithTransfer->value, self::ExpenseWithTransfer->value],
            'expense_with_transfer' => [self::ExpenseWithTransfer->value],
            'income_with_transfer' => [self::IncomeWithTransfer->value],
            default => [],
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Income => 'bi-arrow-down-left',
            self::Expense => 'bi-arrow-up-right',
            self::Transfer => 'bi-arrow-left-right',
            self::ExpenseWithTransfer => 'bi-cart-plus',
            self::IncomeWithTransfer => 'bi-cash-coin',
        };
    }

    public function isTransfer(): bool
    {
        return $this === self::Transfer;
    }

    public function isComposite(): bool
    {
        return $this === self::ExpenseWithTransfer || $this === self::IncomeWithTransfer;
    }

    public function involvesTransfer(): bool
    {
        return $this === self::Transfer || $this->isComposite();
    }

    public function entryCount(): int
    {
        return match ($this) {
            self::Income, self::Expense => 1,
            self::Transfer => 2,
            self::ExpenseWithTransfer, self::IncomeWithTransfer => 3,
        };
    }

    public function entrySign(int $index): int
    {
        return match ($this) {
            self::Income => 1,
            self::Expense => -1,
            self::Transfer => $index === 0 ? -1 : 1,
            self::ExpenseWithTransfer => match ($index) {
                0 => -1,
                1 => 1,
                2 => -1,
                default => throw new \OutOfBoundsException("Invalid entry index {$index} for ExpenseWithTransfer"),
            },
            self::IncomeWithTransfer => match ($index) {
                0 => 1,
                1 => -1,
                2 => 1,
                default => throw new \OutOfBoundsException("Invalid entry index {$index} for IncomeWithTransfer"),
            },
        };
    }

    public function entryAssetSlot(int $index): string
    {
        return match ($this) {
            self::Income, self::Expense => 'source',
            self::Transfer => $index === 0 ? 'source' : 'destination',
            self::ExpenseWithTransfer => match ($index) {
                0 => 'source',
                1, 2 => 'destination',
                default => throw new \OutOfBoundsException("Invalid entry index {$index} for ExpenseWithTransfer"),
            },
            self::IncomeWithTransfer => match ($index) {
                0, 1 => 'source',
                2 => 'destination',
                default => throw new \OutOfBoundsException("Invalid entry index {$index} for IncomeWithTransfer"),
            },
        };
    }
}
