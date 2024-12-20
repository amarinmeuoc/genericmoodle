<?php 

$functions = [
     // The name of your web service function, as discussed above.
     'block_itp_remove_client' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\remove_client',

        // A brief, human-readable, description of the web service function.
        'description' => 'Remove a client by its shortname.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
        // The name of your web service function, as discussed above.
    'block_itp_add_client' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\add_client',

        // A brief, human-readable, description of the web service function.
        'description' => 'Add a new client.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
    ],
    // The name of your web service function, as discussed above.
    'block_itp_load_groups' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\load_groups',

        // A brief, human-readable, description of the web service function.
        'description' => 'Load the list of groups that belongs to a selected customer.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_add_group' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\add_group',

        // A brief, human-readable, description of the web service function.
        'description' => 'Load the list of groups that belongs to a selected customer.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_remove_group' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\remove_group',

        // A brief, human-readable, description of the web service function.
        'description' => 'Remove a group from id.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_reset_training_plan' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\reset_training_plan',

        // A brief, human-readable, description of the web service function.
        'description' => 'Reset the hole training plan.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_reset_itp' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\reset_itp',

        // A brief, human-readable, description of the web service function.
        'description' => 'Reset the hole training plan.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_load_itp' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\load_itp',

        // A brief, human-readable, description of the web service function.
        'description' => 'Load the ITP.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_get_assessment_details' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\get_assessment_details',

        // A brief, human-readable, description of the web service function.
        'description' => 'Show the assessment of a selected course.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_get_daily_attendance' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\get_daily_attendance',

        // A brief, human-readable, description of the web service function.
        'description' => 'Show the assessment of a selected course.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_get_list_trainees' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\get_list_trainees',

        // A brief, human-readable, description of the web service function.
        'description' => 'get the list of trainees from a group.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_itp_edit_group' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_itp\external\edit_group',

        // A brief, human-readable, description of the web service function.
        'description' => 'get the list of trainees from a group.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    
];

$services = [
    'NAVANTIA_SERVICES' => [
        'functions' => [
            'block_itp_remove_client', // Aquí se incluye la función en el nuevo servicio
            'block_itp_add_client',
            'block_itp_load_groups_from_customer',
            'block_itp_add_group',
            'block_itp_remove_group',
            'block_itp_reset_training_plan',
            'block_itp_reset_itp',
            'block_itp_load_itp',
            'block_itp_get_assessment_details',
            'block_itp_get_daily_attendance',
            'block_itp_get_list_trainees',
            'block_itp_edit_group',
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'NAVANTIA_SERVICES',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];