<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

require_once($CFG->libdir . '/filelib.php');


class add_activity extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'ticketId' => new external_value(PARAM_TEXT, 'ID del ticket'),
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
        $ticketId=$request['params'][0]['ticketId'];
  

        
       

        $DB->execute("INSERT INTO {ticket_action} (action, dateaction, userid, ticketid)
                VALUES (?,?,?,?)",
                array("ticket created",$record->lastupdate,$record->assigned,$next_id));

        //Borramos etiquetas HTML
        $record->description=strip_tags($record->description);

        $record->familyissue=($record->userid!==$record->familiarid)?'Yes':'No';

        //Nombre y apellidos del usuario afectado
        $affectedUser=$DB->get_record('user', ['id'=>$record->userid], 'username,firstname,lastname');
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
                'fileid' => new external_value(PARAM_INT, 'ID del archivo adjunto'),
                'lastupdate' => new external_value(PARAM_INT, 'Fecha de última actualización')
            )
        );
    }
    

}