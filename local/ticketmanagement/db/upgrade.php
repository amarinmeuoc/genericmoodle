<?php
function xmldb_local_ticketmanagement_upgrade($oldversion) {
    global $DB;

    mtrace("Starting local_ticketmanagement upgrade from version $oldversion");


    $dbman = $DB->get_manager();

    if ($oldversion < 2024042206) {
        mtrace("Processing upgrade to version 2024042203");

        // Define table ticketmanagement_cars to be created
        $table = new xmldb_table('ticketmanagement_cars');
        
        // Add fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('brand', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('model', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('color', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('platenumber', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('delivery_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('refund_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Add keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        if (!$dbman->table_exists($table)) {
            mtrace("Creating table ticketmanagement_cars");
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