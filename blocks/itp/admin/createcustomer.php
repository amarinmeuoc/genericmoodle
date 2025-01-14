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
 admin_externalpage_setup('block_itp_createcustomer');

 $context=context_system::instance();
 if (!has_capability('moodle/site:config',$context)) {
    echo $OUTPUT->header();
    $message=get_string('error','block_itp');
    \core\notification::error($message);
    echo $OUTPUT->footer();
    return;
 }
 
 $mform=new \block_itp\form\createcustomerform();
 echo $OUTPUT->header();

 echo $OUTPUT->heading(get_string('createcustomer', 'block_itp'));
 $toform='';

// Set anydefault data (if any).
$mform->set_data($toform);

// Display the form.
$mform->display();

 echo $OUTPUT->footer();

 
 