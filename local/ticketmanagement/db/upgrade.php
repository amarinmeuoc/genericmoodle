<?php
function xmldb_local_ticketmanagement_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025042702) { // Use today's date in YYYYMMDDXX format (XX is 00 for first version of the day)
        
        // Define field passport to be added to family
        $table = new xmldb_table('family');
        $field = new xmldb_field('passport', XMLDB_TYPE_CHAR, '25', null, null, null, null, 'userid');

        // Conditionally launch add field
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Plugin savepoint reached
        upgrade_plugin_savepoint(true, 2025042702, 'local', 'ticketmanagement');
    }

    return true;
}