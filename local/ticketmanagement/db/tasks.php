<?php
defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array (
        'classname' => 'local_ticketmanagement\task\check_fine_reminders',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '9',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
    array (
        'classname' => 'local_ticketmanagement\task\sync_address',
        'blocking' => 0,
        'minute' => '10',
        'hour' => '18',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    )
    );