<?php
namespace local_ticketmanagement\task;

defined('MOODLE_INTERNAL') || die();

class send_email_task extends \core\task\adhoc_task {
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->libdir . '/phpmailer/moodle_phpmailer.php');

        $data = $this->get_custom_data(); // Data passed when queuing the task.
        

        $fromuser = \core_user::get_user($data->from);
        $touser = \core_user::get_user($data->to);

        email_to_user(
            $touser,
            $fromuser,
            $data->subject,
            $data->message_plain, // Optional: plaintext version.
            $data->message_html,   // HTML message.
            $data->attachment,     // Optional: attachment path.
            $data->attachmentname  // Optional: attachment name.
        );
    }
}