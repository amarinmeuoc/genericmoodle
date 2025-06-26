<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_charts_load_projects' => [  // Changed function name
        'classname'   => 'block_charts\external\load_projects',
        'methodname'  => 'execute',  // Explicitly declare method name
        'description' => 'Load all projects from registered',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'services' => ['block_charts_services']
    ],

    'block_charts_get_number_tickets_groupedby_category' => [  // Changed function name
        'classname'   => 'block_charts\external\get_number_tickets_groupedby_category',
        'methodname'  => 'execute',
        'description' => 'Load all data registered',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'services' => ['block_charts_services']
    ],
];

$services = [
    'block_charts_services' => [
        'functions' => [
            'block_charts_load_projects',
            'block_charts_get_number_tickets_groupedby_category'
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'block_charts_services',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ]
];