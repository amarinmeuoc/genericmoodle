<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;




class get_ticket_actions_excel extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   
                   'startdate' => new external_value(PARAM_INT, 'ID del aprendiz'),
                   'enddate' => new external_value(PARAM_INT, 'ID del aprendiz'),
                   'state'=>new external_value(PARAM_TEXT,'Estado del ticket'),
                   'gestor'=>new external_value(PARAM_INT,'Gestor encargado del ticket')
                   
                ])
            ) 
        ]);
    }


    public static function execute($params) {
        global $DB;
        
        // Validar parámetros
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        
        // Obtener los valores de los parámetros
        $startdate = $request['params'][0]['startdate'];
        $enddate = $request['params'][0]['enddate'];     
        $state = $request['params'][0]['state']; 
        $gestor = $request['params'][0]['gestor']; 
        
        // Comprobaciones de seguridad
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);
    
        // Consulta base para obtener los tickets
        $sql = "SELECT * FROM {ticket}
                WHERE dateticket >= :startdate AND dateticket <= :enddate";
    
        // Filtro por 'gestor' si no es 0
        if ($gestor != 0) {
            $sql .= " AND assigned = :assigned";
        }
    
        // Filtro por 'state' si no es 'all'
        if ($state != 'all') {
            $sql .= " AND state = :stateval";
        }
    
        // Ordenar por fecha de creación del ticket
        $sql .= " ORDER BY dateticket ASC";
    
        // Parámetros de la consulta
        $params_array = [
            'startdate' => $startdate,
            'enddate' => $enddate,
            'assigned' => ($gestor != 0) ? $gestor : null,
            'stateval' => ($state != 'all') ? $state : null,
        ];
    
        // Ejecutar la consulta para obtener los tickets
        $tickets = $DB->get_records_sql($sql, $params_array);
    
        // Array para almacenar las acciones
        $actions = [];
    
        // Recorrer los tickets y obtener las acciones correspondientes
        foreach ($tickets as $ticket) {
            // Obtener las acciones de este ticket
            $ticket_actions = $DB->get_records('ticket_action', ['ticketid' => $ticket->id], 'dateaction', 'action, internal, dateaction, userid, ticketid');
    
            foreach ($ticket_actions as $action) {
                // Obtener el nombre y apellidos del usuario con el userid
                $user = $DB->get_record('user', ['id' => $action->userid], 'firstname, lastname');
                if ($user) {
                    // Concatenar el nombre y apellido
                    $user_full_name = $user->firstname . ' ' . $user->lastname;
                    $pattern='/service/i';
                    if (preg_match($pattern,$user_full_name)){
                        $user_full_name='No controller assigned';
                    }
                } else {
                    // Si no se encuentra el usuario, usar un valor por defecto
                    $user_full_name = 'Usuario no encontrado';
                }
    
                // Añadir la acción con el nombre completo del usuario
                $actions[] = [
                    'action' => $action->action,
                    'internal' => $action->internal,
                    'dateaction' => $action->dateaction,
                    'user' => $user_full_name,  // Reemplazamos el userid por el nombre completo
                    'ticketid' => $ticket->id // Añadir el ticketid para cada acción
                ];
            }
        }
        
        // Devolver la lista de acciones por ticket
        return [
            'listofactions' => $actions,
        ];
    }
    
    
    /**
     * Devuelve la estructura de los datos que se van a devolver en el servicio
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'listofactions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'action' => new external_value(PARAM_TEXT, 'Descripción de la acción'),
                            'internal' => new external_value(PARAM_TEXT, 'Mensaje interno'),
                            'dateaction' => new external_value(PARAM_INT, 'Fecha y hora de la acción'),
                            'user' => new external_value(PARAM_TEXT, 'Nombre completo del usuario que realizó la acción'),
                            'ticketid' => new external_value(PARAM_TEXT, 'ID del ticket') // Asegúrate de que ticketid es un text
                        )
                    )
                ),
            )
        );
    }
}