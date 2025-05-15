<?php
namespace local_ticketmanagement\task;

defined('MOODLE_INTERNAL') || die();

class add_notification_task extends \core\task\adhoc_task {
    public function execute() {
        global $DB;
    
        $data = $this->get_custom_data();
        
        $event = \core\event\notification_sent::create([
            'context' => \context_system::instance(),
            'userid' => $data->to, // Recipient ID
            'relateduserid' => $data->from, // Sender ID
            'other' => [
                'subject' => $data->subject,
                'fullmessage' => $data->message_plain,
                'fullmessagehtml' => $data->message_html,
            ]
        ]);
        $event->trigger();
        
        // Alternative: Direct message_send()
        $message = new \core\message\message();
        $message->component = 'local_ticketmanagement';
        $message->name = 'ticket_notification';
        $message->userfrom = $data->from;
        $message->userto = $data->to;
        $message->subject = $data->subject;
        $message->fullmessage = $data->message_plain;
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml = $data->message_html;
        $message->smallmessage = strip_tags($data->message_plain); // For popups
        $message->notification = 1; // Mark as notification (not private message)
        
        message_send($message);
    }
}