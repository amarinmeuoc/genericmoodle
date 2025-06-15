<?php 

$functions = [
     // The name of your web service function, as discussed above.
    'local_emails_get_list_trainees' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_emails\external\get_list_trainees',

        // A brief, human-readable, description of the web service function.
        'description' => 'Show all trainees.',

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
    'local_emails_load_groups' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\local_emails\external\load_groups',

        // A brief, human-readable, description of the web service function.
        'description' => 'Show all groups.',

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
            'local_emails_get_list_trainees',
            'local_emails_load_groups',
            
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'NAVANTIA_SERVICES',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];