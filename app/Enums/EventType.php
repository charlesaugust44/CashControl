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
}
