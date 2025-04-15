$tasks = [
    [
        'classname' => 'local_ticketmanagement\task\check_fine_reminders',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '9', // Runs daily at 9 AM
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];