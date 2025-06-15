<?php
namespace local_emails\event;

defined('MOODLE_INTERNAL') || die();

class email_sent extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_emails'; // Reemplaza con tu tabla si es necesario
    }

    public static function get_name() {
        return get_string('eventemailsent', 'local_emails');
    }

    public function get_description() {
        return "The user with id {$this->userid} sent an email.";
    }

    public function get_url() {
        return new \moodle_url('/local/emails/index.php'); // Ajusta la URL según tu plugin
    }
}