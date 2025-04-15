<?php
function xmldb_local_ticketmanagement_upgrade($oldversion) {
    global $DB;

    mtrace("Starting local_ticketmanagement upgrade from version $oldversion");


    $dbman = $DB->get_manager();

    if ($oldversion < 2024042207) {
        mtrace("Processing upgrade to version 2024042203");

        // Define table ticketmanagement_cars to be created
        $table = new xmldb_table('ticketmanagement_fines');
        
        // Add fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('reminder', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('expiration_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('payment_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('ticketid', XMLDB_TYPE_CHAR, '36', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('carid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Add keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_ticketid', XMLDB_KEY_FOREIGN, ['ticketid'], 'ticket', ['id']);
        $table->add_key('fk_userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('fk_carid', XMLDB_KEY_FOREIGN, ['carid'], 'ticketmanagement_cars', ['id']);

        if (!$dbman->table_exists($table)) {
            mtrace("Creating table ticketmanagement_fines");
            $dbman->create_table($table);
            mtrace("Table created successfully");
        } else {
            mtrace("Table already exists");
        }

        upgrade_plugin_savepoint(true, 2024042203, 'local', 'ticketmanagement');
        mtrace("Upgrade to 2024042203 completed");

    }

    return true;
}
?>