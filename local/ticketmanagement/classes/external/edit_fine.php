<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class edit_fine extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'id' => new external_value(PARAM_INT, 'User ID'),
                   'reminder' => new external_value(PARAM_INT, '1 reminder activated / 0 reminder not activated'),
                    'ticketId' => new external_value(PARAM_TEXT, 'Ticket ID'),
                    'status' => new external_value(PARAM_TEXT, 'Car status'),
                    
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
        global $DB;
        
       // $params=self::validate_parameters(self::execute_parameters(), ['newTicket' => $newTicket]);
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        
        $id=$request['params'][0]['id'];   
        $reminder=$request['params'][0]['reminder'];   
        $ticketId=$request['params'][0]['ticketId'];   
        $status=$request['params'][0]['status'];
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        $record= new \stdClass();
        $record->id=$id;
        
        $record->ticketid=$ticketId;
        $record->status=$status;
        $record->reminder=$reminder;
        if ($status!=='pending'){
            $record->payment_date=time();
        } else {
            $record->payment_date=0;
        }

        $success = $DB->update_record('ticketmanagement_fines', $record);
        if (!$success) {
            throw new moodle_exception('Failed to update record');
        }
        


        $ObjReturn=[
            'listadoFine'=>[
                'id'=>$record->id,
                'ticketId'=>$record->ticketId,
                'status'=>$record->status,
                'reminder'=>$record->reminder,
            ]
        ];

        // Retornar una respuesta (ej. el ID del nuevo ticket creado)
        return $ObjReturn;
    }


    public static function execute_returns() {
        return new external_single_structure([
            'listadoFine' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID of fine table'),
                'ticketId' => new external_value(PARAM_TEXT, 'ticketId of the fine'),
                'status' => new external_value(PARAM_TEXT, 'status of the fine'),
                'reminder' => new external_value(PARAM_TEXT, 'reminder of the fine'),
            ])
        ]);
    }

}