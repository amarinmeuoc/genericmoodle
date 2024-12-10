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
 * @package    report_coursereportadmin
 * @subpackage traineereport
 * @copyright  2024 Alberto Marín
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require(__DIR__.'/../../config.php');
global $USER, $PAGE, $OUTPUT, $CFG;

require_login();

$PAGE->requires->js_call_amd('report_coursereportadmin/loadModules', 'init');
$url=new moodle_url('/report/coursereportadmin/index.php');

//Getting the id of frontpage and setting the context
//All users enroled in the main course will get access to the reports
$context=\context_course::instance(1);
$PAGE->set_context($context);

$PAGE->set_pagelayout('report');
$PAGE->set_title('List of Course Reports');
$PAGE->set_url($url);

if (!has_capability('report/coursereportadmin:view',$context)){   
    echo $OUTPUT->header();       
    $message="<h1>Error: Access forbidden!!.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('titlelegend', 'report_coursereportadmin'));
$clientes=$DB->get_records('customer', [], '', 'id,shortname', 0, 0);
$clientes=array_values($clientes);

$token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);

$data = [ 
    'token'=>($token)?$token->token:'',
    'customers'=>$clientes
];

echo $OUTPUT->render_from_template('report_coursereportadmin/content', $data);
echo $OUTPUT->footer();



