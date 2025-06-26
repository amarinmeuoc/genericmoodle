<?php 

$functions = [
     // The name of your web service function, as discussed above.
     'block_gantt_diagram_get_gantt' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\block_gantt_diagram\external\get_gantt',

        // A brief, human-readable, description of the web service function.
        'description' => 'Remove a client by its shortname.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'gantt_navantiaservices',
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ]
        
    ],
    
];

$services = [
    'gantt_navantiaservices' => [
        'functions' => [
            'block_gantt_diagram_get_gantt', // Aquí se incluye la función en el nuevo servicio
           
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'gantt_navantiaservices',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];