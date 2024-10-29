<?php

$functions = [
    // The name of your web service function, as discussed above.
    'report_dailyattendance_get_list_courses' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'report_dailyattendance\external\get_list_courses',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get the list of courses that matches the customerid, startdate, enddate and status attendance.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ]
    ],
    // The name of your web service function, as discussed above.
    'report_dailyattendance_get_list_group' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'report_dailyattendance\external\get_list_group',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get the list of group that matches the customerid, startdate, enddate and status attendance.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            'NAVANTIA_SERVICES',
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ]
    ],
];

$services = [
    'NAVANTIA_SERVICES' => [
        'functions' => [
            'report_dailyattendance_get_list_courses', // Aquí se incluye la función en el nuevo servicio
            'report_dailyattendance_get_list_group',
        ],
        'restrictedusers' => 1, // 0 = disponible para todos los usuarios, 1 = restringido
        'enabled' => 1, // 1 = habilitado, 0 = deshabilitado
        'shortname' => 'NAVANTIA_SERVICES',
        'downloadfiles' => 0, // Permitir la descarga de archivos
        'uploadfiles' => 0, // No permitir la subida de archivos
    ],
];