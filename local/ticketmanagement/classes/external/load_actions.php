<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class load_actions extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'ticketid'=>new external_value(PARAM_TEXT,'Ticket id'),
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
        
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        $ticketid=$request['params'][0]['ticketid'];
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);

         //Se listan todos los grupos del cliente seleccionado
         $result=$DB->get_records('ticket_action', ['ticketid'=>$ticketid], 'dateaction ASC', 'id,action,dateaction,userid,ticketid');
         
         // Recorrer cada acción del resultado
        foreach ($result as $key => $action) {
            // Obtener el nombre y apellidos del usuario usando el userid
            $user = $DB->get_record('user', ['id' => $action->userid], 'firstname, lastname');
            
            // Formatear la fecha
            $formattedDate = date('d-m-Y H:i', $action->dateaction); // Asegúrate de que dateaction es un timestamp

            // Añadir los datos formateados al nuevo array
            $formatedResult[] = [
                'id' => $action->id,
                'action' => $action->action,
                'dateaction' => $formattedDate,
                'user' => isset($user) ? $user->firstname . ' ' . $user->lastname : 'Usuario desconocido', // Manejar caso de usuario no encontrado
                'ticketid' => $action->ticketid
            ];
        }

        return $formatedResult;

        
    }


    public static function execute_returns() {
        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        return new external_multiple_structure(
            new external_single_structure([
                'id'=> new external_value(PARAM_INT,'Group id'),
                'action'=>new external_value(PARAM_TEXT,'action name'),
                'dateaction'=>new external_value(PARAM_TEXT,'date '),
                'user'=>new external_value(PARAM_TEXT,'username name'),
                'ticketid'=>new external_value(PARAM_TEXT,'ticket ID')
            ])
        );
    }
}