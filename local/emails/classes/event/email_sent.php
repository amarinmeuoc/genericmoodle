<?php
namespace local_emails\event;

defined('MOODLE_INTERNAL') || die();

class email_sent extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'c'; // c = create
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user'; // ✅ Add this
    }

    public static function get_name() {
        return get_string('eventemailsent', 'local_emails');
    }

    public function get_description() {
        return "The user with id '{$this->userid}' sent an email to user id '{$this->other['recipientid']}' with subject '{$this->other['subject']}'.";
    }

    public function get_url() {
        return new \moodle_url('/local/emails/index.php');
    }

    protected function get_legacy_logdata() {
        // Optional: for legacy log support
        return array($this->courseid, 'local_emails', 'email sent', 'index.php', $this->other['subject']);
    }

    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['recipientid']) || !isset($this->other['subject'])) {
            throw new \coding_exception('Missing required information in event: recipientid and subject.');
        }
    }
}
