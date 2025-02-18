<?php

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_ticketmanagement_removeticketbyid');
$context = context_system::instance();
if (!has_capability('moodle/site:config', $context) && !is_siteadmin()) {
    echo $OUTPUT->header();
    $message = get_string('error', 'local_ticketmanagement');
    \core\notification::error($message);
    echo $OUTPUT->footer();
    return;
}

$mform = new \local_ticketmanagement\form\removeticketformbyid();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('removeticketbyid', 'local_ticketmanagement'));

$toform = null;

if ($mform->is_cancelled()) {
    // Manejar cancelación del formulario
    redirect(new \moodle_url('/my'), 'Formulario cancelado.');
} elseif ($fromform = $mform->get_data()) {
    global $DB, $USER;

    $ticketid=$fromform->ticketid;

    if ($DB->record_exists('ticket', array('id' => $ticketid))) {
        //Obtengo el itemid de los archivos asociados antes de proceder al borrado
        $itemid=$DB->get_field('ticket','lastupdate',['id'=>$ticketid]);
        $num_archivos_borrados=0;
        if ($itemid) {
            $fs = get_file_storage();
            $contextid = context_system::instance()->id;
        
            // Obtener todos los archivos relacionados con el itemid
            $files = $fs->get_area_files($contextid, 'local_ticketmanagement', 'sharedfiles', $itemid, 'sortorder', false);
        
            // Eliminar cada archivo encontrado
            foreach ($files as $file) {
                $num_archivos_borrados++;
                $file->delete();
            }
        }
        $result=$DB->delete_records('ticket',['id'=>$ticketid]);
        if ($result){
            $message = "El registro se ha borrado correctamente. Se han borrado <strong>$num_archivos_borrados</strong> archivos en total";
            redirect(new \moodle_url('delete_ticket.php'), $message,null,\core\output\notification::NOTIFY_SUCCESS);
        } else {
            $message = "Ocurrio un problema en el proceso de borrado";
            redirect(new \moodle_url('delete_ticket.php'), $message,null,\core\output\notification::NOTIFY_ERROR);
        }

    } else {
        $message = "El ticket con el id <strong>$ticketid</strong>, no existe. Debes indicar un id exacto.";
        redirect(new \moodle_url('delete_ticket.php'), $message,null,\core\output\notification::NOTIFY_INFO);
    }
    

} else {
    // Primera carga del formulario o datos inválidos
    $mform->set_data($toform);
    $mform->display();
}

echo $OUTPUT->footer();
