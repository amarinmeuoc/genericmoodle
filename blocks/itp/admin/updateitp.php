<?php

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

global $PAGE, $DB, $OUTPUT;

require_login();

admin_externalpage_setup('block_itp_upload');
$pageurl = $PAGE->url;

$context = context_system::instance();
if (!has_capability('moodle/site:config', $context)) {
    echo $OUTPUT->header();
    \core\notification::error(get_string('error', 'block_itp'));
    echo $OUTPUT->footer();
    return;
}

$mform = new \block_itp\form\uploaditpform();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadITP', 'block_itp'));

$errors = []; // Lista para almacenar errores.
$inserted_count = 0;
$processed_lines = 0;

if ($mform->is_cancelled()) {
    redirect($pageurl);
} elseif ($fromform = $mform->get_data()) {
    $customerid = intval($fromform->tecustomer);
    $groupid = isset($fromform->tegroup) ? intval($fromform->tegroup) : 0;
    $ifemail = $fromform->email;
    $subject = $fromform->subject;
    $editorContent = $fromform->email_editor;
    $separator = $fromform->selectdelimiter;
    $csv_content = $mform->get_file_content('csv_file');

    // Eliminar BOM del contenido completo del CSV
    $csv_content = removeBom($csv_content);

    $lines = array_filter(array_map('trim', explode(PHP_EOL, $csv_content))); // Filtrar líneas vacías.
    if (empty($lines)) {
        
        redirect($pageurl,"El archivo CSV está vacío.",null,\core\output\notification::NOTIFY_ERROR);
    }

    $header = removeBomKeys(str_getcsv(array_shift($lines), $separator)); // Extraer encabezado.
    $required_fields = ['customer','group', 'billid', 'email', 'startdate', 'enddate', 'wbs', 'coursename', 'duration', 'location', 'classroom', 'schedule'];
    
    if ($header !== $required_fields) {
       
        redirect($pageurl,"Formato de archivo incorrecto. Verifica los encabezados.",null,\core\output\notification::NOTIFY_ERROR);
    }

    // Eliminar registros anteriores.
    $delete_params = ['customerid' => $customerid];
    if ($groupid > 0) $delete_params['groupid'] = $groupid;
    $DB->delete_records('itptrainee', $delete_params);

    $list_of_emails = [];

    foreach ($lines as $index => $line) {
        $processed_lines++;
        $data = str_getcsv($line, $separator);
        if (count($data) !== count($header)) {
            $errors[] = "Error en línea {$processed_lines}: Número incorrecto de columnas.";
            break;
        }

        $row = array_combine($header, $data);

        if (intval($row['customer'])!==$customerid) continue; //Filtrar por customer

        if (intval($row['group']) !== $groupid && $groupid > 0) continue; // Filtrar por grupo.

        // Validaciones.
        if (empty($row['billid']) || empty($row['email'])) {
            $errors[] = "Error en línea {$processed_lines}: 'billid' o 'email' no puede estar vacío.";
            break;
        }

        if (empty($row['wbs']) || strlen($row['wbs']) > 40) {
            $errors[] = "Error en línea {$processed_lines}: 'wbs' inválido (máx 40 caracteres).";
            break;
        }

        $startdate = \DateTime::createFromFormat('d/m/Y', $row['startdate']);
        $enddate = \DateTime::createFromFormat('d/m/Y', $row['enddate']);
        if (!$startdate || !$enddate) {
            $errors[] = "Error en línea {$processed_lines}: Formato de fecha incorrecto.";
            break;
        }

        if (!grupoExiste($customerid, intval($row['group']))) {
            $errors[] = "Error en línea {$processed_lines}: El grupo no existe.";
            break;
        }

        // Crear registro.
        $record = (object)[
            'customerid' => intval($row['customer']),
            'groupid' => intval($row['group']),
            'billid' => $row['billid'],
            'email' => $row['email'],
            'startdate' => $startdate->getTimestamp(),
            'enddate' => $enddate->getTimestamp(),
            'course' => $row['wbs'],
            'name' => $row['coursename'],
            'duration' => $row['duration'],
            'location' => $row['location'],
            'classroom' => $row['classroom'],
            'schedule' => $row['schedule'],
            'lastupdate' => time(),
        ];

        $DB->insert_record('itptrainee', $record);
        $inserted_count++;
        if (!in_array($row['email'], $list_of_emails)) $list_of_emails[] = $row['email'];
    }

    // Notificar errores y resultados.
    if (!empty($errors)) {
        $error_message="";
        foreach ($errors as $error) {
            $error_message.=$error;
        }
        redirect($pageurl,$error_message,null,\core\output\notification::NOTIFY_ERROR);
    }
    
    $success_message="Se insertaron {$inserted_count} registros correctamente. ";
    // Enviar correos si está habilitado.
    if ($ifemail === 'yes' && count($list_of_emails) > 0) {
        sendEmail($list_of_emails, $subject, $editorContent);
        $success_message.="Correos enviados a los participantes. ";
    }

    redirect($pageurl,$success_message,null,\core\output\notification::NOTIFY_SUCCESS);
} else {
    $mform->display();
}

echo $OUTPUT->footer();

// Función para validar grupo.
function grupoExiste($customerid, $groupid) {
    global $DB;
    return $DB->record_exists('grouptrainee', ['customer' => $customerid, 'id' => $groupid]);
}

// Función para enviar correos.
function sendEmail($list_of_emails, $subject, $editorContent) {
    global $DB;
    $noreplyuser = core_user::get_noreply_user();
    foreach ($list_of_emails as $email) {
        $user = $DB->get_record('user', ['email' => $email]);
        if ($user) {
            email_to_user($user, $noreplyuser, $subject, '', $editorContent['text']);
        }
    }
}

function removeBomKeys(array $data): array {
    $cleanData = [];
    foreach ($data as $key => $value) {
        // Eliminar el BOM de la clave
        $cleanKey = preg_replace('/^\xEF\xBB\xBF/', '', $key);
        $cleanData[$cleanKey] = $value;
    }
    return $cleanData;
}

function removeBom($string) {
    // Eliminar BOM si existe
    return preg_replace('/^\xEF\xBB\xBF/', '', $string);
}
