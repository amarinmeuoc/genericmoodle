<?php
namespace local_ticketmanagement\task;

defined('MOODLE_INTERNAL') || die();

class sync_address extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('sync_address_task', 'local_ticketmanagement');
    }

    public function execute() {
        global $DB;
        
        mtrace("Starting address synchronization task...");
        
        try {
            // Get all active addresses (without departure date)
            $sql = "SELECT ta.*
                    FROM {ticketmanagement_address} ta
                    JOIN {user} u ON u.id = ta.userid
                    WHERE ta.departure_date IS NULL OR ta.departure_date = 0";
            
            $addresses = $DB->get_records_sql($sql);
            
            if (empty($addresses)) {
                mtrace("No addresses to synchronize found.");
                return;
            }
            
            mtrace("Found " . count($addresses) . " addresses to synchronize.");
            
            $updated = 0;
            foreach ($addresses as $address) {
                $user = $DB->get_record('user', ['id' => $address->userid]);
                
                if (!$user) {
                    mtrace("User with ID {$address->userid} not found, skipping...");
                    continue;
                }

                $pattern="/Carraca -Cuatro Torres-/i";
                $pattern2="/Carraca -Houses-/i";

                if (preg_match($pattern,$address->address)){
                    // Prepare address components
                    $full_address = trim(implode(' ', [
                        $address->address.', ',
                        'Floor: '.$address->floor,
                        'Number: '.$address->number
                    ]));
                } elseif (preg_match($pattern2,$address->address)){
                    // Prepare address components
                    // Prepare address components
                    $full_address = trim(implode(' ', [
                        $address->address.', ',
                        'House: '.$address->house+1,
                        'Number: '.$address->number
                    ]));
                } else {
                    // Prepare address components
                    $full_address = trim(implode(' ', [
                        $address->address.', ',
                        'Bloque: '.$address->block,
                        'Door: '.$address->door,
                        'Number: '.$address->number
                    ]));
                }
                
                
                // Only update if there are changes
                if ($user->address !== $full_address || $user->city !== $address->town) {
                    $user->address = $full_address;
                    $user->city = $address->town;
                    
                    user_update_user($user, false);
                    $updated++;
                    
                    mtrace("Updated address for user {$user->id} ({$user->username})");
                }
            }
            
            mtrace("Task completed. {$updated} users updated.");
        } catch (\Exception $e) {
            mtrace("Error during synchronization: " . $e->getMessage());
        }
    }
}