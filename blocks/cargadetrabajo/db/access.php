<?php
// This file is part of your Moodle block plugin.
// Defines the capabilities for your block.

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'block/cargadetrabajo:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
    
];