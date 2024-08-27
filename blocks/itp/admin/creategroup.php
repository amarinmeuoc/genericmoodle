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
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the local_createcustomer plugin code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    local_createcustomer
 * @copyright  2024 Alberto Marín Mendoza (http://myhappycoding.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once('../../../config.php');
 require_once($CFG->libdir.'/adminlib.php');
 

 require_login();

 // Configura la página para que se mantenga en el contexto de administración.
 admin_externalpage_setup('block_itp_creategroup');

 $context=context_system::instance();
 if (!has_capability('moodle/site:config',$context)) {
    echo $OUTPUT->header();
    $message=get_string('error','block_itp');
    \core\notification::error($message);
    echo $OUTPUT->footer();
    return;
 }
 
 $mform=new \block_itp\form\creategroupform();
 echo $OUTPUT->header();

 echo $OUTPUT->heading(get_string('creategroup', 'block_itp'));

 // Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($fromform = $mform->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    // Set anydefault data (if any).
    $mform->set_data($toform);

    // Display the form.
    $mform->display();
}

 echo $OUTPUT->footer();

 