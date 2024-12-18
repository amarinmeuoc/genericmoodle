<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

require_once($CFG->libdir . '/filelib.php');


class edit_ticket_byUser extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'ticketid' => new external_value(PARAM_TEXT, 'Ticket ID'),
                    'fileid' => new external_value(PARAM_INT, 'File ID for attachments'),
                    'userid'=> new external_value(PARAM_TEXT, 'user ID'),
                   
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
        $record->userid=$ticket['userid'];
        
        //$record->lastupdate = time(); // Marca la fecha de actualización

        // Maneja los archivos adjuntos
        //$draftitemid = $ticket['fileid'];
        //$context = \context_system::instance();
        //$filearea = 'sharedfiles';
        //$component = 'local_ticketmanagement';
        //$ticketid=$ticket['ticketid'];
        
        // Guarda los archivos en el área de borrador
        /*file_save_draft_area_files(
            $draftitemid,
            $context->id,
            $component,
            $filearea,
            $ticketid,
            [
                'subdirs' => 0,
                'maxbytes' => 10485760, // 10MB
                'maxfiles' => 50,
                'accepted_types' => ['document'], // Solo documentos
            ]
        );
        */
    try {
        $DB->update_record('ticket', $record);
        $userid=$record->userid;
        $dateaction=time();
        $message="Ticket updated";

        $DB->execute("INSERT INTO {ticket_action} (action, dateaction, userid, ticketid)
                VALUES (?,?,?,?)",
                array($message,$dateaction,$userid,$ticketid));

        
        // Return the ticket object
        return [
            'ticket' => [
                'ticketid' => $ticket['ticketid'],
                'state' => $record->state,
                'priority' => $ticket['priority']
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
            ]),
            'success' => new external_value(PARAM_BOOL, 'Status of the ticket update'),
            'message' => new external_value(PARAM_TEXT, 'Error message if update fails', VALUE_OPTIONAL),
        ]);

    }   

}