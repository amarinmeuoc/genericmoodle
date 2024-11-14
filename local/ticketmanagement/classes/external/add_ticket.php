<?php
namespace local_ticketmanagement\external;
require_once($CFG->dirroot . '/user/profile/lib.php');

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

require_once($CFG->libdir . '/filelib.php');


class add_ticket extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'subcategoryid' => new external_value(PARAM_INT, 'ID de la subcategoría'),
                   'traineeid' => new external_value(PARAM_INT, 'ID del aprendiz'),
                   'state' => new external_value(PARAM_TEXT, 'ID del aprendiz'),
                   'priority' => new external_value(PARAM_TEXT, 'ID del aprendiz'),
                   'description' => new external_value(PARAM_RAW, 'ID del aprendiz'),
                   'familiarid' => new external_value(PARAM_INT, 'ID del familiar o aprendiz (si no hay issue familiar)'),
                   'gestorid' => new external_value(PARAM_INT, 'Ticket made by...'),
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
        $userid=$ticket['traineeid'];
        $userCreatedTicket=$ticket['gestorid'];

        $madeby=$DB->get_record('user', ['id'=>$userCreatedTicket], 'firstname,lastname');

        //Nombre y apellidos del usuario afectado
        $affectedUser=$DB->get_record('user', ['id'=>$userid], 'id,username,firstname,lastname');
        
        if ($affectedUser) {
            \profile_load_custom_fields($affectedUser);
            $customer = $affectedUser->profile['customer'] ?? '';
            $vessel = $affectedUser->profile['group'] ?? '';
            $billid = $affectedUser->profile['billid'] ?? '';
        } else {
            throw new \invalid_parameter_exception("El usuario con ID $userid no existe.");
        }
        

        

        $year = date("Y");
        $last_ticket = $DB->get_record_sql("
            SELECT SUBSTRING_INDEX(MAX(id), '-', -1) as lastid 
            FROM {ticket} 
            WHERE id LIKE CONCAT(?, '-', ?, '-%')
        ", array($customer, $vessel));
        
        
        if (is_null($last_ticket->lastid))
            $last_ticket->lastid=0;
        
        $unique_suffix = time();
        $next_id = sprintf("%s-%s-%s-%06d", $customer, $vessel, $unique_suffix, ($last_ticket->lastid + 1));
        
        
        // Aquí puedes hacer las operaciones necesarias, como insertar el ticket en la base de datos.
        $record = new \stdClass();
        $record->id=$next_id;
        $record->subcategoryid = ($ticket['subcategoryid'])?$ticket['subcategoryid']:0;
        $record->dateticket=time();
        $record->description = $ticket['description'];
        $record->state = $ticket['state'];
        $record->priority = $ticket['priority'];
        $record->userid = $ticket['traineeid']; //ID del usuario afectado 
        $record->familiarid = $ticket['familiarid']; //ID del familiar, sino el usuario que hace el ticket
        $record->assigned=$USER->id; //ID del usuario que lleva el ticket, sino el del operador que lo crea
        
        $record->lastupdate = time();

       

        
        // Insertar el nuevo ticket en una tabla personalizada
        
        $DB->execute("INSERT INTO {ticket} (id, subcategoryid, dateticket, description, state, priority, userid, familiarid, assigned, lastupdate)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
              array($next_id, $record->subcategoryid, $record->dateticket, $record->description, $record->state, $record->priority, 
                    $record->userid, $record->familiarid, $record->assigned, $record->lastupdate));

        $DB->execute("INSERT INTO {ticket_action} (action, dateaction, userid, ticketid)
                VALUES (?,?,?,?)",
                array("ticket created by: $madeby->firstname, $madeby->lastname",$record->lastupdate,$record->assigned,$next_id));

        //Borramos etiquetas HTML
        $record->description=strip_tags($record->description);

        $record->familyissue=($record->userid!==$record->familiarid)?'Yes':'No';

        $record->username="$affectedUser->firstname, $affectedUser->lastname";

        
        //Send email ticket created
        
        // Retornar una respuesta (ej. el ID del nuevo ticket creado)
        return (array) $record;
    }


    public static function execute_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_TEXT, 'ID del nuevo ticket creado'), // Esta clave ya contiene el ID
                'subcategoryid' => new external_value(PARAM_INT, 'ID de la subcategoría'),
                'dateticket' => new external_value(PARAM_INT, 'Fecha de creación del ticket'),
                'description' => new external_value(PARAM_RAW, 'Descripción del ticket'),
                'state' => new external_value(PARAM_TEXT, 'Estado del ticket'),
                'priority' => new external_value(PARAM_TEXT, 'Prioridad del ticket'),
                'userid' => new external_value(PARAM_INT, 'ID del aprendiz (usuario)'),
                'username'=> new external_value(PARAM_TEXT, 'Firstname and lastname'),
                'familyissue' => new external_value(PARAM_TEXT, 'Yes/No value'),
                'familiarid' => new external_value(PARAM_INT, 'ID del familiar o aprendiz'),
                'assigned'=>new external_value(PARAM_INT, 'ID del operador asignado'),
                
                'lastupdate' => new external_value(PARAM_INT, 'Fecha de última actualización')
            )
        );
    }
    

}