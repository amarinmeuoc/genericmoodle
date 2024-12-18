<?php

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_ticketmanagement_uploadfamilycsv');
$context = context_system::instance();
if (!has_capability('moodle/site:config', $context)) {
    echo $OUTPUT->header();
    $message = get_string('error', 'local_ticketmanagement');
    \core\notification::error($message);
    echo $OUTPUT->footer();
    return;
}

$mform = new \local_ticketmanagement\form\uploadFamilyformcsv();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadFamily', 'local_ticketmanagement'));

$toform = null;

if ($mform->is_cancelled()) {
    // Manejar cancelación del formulario
    redirect(new \moodle_url('/my'), 'Formulario cancelado.');
} elseif ($fromform = $mform->get_data()) {
    global $DB, $USER;

    // Leer el archivo CSV
    $delimiter = $fromform->format; // Formato seleccionado (separador)
    $csv_content = $mform->get_file_content('familycsv');

    if (empty($csv_content)) {
        \core\notification::error('El archivo CSV está vacío.');
        echo $OUTPUT->footer();
        return;
    }

    $lines = explode(PHP_EOL, $csv_content);
    $header = null;

    $inserted_count = 0;
    $error_count = 0;

    $line_number = 1; // Para rastrear la línea actual
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            $line_number++;
            continue; // Omitir líneas vacías
        }
    
        // Usar el separador seleccionado
        $fields = str_getcsv($line, $delimiter);
    
        // Validar el número de columnas
        if (!$header) {
            $header = $fields; // Asume que la primera fila es el encabezado
            if (count($header) !== 12) {
                \core\notification::error('El encabezado del CSV no tiene el formato esperado.');
                echo $OUTPUT->footer();
                return;
            }
            $line_number++;
            continue;
        }
    
        if (count($fields) !== count($header)) {
            \core\notification::error("Número incorrecto de columnas en la línea {$line_number}.");
            $error_count++;
            $line_number++;
            continue;
        }
    
        $data = array_combine($header, $fields);
    
        // Validar trainee_email
        $trainee_email = $data['trainee_email'];
        if (empty($trainee_email)) {
            \core\notification::warning("El campo trainee_email está vacío en la línea {$line_number}.");
            $error_count++;
            $line_number++;
            continue;
        }
    
        // Buscar el ID del usuario basado en trainee_email
        $user = $DB->get_record('user', ['email' => $trainee_email], 'id', IGNORE_MISSING);
        if (!$user) {
            \core\notification::warning("El usuario con email {$trainee_email} no existe. Línea {$line_number} omitida.");
            $error_count++;
            $line_number++;
            continue;
        }
    
        // Validar y formatear fechas
        $birthdate = DateTime::createFromFormat('d/m/Y', $data['birthdate']);
        $arrival = DateTime::createFromFormat('d/m/Y', $data['arrival']);
        $departure = DateTime::createFromFormat('d/m/Y', $data['departure']);
        
        // Verificar que las fechas sean válidas
        if (!$birthdate || !$arrival || !$departure) {
            \core\notification::warning("Formato de fecha incorrecto en la línea {$line_number}. Asegúrate de usar el formato dd/mm/yyyy.");
            $error_count++;
            $line_number++;
            continue;
        }

        // Convertir las fechas a timestamp (formato Unix)
        $birthdate = $birthdate->getTimestamp();
        $arrival = $arrival->getTimestamp();
        $departure = $departure->getTimestamp();
    
        // Preparar datos para inserción
        $record = new stdClass();
        $record->userid = $user->id;
        $record->relationship = $data['relationship'];
        $record->name = $data['name'];
        $record->lastname = $data['lastname'];
        $record->nie = $data['nie'];
        $record->birthdate = $birthdate;
        $record->adeslas = $data['healthinsurance']; // Campo healthinsurance mapeado a adeslas
        $record->phone1 = $data['phone1'];
        $record->email = $data['email'];
        $record->arrival = $arrival;
        $record->departure = $departure;
        $record->notes = $data['notes'];
    
        // Verificar si ya existe un registro con los mismos datos
        $existing_record = $DB->get_record('family', [
            'userid' => $record->userid,
            'name' => $record->name,
            'lastname' => $record->lastname,
            'nie' => $record->nie
        ]);
    
        if ($existing_record) {
            // Si el registro ya existe, actualizarlo
            $record->id = $existing_record->id;
            $DB->update_record('family', $record);
        } else {
            // Si no existe, insertarlo
            $DB->insert_record('family', $record);
        }
    
        $inserted_count++;
        $line_number++;
    }
    
    // Mensajes de finalización
    if ($inserted_count > 0) {
        \core\notification::success("Se han insertado correctamente {$inserted_count} registros.");
    }
    if ($error_count > 0) {
        \core\notification::warning("Se encontraron errores en {$error_count} líneas.");
    }

    redirect(new \moodle_url('upload_familycsv.php'), 'Proceso completado.');
} else {
    // Primera carga del formulario o datos inválidos
    $mform->set_data($toform);
    $mform->display();
}

echo $OUTPUT->footer();
