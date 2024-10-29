<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_ticket_byId extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'order' => new external_value(PARAM_INT, 'ID de la subcategoría'),
                   'orderby' => new external_value(PARAM_TEXT, 'Método de contacto'),
                   'page' => new external_value(PARAM_INT, 'ID del aprendiz'),
                   'ticketId' => new external_value(PARAM_TEXT, 'Ticket Id'),
                   'activePage' => new external_value(PARAM_INT, 'Pagina activa'),
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
        $order=($request['params'][0]['order']===1)?'ASC':'DESC'; //Si order es igual a 1 ordenamiento ascendente, sino descendente
        $orderby=$request['params'][0]['orderby'];
        $page=$request['params'][0]['page'];   
        $ticketId=$request['params'][0]['ticketId'];   
        $activePage=$request['params'][0]['activePage'];
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);
        
        $ticket = $DB->get_record('ticket', ['id'=>$ticketId], '*');
        
         // Obtener el número total de tickets (para la paginación)
         $num_total_records = ($ticket)?1:0;
           

        // **Validación importante para evitar el error cuando no hay registros**
        if ($num_total_records === null || $num_total_records === '') {
            $num_total_records = 0; // Asegurarse de que siempre sea un número
        }
        
        // Calcular el número total de páginas
        $num_pages = 1;

        //Numero total registros por paginas
        $num_records=1;

        $pages=[
            (object)[
                'page'=>1,
                'active'=>($i === $activePage)
            ]
        ];
      

        $formatted_tickets=[];
        if ($ticket){
            //Check the username of the person in charge
            $userincharge=$DB->get_record('user', ['id'=>$ticket->assigned], 'username,firstname,lastname');
            $user=$DB->get_record('user', ['id'=>$ticket->userid], 'username,firstname,lastname');
            $formatted_tickets[] = [
                'ticketnumber' => $ticket->id,
                'username' => "$user->firstname, $user->lastname",
                'familyissue' => ($ticket->familiarid!==$ticket->userid) ? 'Yes' : 'No', // Si tiene un familiar asignado
                'date' => (int) $ticket->dateticket,
                'state' => $ticket->state,
                'description' => strip_tags($ticket->description), // Eliminamos etiquetas HTML
                'priority' => empty($ticket->priority) ? 'Low' : $ticket->priority,
                'assigned' => ($userincharge->username==='webserviceuser')?'Waiting to be assigned':"$userincharge->firstname, $userincharge->lastname"
            ];
        }
        
        
        $tickets=[
            'listadoTickets'=>$formatted_tickets,
            'orderbyticket'=>$orderby==='id'?true:false,
            'orderbyuser'=>$orderby==='userid'?true:false,
            'orderbyfamilyissue'=>$orderby==='familiarid'?true:false,
            'orderbypriority'=>$orderby==='priority'?true:false,
            'orderbyassigned'=>$orderby==='assigned'?true:false,
            'orderbydate'=>$orderby==='dateticket'?true:false,
            'orderbystate'=>$orderby==='state'?true:false,
            'order'=>($order==='ASC')?1:0,
            'hidecontrolonsinglepage'=>true,
            'activepagenumber'=>$activePage,
            'barsize'=>'small',
            'num_total_records'=>$num_total_records,
            'num_pages'=>$num_pages,
            'num_records'=>$num_records,
            'pages'=>$pages,
            'previous' => [
                'page' => ($page - 1 < 1) ? 1 : $page - 1, // Si es menor a 1, devolver siempre 1
                'url' => '' // URL vacía o generada
            ],
            'next' => [
                'page' => ($page + 1 > $num_pages) ? $num_pages : $page + 1, // Límite superior en 10
                'url' => '' // URL vacía o generada
            ],
            'first' => [
                'page' => 1,
                'url' => '' // URL vacía o generada
            ],
            'last' => [
                'page' => $num_pages, // Asumiendo que 10 es el número máximo de páginas
                'url' => '' // URL vacía o generada
            ],
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
                            'username' => new external_value(PARAM_TEXT, 'Nombre de usuario'),
                            'familyissue' => new external_value(PARAM_TEXT, 'Yes/No'),
                            'date' => new external_value(PARAM_INT, 'Fecha del ticket (timestamp)'),
                            'state' => new external_value(PARAM_TEXT, 'Open/Assigned/Cancelled/Closed'),
                            'description' => new external_value(PARAM_TEXT, 'Descripción del problema'),
                            'priority' => new external_value(PARAM_TEXT, 'High/Medium/Low'),
                            'assigned' => new external_value(PARAM_TEXT, 'ID del usuario asignado'),
                        )
                    )
                ),
                'orderbyticket' => new external_value(PARAM_BOOL, 'Indica si los tickets están ordenados por número de ticket'),
                'orderbyuser' => new external_value(PARAM_BOOL, 'Indica si los tickets están ordenados por usuario'),
                'orderbyfamilyissue' => new external_value(PARAM_BOOL, 'Indica si los tickets están ordenados por familiar'),
                'orderbypriority' => new external_value(PARAM_BOOL, 'Indica si los tickets están ordenados por prioridad'),
                'orderbyassigned' => new external_value(PARAM_BOOL, 'Indica si los tickets están ordenados por asignado'),
                'orderbydate' => new external_value(PARAM_BOOL, 'Indica si los tickets están ordenados por fecha'),
                'orderbystate' => new external_value(PARAM_BOOL, 'Indica si los tickets están ordenados por estado'),
                'order'=> new external_value(PARAM_INT, 'Indica si es orden ascendente o descendente'),
                'hidecontrolonsinglepage' => new external_value(PARAM_BOOL, 'Control para ocultar la navegación en una sola página'),
                'activepagenumber' => new external_value(PARAM_INT, 'Número de página actual'),
                'barsize' => new external_value(PARAM_TEXT, 'Tamaño de la barra de navegación'),
                'num_total_records' => new external_value(PARAM_INT, 'Número total de registros'),
                'num_pages' => new external_value(PARAM_INT, 'Número total de registros para la pagina seleccionada'),
                'num_records' => new external_value(PARAM_INT, 'Número total de registros para la pagina seleccionada'),
                // Nueva estructura 'pages'
                'pages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'page' => new external_value(PARAM_INT, 'Número de página'),
                            'active' => new external_value(PARAM_BOOL, 'Indica si la página está activa')
                        )
                    )
                ),
                'previous' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la página anterior'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la página anterior'),
                    )
                ),
                'next' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la página siguiente'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la página siguiente'),
                    )
                ),
                'first' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la primera página'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la primera página'),
                    )
                ),
                'last' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la última página'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la última página'),
                    )
                ),
            )
        );
    }


    
    

}