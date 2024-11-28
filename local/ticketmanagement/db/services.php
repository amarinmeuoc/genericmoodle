<?php 

$functions = [
     // The name of your web service function, as discussed above.
     'local_ticketmanagement_remove_ticketcategory' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\remove_ticketcategory',

        // A brief, human-readable, description of the web service function.
        'description' => 'Remove ticket category.',

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
    'local_ticketmanagement_add_ticketcategory' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\add_ticketcategory',

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
    'local_ticketmanagement_add_ticketsubcategory' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\add_ticketsubcategory',

        // A brief, human-readable, description of the web service function.
        'description' => 'Add a new subcategory.',

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
    'local_ticketmanagement_remove_ticketsubcategory' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\remove_ticketsubcategory',

        // A brief, human-readable, description of the web service function.
        'description' => 'Remove ticket subcategory.',

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
    'local_ticketmanagement_get_ticketsubcategory' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_ticketsubcategory',

        // A brief, human-readable, description of the web service function.
        'description' => 'Remove ticket subcategory.',

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
    'local_ticketmanagement_edit_ticketcategory' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\edit_ticketcategory',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit ticket category.',

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
    'local_ticketmanagement_edit_ticketsubcategory' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\edit_ticketsubcategory',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit ticket subcategory.',

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
    'local_ticketmanagement_load_groups' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\load_groups',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit ticket subcategory.',

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
    'local_ticketmanagement_get_list_trainees' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_list_trainees',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit ticket subcategory.',

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
    'local_ticketmanagement_load_subcategories' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\load_subcategories',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit ticket subcategory.',

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
    'local_ticketmanagement_add_ticket' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\add_ticket',

        // A brief, human-readable, description of the web service function.
        'description' => 'Add ticket .',

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
    'local_ticketmanagement_get_tickets' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_tickets',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get all ticket filtered by dates.',

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
    'local_ticketmanagement_get_ticket_byId' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_ticket_byId',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get ticket by id .',

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
    'local_ticketmanagement_get_family_members' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_family_members',

        // A brief, human-readable, description of the web service function.
        'description' => 'List the family members .',

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
    'local_ticketmanagement_add_family_members' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\add_family_members',

        // A brief, human-readable, description of the web service function.
        'description' => 'Add family members .',

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
    'local_ticketmanagement_edit_family_members' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\edit_family_members',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit family members',

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
    'local_ticketmanagement_remove_family_members' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\remove_family_members',

        // A brief, human-readable, description of the web service function.
        'description' => 'Remove family member.',

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
    'local_ticketmanagement_edit_ticket' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\edit_ticket',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit a ticket.',

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
    'local_ticketmanagement_get_tickets_byUserId' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_tickets_byUserId',

        // A brief, human-readable, description of the web service function.
        'description' => 'List user tickets.',

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
    'local_ticketmanagement_edit_ticket_byUser' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\edit_ticket_byUser',

        // A brief, human-readable, description of the web service function.
        'description' => 'Edit a ticket.',

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
    'local_ticketmanagement_load_actions' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\load_actions',

        // A brief, human-readable, description of the web service function.
        'description' => 'Load actions.',

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
    'local_ticketmanagement_get_tickets_excel' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_tickets_excel',

        // A brief, human-readable, description of the web service function.
        'description' => 'Load actions.',

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
    'local_ticketmanagement_update_ticket_communication' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\update_ticket_communication',

        // A brief, human-readable, description of the web service function.
        'description' => 'Activate/Desactivate users communication.',

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
    'local_ticketmanagement_get_logistic_users' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_logistic_users',

        // A brief, human-readable, description of the web service function.
        'description' => 'Getting all logistic users.',

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
    'local_ticketmanagement_get_list_users' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_list_users',

        // A brief, human-readable, description of the web service function.
        'description' => 'Getting all users.',

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
    'local_ticketmanagement_get_list_families' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_ticketmanagement\external\get_list_families',

        // A brief, human-readable, description of the web service function.
        'description' => 'Getting all families.',

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
            'local_ticketmanagement_remove_ticketcategory', // Aquí se incluye la función en el nuevo servicio
            'local_ticketmanagement_add_ticketcategory',
            'local_ticketmanagement_add_ticketsubcategory',
            'local_ticketmanagement_remove_ticketsubcategory',
            'local_ticketmanagement_get_ticketsubcategory',
            'local_ticketmanagement_edit_ticketcategory',
            'local_ticketmanagement_edit_ticketsubcategory',
            'local_ticketmanagement_load_groups',
            'local_ticketmanagement_get_list_trainees',
            'local_ticketmanagement_load_subcategories',
            'local_ticketmanagement_add_ticket',
            'local_ticketmanagement_get_tickets',
            'local_ticketmanagement_get_ticket_byId',
            'local_ticketmanagement_get_family_members',
            'local_ticketmanagement_add_family_members',
            'local_ticketmanagement_edit_family_members',
            'local_ticketmanagement_remove_family_members',
            'local_ticketmanagement_edit_ticket',
            'local_ticketmanagement_get_tickets_byUserId',
            'local_ticketmanagement_load_actions',
            'local_ticketmanagement_get_tickets_excel',
            'local_ticketmanagement_update_ticket_communication',
            'local_ticketmanagement_get_logistic_users',
            'local_ticketmanagement_get_list_users',
            'local_ticketmanagement_get_list_families'
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'NAVANTIA_SERVICES',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];