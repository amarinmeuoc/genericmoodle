<?php
defined('MOODLE_INTERNAL') || die();

$messageproviders = [
    'ticket_notification' => [
        'capability' => 'local/ticketmanagement:receive_notifications',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
        ],
    ],
];