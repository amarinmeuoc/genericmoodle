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
global $USER,$DB,$PAGE;

require_login();
$PAGE->set_context(\context_system::instance());
$message=optional_param('messagebody', '', PARAM_RAW);
$to=optional_param('to', '', PARAM_TEXT);
$subject=optional_param('subject', '', PARAM_TEXT);
$to=explode(',',$to);
$result=true;

// Obtén el objeto de usuario "no-reply" configurado en Moodle
$noreplyuser = core_user::get_noreply_user();

foreach ($to as $email){
    $selectedUser = $DB->get_record("user", ["email"=>$email]);
    if (!email_to_user($selectedUser,$USER,$subject,'',$message,'',''))
        $result=false;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['result'=>$result]);