<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

require_once($CFG->libdir . '/filelib.php');


class update_ticket_communication extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'ticketid' => new external_value(PARAM_TEXT, 'Ticket ID'),
                    'value' => new external_value(PARAM_BOOL, 'File ID for attachments'),
                    
                   
                ])
            ) 
        ]);
    }

        /**
     * Show Partial Training Plan
     * @param array A list of params for display the table
     * @return array Return a array of courses
     */
    public static function execute($params) {
        global $DB,$USER;
        
       // $params=self::validate_parameters(self::execute_parameters(), ['newTicket' => $newTicket]);
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        $ticket=$request['params'][0];

        // Prepara el registro para la actualización
        $record = new \stdClass();
        $record->id = $ticket['ticketid'];
        $record->communication = $ticket['value'];
        
        
        //$record->lastupdate = time(); // Marca la fecha de actualización

        // Maneja los archivos adjuntos
        
     

    try {
        $DB->update_record('ticket', $record);
    
        
        // Return the ticket object
        return [
            'ticket' => [
                'ticketid' => $ticket['ticketid'],
                'communication'=> $ticket['value']
            ],
            'success'=>true
        ];
    } catch (\Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }

    }


    public static function execute_returns() {
        return new external_single_structure([
            'ticket' => new external_single_structure([
                'ticketid' => new external_value(PARAM_TEXT, 'Ticket ID'),
                'communication' => new external_value(PARAM_BOOL, 'Current state of the ticket'),
            ]),
            'success' => new external_value(PARAM_BOOL, 'Status of the ticket update'),
            'message' => new external_value(PARAM_TEXT, 'Error message if update fails', VALUE_OPTIONAL),
        ]);

    }   

}