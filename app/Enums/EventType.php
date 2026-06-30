<?php

namespace App\Enums;

enum EventType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';
    case ExpenseWithTransfer = 'expense_with_transfer';
}
