<?php 

$functions = [
     // The name of your web service function, as discussed above.
     'block_charts_load_projects' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_charts\external\load_projects',

        // A brief, human-readable, description of the web service function.
        'description' => 'load all projects from registered.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'block_charts_services',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],

        // The name of your web service function, as discussed above.
        'block_charts_get_number_tickets_groupedby_category' => [
            // The name of the namespaced class that the function is located in.
            'classname'   => '\block_charts\external\get_number_tickets_groupedby_category',
    
            // A brief, human-readable, description of the web service function.
            'description' => 'load all data registered.',
    
            // Options include read, and write.
            'type'        => 'read',
    
            // Whether the service is available for use in AJAX calls from the web.
            'ajax'        => true,
    
            // An optional list of services where the function will be included.
            'services' => [
                'block_charts_services',
                MOODLE_OFFICIAL_MOBILE_SERVICE
            ]
            
        ],
    
];

$services = [
    'block_charts_services' => [
        'functions' => [
            'block_charts_load_projects',
            'block_charts_get_number_tickets_groupedby_category'
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'block_charts_services',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];