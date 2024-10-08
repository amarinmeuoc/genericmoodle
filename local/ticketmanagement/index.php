<?php
require_once('../../config.php');

require_login();

global $USER;
$url=new moodle_url('local/ticketmanagement.php');
//Si es estudiante o cualquier otro rol cargamos un formulario de creación de ticket
//Si es logistico cargamos un formulario de creación de ticket y de búsqueda de tickets
$role=$USER->profile_field['role'];
$pattern='/logistic/i';
$logistic_role=preg_match($pattern,'logistic');

$data = [ 
    'form' => $form,
];

if ($logistic_role){
    //Muestra formulario para logistic
    $mform=new \local_ticketmanagement\form\manageticket_log();
    
    $render=$OUTPUT->render_from_template('local_ticketmanagement/content-log', $data);
} else{
    //Muestra sistema de gestión de tickets alumno
    $mform=new \local_ticketmanagement\form\manageticket_st();
    
    $render=$OUTPUT->render_from_template('local_ticketmanagement/content-user', $data);
}
$PAGE->requires->css('/blocks/itp/css/styles.scss');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageticket', 'local_ticketmanagement'));
$mform->display();



echo $render;


echo $OUTPUT->footer();
?>