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

         //Se obtiene valor 0: El usuario no puede enviar feedback, ó 1: El usuario puede enviar feedback
         $allowfeedback=$DB->get_field('ticket', 'communication', ['id'=>$ticketid], IGNORE_MISSING);
         

         //Se listan todos los grupos del cliente seleccionado
         $result=$DB->get_records('ticket_action', ['ticketid'=>$ticketid], 'dateaction ASC', 'id,action,internal,dateaction,userid,ticketid');
         
         // Recorrer cada acción del resultado
        foreach ($result as $key => $action) {
            // Obtener el nombre y apellidos del usuario usando el userid
            $user = $DB->get_record('user', ['id' => $action->userid], 'firstname, lastname');
            
            // Formatear la fecha
            $formattedDate = date('d-m-Y H:i', $action->dateaction); // Asegúrate de que dateaction es un timestamp

            // Añadir los datos formateados al nuevo array
            $formatedResult[] = [
                'id' => $action->id,
                'action' => strip_tags($action->action),
                'hiddenmessage'=>strip_tags($action->internal),
                'dateaction' => strip_tags($formattedDate),
                'user' => isset($user) ? $user->firstname . ' ' . $user->lastname : 'Usuario desconocido', // Manejar caso de usuario no encontrado
                'ticketid' => $action->ticketid
            ];
        }

        return [
                'result'=>$formatedResult,
                'allowfeedback'=> $allowfeedback
            ];

        
    }


    public static function execute_returns() {
        return new external_single_structure([
            'result' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Group id'),
                    'action' => new external_value(PARAM_TEXT, 'Action name'),
                    'hiddenmessage' => new external_value(PARAM_TEXT, 'hidden message'),
                    'dateaction' => new external_value(PARAM_TEXT, 'Date of action'),
                    'user' => new external_value(PARAM_TEXT, 'Username'),
                    'ticketid' => new external_value(PARAM_TEXT, 'Ticket ID')
                ])
            ),
            'allowfeedback' => new external_value(PARAM_BOOL, 'Whether feedback is allowed (0 or 1)')
        ]);
    }
    
}