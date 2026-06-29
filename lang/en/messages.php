<?php

return [
    'success' => [
        'created' => ':item created successfully',
        'updated' => ':item updated successfully',
        'deleted' => ':item deleted successfully',
        'consolidated' => 'Event consolidated successfully',
        'unconsolidated' => 'Event unconsolidated successfully',
        'saved' => ':item saved successfully',
        'month_closed' => 'Month :month closed successfully',
        'month_reopened' => 'Last month reopened successfully',
    ],
    'error' => [
        'generic' => 'An error occurred',
        'not_found' => ':item not found',
        'validation' => 'Please check your input',
    ],
    'confirm' => [
        'delete' => 'Are you sure you want to delete this :item?',
        'close_month' => 'Close this month? All events must be consolidated.',
        'reopen_month' => 'Reopen this month? This will unconsolidate all events.',
        'delete_event' => 'Are you sure you want to delete this event?',
    ],
];
