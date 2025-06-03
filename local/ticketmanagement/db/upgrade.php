<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_ticketmanagement_upgrade($oldversion) {
    global $CFG;
    
    // Force task re-registration
    if ($oldversion < 2025042815) {
        require_once($CFG->dirroot.'/lib/adminlib.php');
        \core\task\manager::reset_scheduled_tasks_for_component('local_ticketmanagement');
    }
    
    return true;
}