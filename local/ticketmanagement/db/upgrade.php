<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_ticketmanagement_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023061000) {
        // Define table ticketmanagement_address to be created.
        $table = new xmldb_table('ticketmanagement_address');

        // Adding fields to table ticketmanagement_address.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('address', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('house', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('floor', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('block', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('door', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('number', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('town', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('entry_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('departure_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table ticketmanagement_address.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for ticketmanagement_address.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ticketmanagement savepoint reached.
        upgrade_plugin_savepoint(true, 2023061000, 'local', 'ticketmanagement');
    }

    return true;
}