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
 * @package    report_partialplan
 * @subpackage traineereport
 * @copyright  2024 Alberto Marín
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require(__DIR__.'/../../config.php');
global $USER, $PAGE, $OUTPUT, $CFG;

require_login();

$url=new moodle_url('/report/partialplan/index.php');


//Getting the id of frontpage and setting the context
//All users enroled in the main course will get access to the reports
$context=\context_course::instance(1);
$PAGE->set_context($context);

$PAGE->set_pagelayout('report');
$PAGE->set_title('ITP Reports view');
$PAGE->set_url($url);


if (!has_capability('report/partialplan:view',$context)){   
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


if (preg_match('/(controller|manager|logistic)/i', $role)) {
    $mform= new \report_partialplan\form\Controller_Form();
    $PAGE->requires->js_call_amd('report_partialplan/init_con', 'init');
} elseif (preg_match('/observer/i', $role)) {
    $mform= new \report_partialplan\form\Observer_Form();
    $PAGE->requires->js_call_amd('report_partialplan/init_obv', 'init');
} else {
    echo $OUTPUT->header();       
    $message="<h1>There is some missing information in your user setup!!.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}




echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('titlelegend', 'report_partialplan'));

// Set anydefault data (if any).
$mform->set_data($toform);

// Display the form.
$form_html = $mform->render();

$data = [
    'form'=>$form_html,
];



echo $OUTPUT->render_from_template('report_partialplan/content_con', $data);

echo $OUTPUT->footer();



