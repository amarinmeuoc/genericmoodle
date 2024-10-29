<?php

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_ticketmanagement_subcategory');
$context=context_system::instance();
 if (!has_capability('moodle/site:config',$context)) {
    echo $OUTPUT->header();
    $message=get_string('error','local_ticketmanagement');
    \core\notification::error($message);
    echo $OUTPUT->footer();
    return;
 }

 $mform=new \local_ticketmanagement\form\createsubcategoryform();

 echo $OUTPUT->header();

 echo $OUTPUT->heading(get_string('createsubcategory', 'local_ticketmanagement'));

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