<?php
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/local/ticketmanagement/classes/form/uploadFamilyformcsv.php');

admin_externalpage_setup('local_ticketmanagement_uploadfamily_csv');
$context = \context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new \moodle_url('/local/ticketmanagement/admin/upload_family_csv.php'));
$PAGE->set_title(get_string('uploadfamilydetails', 'local_ticketmanagement'));
$PAGE->set_heading(get_string('uploadfamilydetails', 'local_ticketmanagement'));

$mform = new local_ticketmanagement\form\uploadFamilyformcsv();
$previewdata = null;

// Handle form submission
if ($mform->is_cancelled()) {
    redirect(new \moodle_url('/admin/settings.php', ['section' => 'localpluginsticketmanagement']));
} else if ($formdata = $mform->get_data()) {
    // Get the file content using Moodle's file API
    $content = $mform->get_file_content('familycsvfile');
    if (empty($content)) {
        \core\notification::error(get_string('nofileuploaded', 'local_ticketmanagement'));
    } else {
        // Procesar el contenido CSV manualmente
        $delimiter = ($formdata->delimiter == 'tab') ? "\t" : $formdata->delimiter;
        $encoding = $formdata->encoding ?? 'UTF-8';
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        $lines = array_filter(explode(PHP_EOL, $content));

        $data = [];
        $columns = [];
        $line = 0;

        // Procesar encabezado si existe
        if (!empty($formdata->hasheaders)) {
            $columns = str_getcsv(array_shift($lines), $delimiter);
        } else {
            $columns = [
                0 => 'id',
                1 => 'relationship',
                2 => 'name',
                3 => 'lastname',
                4 => 'nie',
                5 => 'passport',
                6 => 'birthdate',
                7 => 'adeslas',
                8 => 'phone1',
                9 => 'email',
                10 => 'arrival',
                11 => 'departure',
                12 => 'notes',
                13 => 'userid'
            ];
        }

        // Verificar columnas requeridas
        $required = ['relationship', 'name', 'lastname', 'nie', 'birthdate', 'userid'];
        $missing = array_diff($required, $columns);

        if (!empty($missing)) {
            \core\notification::error(get_string('missingcolumns', 'local_ticketmanagement', implode(', ', $missing)));
        } else {
            foreach ($lines as $row) {
                $line++;
                $fields = str_getcsv($row, $delimiter);
                $record = array_combine($columns, $fields);

                // Validaciones y conversiones
                if (!$DB->record_exists('user', ['id' => $record['userid']])) {
                    \core\notification::warning(get_string('usernotfound', 'local_ticketmanagement', ['line' => $line, 'userid' => $record['userid']]));
                    continue;
                }

                if (!empty($record['birthdate']) && !is_numeric($record['birthdate'])) {
                    $record['birthdate'] = strtotime($record['birthdate']);
                }

                if (!empty($record['arrival']) && !is_numeric($record['arrival'])) {
                    $record['arrival'] = strtotime($record['arrival']);
                }

                if (!empty($record['departure']) && !is_numeric($record['departure'])) {
                    $record['departure'] = strtotime($record['departure']);
                }

                $obj = (object)$record;

                if (empty($formdata->preview)) {
                    if ($existing = $DB->get_record('family', ['id' => $obj->id])) {
                        $obj->id = $existing->id;
                        $DB->update_record('family', $obj);
                    } else {
                        $DB->insert_record('family', $obj);
                    }
                }

                $data[] = $obj;
            }

            $previewdata = $data;

            if (empty($formdata->preview)) {
                \core\notification::success(get_string('uploadsuccess', 'local_ticketmanagement', count($data)));
            } else {
                \core\notification::success(get_string('previewsuccess', 'local_ticketmanagement', count($data)));
            }
        }

    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadfamilydetails', 'local_ticketmanagement'));

// Add download button
$downloadurl = new \moodle_url('/local/ticketmanagement/admin/download_family_csv.php');
echo $OUTPUT->single_button($downloadurl, get_string('downloadcurrentdata', 'local_ticketmanagement'), 'get');

$mform->display();

// Show preview data if available
if (!empty($previewdata)) {
    $render = $OUTPUT->render_from_template('local_ticketmanagement/family_csv', ['records' => $previewdata]);
    echo $render;
}

echo $OUTPUT->footer();