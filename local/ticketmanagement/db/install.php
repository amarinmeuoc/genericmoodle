<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_ticketmanagement_install() {
    global $DB,$CFG;

    // Define the category name
    $categoryname = 'Navantia';

    // Check if the category already exists
    if (!$category = $DB->get_record('user_info_category', ['name' => $categoryname])) {
        // Create the category
        $category = new stdClass();
        $category->name = $categoryname;
        $category->sortorder = 999; // Default sort order
        $category->id = $DB->insert_record('user_info_category', $category);
    }

    // Define the custom user profile fields
    $customfields = [
        'customer' => 'Customer',
        'group' => 'Group',
        'billid' => 'Bill ID',
        'role' => 'Role',
        'type' => 'Type',
        'department' => 'Department',
        'passport' => 'Passport',
        'personalemail' => 'Personal Email',
        'nie' => 'NIE',
        'niedate'=>'NIE expiration date',
        'birthdate'=>'Birth date',
        'insurance_card_number'=>'Insurance card number',
        'shoesize'=>'Shoe size',
        'overallsize'=>'Overall size',
        'arrival_date'=>'Arrival date',
        'departure_date'=>'Departure date',
        'notes'=>'Notes'
    ];

    foreach ($customfields as $shortname => $name) {
        // Check if the field already exists
        if (!$DB->record_exists('user_info_field', ['shortname' => $shortname])) {
            // Create the custom field
            $data = new stdClass();
            $data->shortname = $shortname;
            $data->name = $name;
            if ($data->shortname==='arrival_date' || $data->shortname==='departure_date' || $data->shortname==='birthdate' || $data->shortname==='niedate'){
                $data->datatype = 'datetime';
                $data->visible = 1;
            } else if ($data->shortname==='notes') {
                $data->datatype = 'textarea';
                $data->visible = 0;
            } else {
                $data->datatype = 'text';
                $data->visible = 1;
            }
            
            $data->description = '';
            $data->descriptionformat = FORMAT_HTML;
            $data->categoryid = $category->id; // Assign to the Navantia category
            $data->sortorder = 999; // Default sort order
            $data->required = 0;
            $data->locked = 0;
            $data->forceunique = 0;
            $data->signup = 0;
            
            // Set parameters based on datatype
            if ($data->datatype === 'text') {
                $data->defaultdata = '';
                $data->defaultdataformat = FORMAT_HTML;
                $data->param1 = 30; // Text field size
                $data->param2 = 2048; // Text field max length
            } elseif ($data->datatype === 'textarea') {
                $data->param1 = 50; // Rows
                $data->param2 = 10; // Columns
                $data->defaultdata = '';
                $data->defaultdataformat = FORMAT_HTML;
            } elseif ($data->datatype === 'datetime') {
                $data->param1 = 2024; // No additional parameters for datetime
                $data->param2 = 2030; // No additional parameters for datetime
                $data->defaultdata = 0;
                $data->defaultdataformat = 0;
            }

            $DB->insert_record('user_info_field', $data);
        }
    }

    // Creación del usuario para acceso a servicios webs
    // Verificar si el usuario ya existe
    if (!$DB->record_exists('user', array('username' => 'logisticwebservice'))) {
        // Datos del nuevo usuario
        $user = new stdClass();
        $user->username = 'logisticwebservice';
        $user->password = hash_internal_user_password('3_%@#OTues$.!$@%Lo3pl8!$4@@');
        $user->firstname = 'Logistic service User';
        $user->lastname = 'only for web service access';
        $user->email = 'logistic@email.com';
        $user->auth = 'manual'; 
        $user->confirmed = 1;
        $user->mnethostid = $DB->get_field('mnet_host', 'id', array('wwwroot' => $CFG->wwwroot));

        // Insertar el usuario en la base de datos y obtener el ID
        $userid = $DB->insert_record('user', $user);

         // Verificar si el usuario fue creado correctamente
         if ($userid) {
            // Obtener el contexto de sistema
            $systemcontext = context_system::instance();

            // Obtener el ID del rol de manager (o cualquier otro rol que desees asignar)
            $roleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));

            // Asignar el rol al usuario en el contexto de sistema
            role_assign($roleid, $userid, $systemcontext->id);
        }
    }

}