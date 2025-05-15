<?php
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');

admin_externalpage_setup('local_ticketmanagement_uploadfamily_csv');
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Set up CSV export
$filename = 'family_data_' . date('Ymd_His') . '.csv';
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);

// Add CSV headers
$headers = [
    'id',
    'relationship',
    'name',
    'lastname',
    'nie',
    'passport',
    'birthdate',
    'adeslas',
    'phone1',
    'email',
    'arrival',
    'departure',
    'notes',
    'userid'
];
$csvexport->add_data($headers);

// Get all family records
$records = $DB->get_records('family');

// Add data rows
foreach ($records as $record) {
    $csvexport->add_data([
        $record->id,
        $record->relationship,
        $record->name,
        $record->lastname,
        $record->nie,
        $record->passport,
        $record->birthdate,
        $record->adeslas,
        $record->phone1,
        $record->email,
        $record->arrival,
        $record->departure,
        $record->notes,
        $record->userid
    ]);
}

// Download the file
$csvexport->download_file();
exit;