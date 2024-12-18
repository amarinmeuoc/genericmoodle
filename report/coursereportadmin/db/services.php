<?php 

$functions = [
    'report_coursereportadmin_get_total_assessment' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\report_coursereportadmin\external\get_total_assessment',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get the total assessment of all courses.',

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
    'report_coursereportadmin_get_total_dailyattendance' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\report_coursereportadmin\external\get_total_dailyattendance',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get the total dailyattendance of all courses.',

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
    'report_coursereportadmin_get_total_trainee_report' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => '\report_coursereportadmin\external\get_total_trainee_report',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get the total trainee report.',

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
            'report_coursereportadmin_get_total_assessment', // Aquí se incluye la función en el nuevo servicio
            'report_coursereportadmin_get_total_dailyattendance',
            'report_coursereportadmin_get_total_trainee_report'
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'NAVANTIA_SERVICES',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];