<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_ticketmanagement_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Nueva tabla a partir de la versión 2025101100
    if ($oldversion < 2025101100) {

        // Define table local_ticketmanagement
        $table = new xmldb_table('local_ticketmanagement_departurealert');

        // Añadir campos
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('departuredate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Clave primaria
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Índice
        $table->add_index('userid_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        // Crear la tabla si no existe
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
            upgrade_plugin_savepoint(true, 2025101100, 'local', 'ticketmanagement');
        }
    }

    return true;
}
