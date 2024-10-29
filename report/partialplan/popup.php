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
global $USER, $PAGE;

require_login();
//$courseid = optional_param('courseid', '0', PARAM_TEXT);
//$userid = optional_param('userid', $USER->id, PARAM_TEXT);

$url=new moodle_url('/report/partialplan/popup.php');

$PAGE->set_url($url);
$context=\context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title('ITP Reports view');

$popup_form= new \report_partialplan\form\modal_form();

$toform="";
// Form processing and displaying is done here.
if ($popup_form->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($fromform = $popup_form->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    // Set anydefault data (if any).
    $popup_form->set_data($toform);

    // Display the form.
    $popup_form->display();
}




