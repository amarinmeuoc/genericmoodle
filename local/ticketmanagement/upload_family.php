<?php 
require_once('../../config.php');
require_login();

global $USER;
$PAGE->set_url(new moodle_url('/local/ticketmanagement/upload_family.php'));
$PAGE->set_context(context_system::instance());

// Verifica si el usuario tiene el rol necesario
$role = $USER->profile['role'];





// Muestra el formulario y el contenido si el rol coincide
if (preg_match('/(logistic|manager)/i', $role)) {
     // Inicializa el formulario antes del encabezado
     $mform = new \local_ticketmanagement\form\uploadFamilyform();

     // Define los datos para renderizar el template
     $data = [ 
        /* 'listadoFamily' => [
             [
                 'id' => '1',
                 'relationship' => 'Wife',
                 'name' => 'Manolo',
                 'lastname' => 'Contreras',
             ],
             [
                 'id' => '2',
                 'relationship' => 'Son',
                 'name' => 'Haya',
                 'lastname' => 'Maralla',
             ],
         ],
         'user' => 'Mohamed Contreras'*/
     ];
    // Aquí se imprime el encabezado
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadFamily', 'local_ticketmanagement'));
    $mform->display();  // Muestra el formulario
    $render = $OUTPUT->render_from_template('local_ticketmanagement/family-log', $data);
    echo $render;
} else {
    // Aquí se imprime el encabezado
    echo $OUTPUT->header();    
    $message="<h1>You dont have permission to be here!!.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}

// Pie de página
echo $OUTPUT->footer();
