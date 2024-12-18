<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class add_family_members extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'userid' => new external_value(PARAM_INT, 'User ID'),
                    'firstname' => new external_value(PARAM_TEXT, 'First name of the family member'),
                    'lastname' => new external_value(PARAM_TEXT, 'Last name of the family member'),
                    'relationship' => new external_value(PARAM_TEXT, 'Relationship to the trainee'),
                    'gestorid' => new external_value(PARAM_INT, 'gestorid'),
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
        $relationship=$request['params'][0]['relationship'];
        $firstname=$request['params'][0]['firstname'];   
        $lastname=$request['params'][0]['lastname'];
        $birthdate=time();
        $arrival=time();
        $departure=time();
   
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        $record= new \stdClass();
        $record->userid=$userid;
        $record->relationship=$relationship;
        $record->name=$firstname;
        $record->lastname=$lastname;
        $record->birthdate=$birthdate;
        $record->departure=$departure;
        $record->arrival=$arrival;

        $id=$DB->insert_record('family', $record,true);

        $gestor=$DB->get_record('user',['id'=>$gestorid],'id,firstname,lastname');
        profile_load_custom_fields($gestor);
        
        $record->id=$id;
        
        $ObjReturn=[
            'listadoFamily'=>[
                'id'=>$record->id,
                'relationship'=>$record->relationship,
                'name'=>$record->name,
                'lastname'=>$record->lastname,
                'gestor_role'=>$gestor->profile['role']
            ]
        ];

        
        // Retornar una respuesta (ej. el ID del nuevo ticket creado)
        return $ObjReturn;
    }


    public static function execute_returns() {
        return new external_single_structure([
            'listadoFamily' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID of the family member record'),
                'relationship' => new external_value(PARAM_TEXT, 'Relationship to the trainee'),
                'name' => new external_value(PARAM_TEXT, 'First name of the family member'),
                'lastname' => new external_value(PARAM_TEXT, 'Last name of the family member'),
                'gestor_role' => new external_value(PARAM_TEXT, 'Last name of the family member')
            ])
        ]);
    }
    
    


    
    

}