<?php
defined('MOODLE_INTERNAL') || die();

function local_ticketmanagement_extend_navigation(global_navigation $navigation) {
    // Función vacía solo para forzar el reconocimiento del plugin
}

function local_ticketmanagement_update_user_addresses() {
    global $DB;
    mtrace("Iniciando actualización de direcciones...");
    
    $sql = "SELECT a.userid, a.address, a.house, a.floor, a.block, a.door, a.number, a.town
            FROM {ticketmanagement_address} a
            INNER JOIN (
                SELECT userid, MAX(entry_date) as max_entry
                FROM {ticketmanagement_address}
                WHERE departure_date IS NULL OR departure_date = 0
                GROUP BY userid
            ) b ON a.userid = b.userid AND a.entry_date = b.max_entry
            WHERE a.departure_date IS NULL OR a.departure_date = 0";
    
    mtrace("Consulta SQL: ".$sql);
    
    $addresses = $DB->get_records_sql($sql);
    mtrace("Registros encontrados: ".count($addresses));

    
    foreach ($addresses as $address) {
        $user = $DB->get_record('user', array('id' => $address->userid));
        
        if ($user) {
            // Update address fields - adjust these based on your user profile fields
            $updates = array();
            $updates['id'] = $user->id;
            
            // Map address components to user profile fields
            $updates['address'] = $address->address;
            $updates['city'] = $address->town;
            // Add other fields as needed
            
            $DB->update_record('user', $updates);
        }
    }
    
    return true;
}