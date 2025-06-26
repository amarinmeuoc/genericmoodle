<?php 

$functions = [
     // The name of your web service function, as discussed above.
     'block_chart_percentaje_it_load_projects' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_chart_percentaje_it\external\load_projects',

        // A brief, human-readable, description of the web service function.
        'description' => 'load all projects from registered.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'chart_percentaje_it_navantiaservices',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    // The name of your web service function, as discussed above.
    'block_chart_percentaje_it_load_vessels' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_chart_percentaje_it\external\load_vessels',

        // A brief, human-readable, description of the web service function.
        'description' => 'load all vessels from registered.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'chart_percentaje_it_navantiaservices',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],

        // The name of your web service function, as discussed above.
        'block_chart_percentaje_it_get_states_percentage' => [
            // The name of the namespaced class that the function is located in.
            'classname'   => '\block_chart_percentaje_it\external\get_states_percentage',
    
            // A brief, human-readable, description of the web service function.
            'description' => 'load all data registered.',
    
            // Options include read, and write.
            'type'        => 'read',
    
            // Whether the service is available for use in AJAX calls from the web.
            'ajax'        => true,
    
            // An optional list of services where the function will be included.
            'services' => [
                'chart_percentaje_it_navantiaservices',
                MOODLE_OFFICIAL_MOBILE_SERVICE
            ]
            
        ],
    
];

$services = [
    'chart_percentaje_it_navantiaservices' => [
        'functions' => [
            'block_chart_percentaje_it_load_projects',
            'block_chart_percentaje_it_get_states_percentage',
            'block_chart_percentaje_it_load_vessels'
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'chart_percentaje_it_navantiaservices',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];