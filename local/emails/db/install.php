<?php

function xmldb_local_emails_install() {
    global $DB;
    
    $dbman = $DB->get_manager();
    
    $table = new xmldb_table('local_emails_log');
    
    // Adding fields
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('message', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
    $table->add_field('recipientcount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('timesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    
    // Adding keys
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
    
    // Create table
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    return true;
}