<?php
class check_fine_reminders extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('checkfinereminders', 'local_ticketmanagement');
    }

    public function execute() {
        global $DB, $USER;
        
        // Get fines needing reminders (within 7 days of expiration)
        $time = time();
        $reminderwindow = $time + (7 * 24 * 60 * 60); // 7 days ahead
        
        $sql = "SELECT f.*, t.assigned 
                FROM {ticketmanagement_fines} f
                JOIN {ticket} t ON f.ticketid = t.id
                WHERE f.reminder = 1 
                AND f.expiration_date BETWEEN ? AND ?
                AND f.status = 'pending'";
        
        $fines = $DB->get_records_sql($sql, [$time, $reminderwindow]);
        
        foreach ($fines as $fine) {
            $this->send_reminder_notification($fine);
        }
    }
    
    protected function send_reminder_notification($fine) {
        $assigneduser = \core_user::get_user($fine->assigned);
        
        $message = new \core\message\message();
        $message->component = 'local_ticketmanagement';
        $message->name = 'finereminder';
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $assigneduser;
        $message->subject = get_string('fineremindersubject', 'local_ticketmanagement');
        $message->fullmessage = get_string('finereminderbody', 'local_ticketmanagement', [
            'expiration' => userdate($fine->expiration_date),
            'ticketid' => $fine->ticketid
        ]);
        $message->fullmessageformat = FORMAT_HTML;
        $message->smallmessage = get_string('fineremindersmall', 'local_ticketmanagement');
        $message->notification = 1;
        
        message_send($message);
    }
}