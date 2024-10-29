<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Config changes report
 *
 * @package    report_coursereport
 * @subpackage traineereport
 * @copyright  2024 Alberto Marín
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require(__DIR__.'/../../config.php');
global $USER, $PAGE, $OUTPUT, $CFG;

require_login();

//Cargamos el javascript de forma asíncrona
//Se carga el módulo de exportar a Excel
$PAGE->requires->js_call_amd('report_coursereport/loadModules', 'init');


$url=new moodle_url('/report/coursereport/index.php');

//Getting the id of frontpage and setting the context
//All users enroled in the main course will get access to the reports
$context=\context_course::instance(1);
$PAGE->set_context($context);

$PAGE->set_pagelayout('report');
$PAGE->set_title('List of Course Reports');
$PAGE->set_url($url);

//Comprobamos permisos de acceso
if (!has_capability('report/coursereport:view',$context)){   
    echo $OUTPUT->header();       
    $message="<h1>Error: Access forbidden!!.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}

//Cargar los campos personalizados
profile_load_custom_fields($USER);

// Acceder al valor del campo personalizado role
$role = $USER->profile['role'];


if (preg_match('/(controller|manager)/i', $role)) {
    $mform= new \report_coursereport\form\form_con();
    $PAGE->requires->js_call_amd('report_coursereport/init_con', 'loadTemplate');
} elseif (preg_match('/observer/i', $role)) {
    $mform= new \report_coursereport\form\form_obv();
    
    $PAGE->requires->js_call_amd('report_coursereport/init_obv', 'loadTemplate');
} else {
    echo $OUTPUT->header();       
    $message="<h1>There is some missing information in your user setup!!.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}



echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('titlelegend', 'report_coursereport'));

// Set anydefault data (if any).
$mform->set_data($toform);

// Display the form.
$form_html = $mform->render();

$data = [
    'form' => $form_html,
];

if (preg_match('/(controller|manager)/i', $role)) {
    echo $OUTPUT->render_from_template('report_coursereport/content_con', $data);
} elseif (preg_match('/observer/i', $role)) {
    echo $OUTPUT->render_from_template('report_coursereport/content_obv', $data);
} 





echo $OUTPUT->footer();