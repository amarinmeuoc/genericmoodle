<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\local_emails\event\email_sent',
        'callback'  => '\local_emails\observer::email_sent',
    ),
);