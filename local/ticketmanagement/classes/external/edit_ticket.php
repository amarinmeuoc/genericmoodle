<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

require_once($CFG->libdir . '/filelib.php');


class edit_ticket extends \core_external\external_api {
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
                    'cancelled' => new external_value(PARAM_INT, 'Cancelled status (0 or 1)'),
                    'state' => new external_value(PARAM_TEXT, 'Current state of the ticket'),
                    'priority' => new external_value(PARAM_TEXT, 'Priority level of the ticket'),
                    'closed' => new external_value(PARAM_INT, 'Closed status (0 or 1)'),
                    'category' => new external_value(PARAM_INT, 'Category ID'),
                    'eventoCat' => new external_value(PARAM_TEXT, 'Priority level of the ticket'),
                    'eventoSubCat' => new external_value(PARAM_TEXT, 'Priority level of the ticket'),
                    'eventoPriority' => new external_value(PARAM_TEXT, 'Priority level of the ticket'),
                   
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
        $record->subcategoryid = $ticket['category'];
        $record->priority = $ticket['priority'];
        
        //$record->lastupdate = time(); // Marca la fecha de actualización

        // Maneja los archivos adjuntos
        
        $userid=$DB->get_record('ticket',['id'=>$ticket['ticketid']],'userid')->userid;
 
        $ticketid=$ticket['ticketid'];
        $eventoCat=$ticket['eventoCat'];
        $eventoSubCat=$ticket['eventoSubCat'];
        $eventoPriority=$ticket['eventoPriority'];

    // Optionally handle ticket closure or cancellation
    if (!empty($ticket['closed']) && $ticket['closed'] === 1) {
        $record->state = 'Closed';
    } elseif (!empty($ticket['cancelled']) && $ticket['cancelled'] === 1) {
        $record->state = 'Cancelled';
    } else {
        $record->state = $ticket['state'];
    }

    try {
        $DB->update_record('ticket', $record);
        $userid=$DB->get_record('ticket',['id'=>$ticketid],'assigned')->assigned;
        $dateaction=time();
        $message="Ticket updated";

        if ($eventoCat!==''){
            $message.=", New Category: ".$eventoCat;
        }

        if ($eventoSubCat!==''){
            $message.=", New SubCategory: ".$eventoSubCat;
        }

        if ($eventoPriority!==''){
            $message.=", New Priority: ".$eventoPriority;
        }

        if ($record->state==='Cancelled'){
            $message="Ticket Cancelled";
        }

        if ($record->state==='Closed'){
            $message="Ticket Closed";
        }

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
                'state' => new external_value(PARAM_TEXT, 'Current state of the ticket'),
                'priority' => new external_value(PARAM_TEXT, 'Priority level of the ticket'),
            ]),
            'success' => new external_value(PARAM_BOOL, 'Status of the ticket update'),
            'message' => new external_value(PARAM_TEXT, 'Error message if update fails', VALUE_OPTIONAL),
        ]);

    }   

}