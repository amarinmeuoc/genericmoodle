<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;




class get_tickets_excel extends \core_external\external_api {
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
        
        $startdate=$request['params'][0]['startdate'];
        $enddate=$request['params'][0]['enddate'];     
        $state=$request['params'][0]['state']; 
        $gestor=$request['params'][0]['gestor']; 
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

    

        // Inicializa la consulta base
        $sql = "SELECT * FROM {ticket}
        WHERE dateticket >= :startdate AND dateticket <= :enddate";

        // Añadir el filtro de 'assigned' solo si es necesario (gestor != 0)
        if ($gestor != 0) {
        $sql .= " AND assigned = :assigned";
        }

        // Añadir el filtro de 'state' solo si es necesario (stateval != 'all')
        if ($state != 'all') {
        $sql .= " AND state = :stateval";
        }

        // Ordenar y limitar los resultados
        $sql .= " ORDER BY dateticket ASC";

        // Construir el array de parámetros dinámicamente
        $params_array = [
        'startdate' => $startdate,
        'enddate' => $enddate,
        'assigned' => ($gestor != 0) ? $gestor : null, // Solo asignar el valor si 'gestor' no es 0
        'stateval' => ($state != 'all') ? $state : null, // Solo asignar el valor si 'state' no es 'all'
        ];


        
        $tickets = $DB->get_records_sql($sql, $params_array);
 


        $formatted_tickets=[];
        foreach ($tickets as $ticket) {
            //Check the username of the person in charge
            $userincharge=$DB->get_record('user', ['id'=>$ticket->assigned], 'username,firstname,lastname');
            $user=$DB->get_record('user', ['id'=>$ticket->userid], 'id,username,firstname,lastname');
            if (!empty($user->id)) {
                profile_load_custom_fields($user);
            }

            // Obtener la última acción para el ticket especificado
            $lastAction = $DB->get_records('ticket_action', ['ticketid' => $ticket->id], 'dateaction DESC','*');
            $lastAction=array_values($lastAction);
            
            $subcategory=$DB->get_record('ticket_subcategory', ['id'=>$ticket->subcategoryid]);
            $category=$DB->get_record('ticket_category', ['id'=>$subcategory->categoryid],'category');
            $subcategory=$subcategory->subcategory;
            $familiar='';
            $familiar_type='';
            //Si tiene un familiar asignado
            if ($ticket->familiarid!==$ticket->userid){
                $familiar=$DB->get_field('family', 'name,lastname', ['id'=>$ticket->familiarid], IGNORE_MISSING);
                $familiar_type=$DB->get_field('family', 'relationship', ['id'=>$ticket->familiarid], IGNORE_MISSING);
                if ($familiar_type==='Wife')
                    $familiar_type='W';
                else if ($familiar_type==='Son')
                    $familiar_type='S';
                else if ($familiar_type==='Daughter')
                    $familiar_type='D';
                else
                    $familiar_type='O'; //Se llega aqui en caso de que sea famliar no reconocido.
            }
            $formatted_tickets[] = [
                'ticketnumber' => $ticket->id,
                'category'=>$category->category,
                'subcategory'=>$subcategory,
                'customer'=>$user->profile['customer'],
                'vessel'=>$user->profile['group'],
                'billid'=>$user->profile['billid'],
                'username' => "$user->firstname, $user->lastname",
                'familyissue' => ($ticket->familiarid!==$ticket->userid) ? 'Yes' : 'No', // Si tiene un familiar asignado
                'familiarname' => $familiar,
                'familiar_role' => $familiar_type,
                'date' => (int) $ticket->dateticket,
                'lastdate'=> (int) $lastAction[0]->dateaction,
                'state' => $ticket->state,
                'description' => strip_tags($ticket->description), // Eliminamos etiquetas HTML
                'priority' => empty($ticket->priority) ? 'Low' : $ticket->priority,
                'label' => $ticket->label_field,
                'assigned' => ($userincharge->username==='logisticwebservice')?'Waiting to be assigned':"$userincharge->firstname, $userincharge->lastname",
            ];
        }
        
        $tickets=[
            'listadoTickets'=>$formatted_tickets,
        ];  

        
        // Retornar una respuesta (ej. el ID del nuevo ticket creado)
        return $tickets;
    }


    public static function execute_returns() {
        return new external_single_structure(
            array(
                'listadoTickets' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'ticketnumber' => new external_value(PARAM_TEXT, 'Número del ticket'),
                            'category' => new external_value(PARAM_TEXT, 'Category ticket'),
                            'subcategory' => new external_value(PARAM_TEXT, 'Subcategory ticket'),
                            'customer' => new external_value(PARAM_TEXT, 'customer user ticket'),
                            'vessel' => new external_value(PARAM_TEXT, 'customer user ticket'),
                            'billid' => new external_value(PARAM_TEXT, 'customer user ticket'),
                            'username' => new external_value(PARAM_TEXT, 'Nombre de usuario'),
                            'familyissue' => new external_value(PARAM_TEXT, 'Yes/No'),
                            'familiarname' => new external_value(PARAM_TEXT, 'familiarname'),
                            'familiar_role' => new external_value(PARAM_TEXT, 'familiar role'),
                            'date' => new external_value(PARAM_INT, 'Fecha del ticket (timestamp)'),
                            'lastdate' => new external_value(PARAM_INT, 'Fecha del ticket (timestamp)'),
                            'state' => new external_value(PARAM_TEXT, 'Open/Assigned/Cancelled/Closed'),
                            'description' => new external_value(PARAM_TEXT, 'Descripción del problema'),
                            'priority' => new external_value(PARAM_TEXT, 'High/Medium/Low'),
                            'label' => new external_value(PARAM_TEXT, ' etiqueta/usuario ficticio'),
                            'assigned' => new external_value(PARAM_TEXT, ' usuario asignado'),
                            
                        )
                    )
                ),
            )
        );
    }


    
    

}