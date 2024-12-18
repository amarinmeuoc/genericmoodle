<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

global $PAGE, $DB, $OUTPUT;

require_login();
admin_externalpage_setup('block_itp_updateTrainingPlan');

$pageurl = $PAGE->url;

// Recuperar parámetros
$type = optional_param('type', '', PARAM_TEXT);
$message = optional_param('message', '', PARAM_TEXT);

// Validación de permisos
$context = context_system::instance();
if (!has_capability('moodle/site:config', $context)) {
    echo $OUTPUT->header();
    \core\notification::error(get_string('error', 'block_itp'));
    echo $OUTPUT->footer();
    exit;
}

// Configurar formulario
$mform = new \block_itp\form\updateTrainingPlanform();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('updateTrainingPlan', 'block_itp'));

$errors = []; // Lista para almacenar errores.
$inserted_count = 0;

// Procesar formulario
if ($mform->is_cancelled()) {
    redirect($pageurl);
} elseif ($fromform = $mform->get_data()) {
    try {
        $customerid = intval($fromform->tecustomer);
        $groupid = isset($fromform->tegroup) ? intval($fromform->tegroup) : 0;
        $separator = $fromform->selectdelimiter;
        $csv_content = $mform->get_file_content('csv_file');

        // Eliminar BOM del contenido completo del CSV
        $csv_content = removeBom($csv_content);

        $lines = array_filter(array_map('trim', explode(PHP_EOL, $csv_content))); // Filtrar líneas vacías.
        if (empty($lines)) {
            redirect($pageurl,"El archivo CSV está vacío.",null,\core\output\notification::NOTIFY_ERROR);
        }

        $header = removeBomKeys(str_getcsv(array_shift($lines), $separator)); // Extraer encabezado.
        $required_fields = ['customer','group', 'wbs', 'course', 'startdate', 'enddate', 'num_trainees', 'trainees', 'location', 'provider'];
        
        if ($header !== $required_fields) {
            redirect($pageurl,"Formato de archivo incorrecto. Verifica los encabezados.",null,\core\output\notification::NOTIFY_ERROR);
        }

        // Eliminar registros anteriores.
        $delete_params = ['customerid' => $customerid];
        if ($groupid > 0) $delete_params['groupid'] = $groupid;
        $DB->delete_records('trainingplan', $delete_params);

        foreach ($lines as $line_num => $line) {
            
            $data = str_getcsv($line, $separator);
            
            if (empty(trim($line))) continue;
            
            if (count($data) !== count($header)) {
                $errors[] = "Error en línea {$line_num}: Número incorrecto de columnas.";
                break;
            }
    
            $row = array_combine($header, $data);
    
            if (intval($row['customer'])!==$customerid) continue; //Filtrar por customer
    
            if (intval($row['group']) !== $groupid && $groupid > 0) continue; // Filtrar por grupo.
                    
            if (!grupoExiste($customerid, intval($row['group']))) {
                $errors[] = "Error en línea {$line_num}: Grupo inválido.";
                break;
            }
        
            if (empty($row['wbs']) || strlen($row['wbs']) > 40) {
                $errors[] = "Error en línea {$line_num}: 'wbs' inválido (máx 40 caracteres).";
                break;
            }
            
            $parsed_startdate = \DateTime::createFromFormat('d/m/Y', $row['startdate']);
            if (!$parsed_startdate) {
                $errors[] = "Error en línea {$line_num}: Fecha startdate inválida '{$row['startdate']}'.";
                break;
            }
            $parsed_startdate=$parsed_startdate->getTimestamp();

            $parsed_enddate = \DateTime::createFromFormat('d/m/Y', $row['enddate']);
            if (!$parsed_enddate) {
                $errors[] = "Error en línea {$line_num}: Fecha enddate inválida '{$row['enddate']}'.";
                break;
            }
            $parsed_enddate=$parsed_enddate->getTimestamp();

            // Mapear campos
            $record = (object) [
                'customerid' => $row['customer'],
                'groupid' => intval($row['group']),
                'wbs' => $row['wbs'],
                'course' => $row['course'],
                'startdate' => $parsed_startdate,
                'enddate' => $parsed_enddate,
                'num_trainees' => intval($row['num_trainees']),
                'trainees' => $row['trainees'],
                'location' => $row['location'],
                'provider' => $row['provider'],
                'lastupdate' => time(),
            ];

            if (!$DB->insert_record('trainingplan', $record)) {
                $errors[] = "Error en línea {$line_num}: No se pudo insertar el registro.";
            }
            $inserted_count++;
        }

        // Notificar errores y resultados.
        if (!empty($errors)) {
            $error_message="";
            foreach ($errors as $error) {
                $error_message.=$error;
            }
            redirect($pageurl,$error_message,null,\core\output\notification::NOTIFY_ERROR);
        }

        // Éxito
        redirect($pageurl,"Operación exitosa: Se insertaron {$inserted_count} registros.",null,\core\output\notification::NOTIFY_SUCCESS);
        
    } catch (Exception $e) {
        echo $OUTPUT->notification("Error: " . $e->getMessage(), 'notifyproblem');
        $mform->display(); // Volver a mostrar el formulario
    }
    
} else {
    $mform->display();
}

echo $OUTPUT->footer();

// Funciones auxiliares
function removeBomKeys(array $data): array {
    return array_map(fn($key) => preg_replace('/^\xEF\xBB\xBF/', '', $key), $data);
}

function grupoExiste($customerid, $groupid) {
    global $DB;
    return $DB->record_exists('grouptrainee', ['customer' => $customerid, 'id' => $groupid]);
}

function removeBom($string) {
    // Eliminar BOM si existe
    return preg_replace('/^\xEF\xBB\xBF/', '', $string);
}
