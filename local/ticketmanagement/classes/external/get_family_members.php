<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_family_members extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'userid' => new external_value(PARAM_INT, 'User id'),
                   'gestorid'=> new external_value(PARAM_INT, 'User id'),
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
        
        $userid=$request['params'][0]['userid'];   
        $gestorid=$request['params'][0]['gestorid'];  
   
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        $familymembers=$DB->get_records('family',['userid'=>$userid],'id ASC', '*');

        $formatted_familymembers=[];
        foreach ($familymembers as $member) {
            //Check the username of the person in charge
            
            $formatted_familymembers[] = [
                'id' => $member->id,
                'relationship' => $member->relationship,
                'name'=>$member->name,
                'lastname'=>$member->lastname
            ];
        }

        $user=$DB->get_record('user',['id'=>$userid],'id,firstname,lastname');
        
        $gestor=$DB->get_record('user',['id'=>$gestorid],'id,firstname,lastname');
        profile_load_custom_fields($gestor);
        
        $family=[
            'listadoFamily'=>$formatted_familymembers,
            'user'=>"$user->firstname, $user->lastname",
            'userid' => $user->id,
            'gestor_role'=>$gestor->profile['role']
        ];
        
            

        // Retornar una respuesta (ej. el ID del nuevo ticket creado)
        return $family;
    }


    public static function execute_returns() {
        return new external_single_structure(
            array(
                'listadoFamily' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'ID'),
                            'relationship' => new external_value(PARAM_TEXT, 'Relationship'),
                            'name' => new external_value(PARAM_TEXT, 'Name'),
                            'lastname' => new external_value(PARAM_TEXT, 'Lastname'),
                        )
                    )
                ),
                'user' => new external_value(PARAM_TEXT, 'Username'),
                'userid' => new external_value(PARAM_INT, 'userid'),
                'gestor_role' => new external_value(PARAM_TEXT, 'gestor role'),
            )
        );
    }


    
    

}