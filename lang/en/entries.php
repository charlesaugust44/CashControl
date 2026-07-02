<?php

return [
    'title' => 'Entries',
    'singular' => 'Entry',
    'create' => 'New Entry',
    'no_entries' => 'No events for this month',
    'balance' => 'Balance',
    'forecasted' => 'Forecasted',
    'consolidated' => 'Consolidated',

    'status' => [
        'forecast' => 'Forecast',
        'consolidated' => 'Consolidated',
        'partial' => 'Partial',
        'pending' => 'Pending',
        'closed' => 'Closed',
        'month_closed' => 'Month Closed',
    ],

    'actions' => [
        'paid' => 'Paid',
        'received' => 'Received',
        'transferred' => 'Transferred',
        'revert' => 'Revert',
        'close_month' => 'Close Month',
        'reopen_month' => 'Reopen Month',
    ],

    'fields' => [
        'name' => 'Name',
        'type' => 'Type',
        'asset' => 'Asset',
        'amount' => 'Amount',
        'note' => 'Note',
        'note_optional' => 'Note (optional)',
        'note_placeholder' => 'Add a note...',
        'date' => 'Date',
        'due_day' => 'Due Day',
        'no_due_day' => 'No due day',
        'transfer_amount' => 'Transfer Amount',
        'from' => 'From',
        'to' => 'To',
        'from_source' => 'From (Source)',
        'to_expense_asset' => 'To / Expense Asset',
    ],

    'select_asset' => 'Select asset',
    'select_source_asset' => 'Select source asset',
    'select_destination_asset' => 'Select destination asset',
    'add_entry' => 'Add Entry',

    'transfer' => [
        'info' => 'Transfers move money between two assets. The total amount is the same for both.',
        'from' => 'From',
        'to' => 'To',
    ],

    'entries_section' => [
        'title' => 'Entries',
        'add_entry' => 'Add Entry',
        'select_asset' => 'Select asset',
    ],

    'event_types' => [
        'income' => 'income',
        'expense' => 'expense',
        'transfer' => 'transfer',
        'expense_with_transfer' => 'expense + transfer',
        'income_with_transfer' => 'income + transfer',
    ],

    'delete_confirmation' => [
        'title' => 'Delete Entry',
        'message' => 'Are you sure you want to delete <strong>:name</strong>? This action cannot be undone.',
    ],
];
