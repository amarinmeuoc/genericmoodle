<?php
namespace local_ticketmanagement\task;

defined('MOODLE_INTERNAL') || die();

class add_notification_task extends \core\task\adhoc_task {
    public function execute() {
        global $DB;
    
        $data = $this->get_custom_data();

        if (empty($data) || !isset($data->to, $data->from)) {
            mtrace("Error: Invalid/missing custom data!");
            return;
        }
        
        $event = \core\event\notification_sent::create([
            'context' => \context_system::instance(),
            'userid' => $data->to,
            'relateduserid' => $data->from,
            'objectid' => $data->to, // Required when using events
            'other' => [
                'courseid' => SITEID, // Always required for notification_sent
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
        
        try {
            message_send($message);
        } catch (\Exception $e) {
            mtrace("Error sending message: " . $e->getMessage());
        }
    }
}