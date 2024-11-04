<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_ticketmanagement_install() {
    global $DB,$CFG;

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