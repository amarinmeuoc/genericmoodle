<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_itp_install() {
    global $DB;

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
        'nie' => 'NIE'
    ];

    foreach ($customfields as $shortname => $name) {
        // Check if the field already exists
        if (!$DB->record_exists('user_info_field', ['shortname' => $shortname])) {
            // Create the custom field
            $data = new stdClass();
            $data->shortname = $shortname;
            $data->name = $name;
            $data->datatype = 'text';
            $data->description = '';
            $data->descriptionformat = FORMAT_HTML;
            $data->categoryid = $category->id; // Assign to the Navantia category
            $data->sortorder = 999; // Default sort order
            $data->required = 0;
            $data->locked = 0;
            $data->visible = 1;
            $data->forceunique = 0;
            $data->signup = 0;
            $data->defaultdata = '';
            $data->defaultdataformat = FORMAT_HTML;
            $data->param1 = 30; // Text field size
            $data->param2 = 2048; // Text field max length
            $data->param3 = ''; // Text field regex
            $data->param4 = ''; // Text field regex flags
            $data->param5 = 0; // Text field is password

            $DB->insert_record('user_info_field', $data);
        }
    }

    // Creación del usuario para acceso a servicios webs
    // Verificar si el usuario ya existe
    if (!$DB->record_exists('user', array('username' => 'webserviceuser'))) {
        // Datos del nuevo usuario
        $user = new stdClass();
        $user->username = 'webserviceuser';
        $user->password = hash_internal_user_password('1_2@#Nav$.!&&%Compl2024@@');
        $user->firstname = 'Webservice User';
        $user->lastname = 'only for web service access';
        $user->email = 'webservice@email.com';
        $user->auth = 'manual'; 
        $user->confirmed = 1;
        $user->mnethostid = $DB->get_field('mnet_host', 'id', array('wwwroot' => $CFG->wwwroot));

        // Insertar el usuario en la base de datos
        $DB->insert_record('user', $user);
    }

}