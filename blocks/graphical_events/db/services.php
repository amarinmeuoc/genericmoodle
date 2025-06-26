<?php 

$functions = [
     // The name of your web service function, as discussed above.
     'block_graphical_events_load_projects' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_graphical_events\external\load_projects',

        // A brief, human-readable, description of the web service function.
        'description' => 'load all projects from registered.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'graphical_events_navantiaservices',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_graphical_events_load_vessels' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_graphical_events\external\load_vessels',

        // A brief, human-readable, description of the web service function.
        'description' => 'load all vessels registered.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'graphical_events_navantiaservices',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
     // The name of your web service function, as discussed above.
     'block_graphical_events_load_data' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_graphical_events\external\load_data',

        // A brief, human-readable, description of the web service function.
        'description' => 'load all data registered.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'graphical_events_navantiaservices',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    
];

$services = [
    'graphical_events_navantiaservices' => [
        'functions' => [
            'block_graphical_events_load_projects',
            'block_graphical_events_load_vessels',
            'block_graphical_events_load_data' // Aquí se incluye la función en el nuevo servicio
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'graphical_events_navantiaservices',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];