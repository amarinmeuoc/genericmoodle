<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'blocks/graphical_events:view' => [
        'riskbitmask' => RISK_SPAM, // Adjust risk bitmask as needed
        'captype' => 'read', // Capability type (read, write, etc.)
        'contextlevel' => CONTEXT_BLOCK, // Context level where the capability applies
        'archetypes' => [],
    ],
];