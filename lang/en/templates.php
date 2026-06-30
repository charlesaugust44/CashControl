<?php

return [
    'title' => 'Templates',
    'singular' => 'Template',
    'new' => 'New Template',
    'edit' => 'Edit Template',
    'delete' => 'Delete Template',
    'show' => 'Template Details',
    'no_templates' => 'No templates yet',
    'no_templates_description' => 'Create your first template to start generating recurring events.',
    'create_first' => 'Create Template',

    'fields' => [
        'name' => 'Name',
        'description' => 'Description',
        'description_optional' => 'Description (optional)',
        'type' => 'Type',
        'rule' => 'Rule',
        'default_amount' => 'Default Amount',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'end_date_optional' => 'End Date (optional)',
        'asset' => 'Asset',
        'source_asset' => 'Source Asset',
        'destination_asset' => 'Destination Asset',
    ],

    'placeholders' => [
        'name' => 'e.g., Monthly Salary',
        'description' => 'Brief description of this template',
        'default_amount' => '0.00',
    ],

    'help' => [
        'end_date' => 'Leave empty for ongoing templates.',
        'default_amount' => 'Used for fixed rule. For max/mean rules, this is the fallback when no history exists.',
    ],

    'types' => [
        'income' => 'Income',
        'expense' => 'Expense',
        'transfer' => 'Transfer',
        'expense_with_transfer' => 'Expense with Transfer',
    ],

    'rules' => [
        'fixed' => 'Fixed',
        'max_last_five_months' => 'Max of last 5 months',
        'mean_last_five_months' => 'Mean of last 5 months',
    ],

    'schedule' => [
        'title' => 'Schedule',
        'ongoing' => 'Ongoing',
    ],

    'configuration' => [
        'title' => 'Configuration',
    ],

    'sections' => [
        'basic_info' => 'Basic Information',
    ],

    'affected_events' => [
        'title' => 'Affected Future Events',
        'edit_description' => 'These events have been saved with custom values. Choose whether to keep them as-is or delete them (they will revert to forecast using the updated template).',
        'delete_description' => 'These future events have saved records. Choose whether to delete them or keep them as standalone events.',
        'will_be_regenerated' => 'These future events will be automatically regenerated using the updated template rules.',
        'keep' => 'Keep',
        'delete' => 'Delete',
    ],

    'delete_confirmation' => [
        'title' => 'Delete Template',
        'message' => 'Are you sure you want to delete <strong>:name</strong>? This action cannot be undone.',
    ],
];
