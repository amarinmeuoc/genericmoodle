<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class edit_family_members extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'id' => new external_value(PARAM_INT, 'User ID'),
                    'firstname' => new external_value(PARAM_TEXT, 'First name of the family member'),
                    'lastname' => new external_value(PARAM_TEXT, 'Last name of the family member'),
                    'relationship' => new external_value(PARAM_TEXT, 'Relationship to the trainee'),
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
        $relationship=$request['params'][0]['relationship'];
        $firstname=$request['params'][0]['firstname'];   
        $lastname=$request['params'][0]['lastname'];
   
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        $record= new \stdClass();
        $record->id=$id;
        $record->relationship=$relationship;
        $record->name=$firstname;
        $record->lastname=$lastname;

        $DB->update_record('family', $record);
        
        $ObjReturn=[
            'listadoFamily'=>[
                'id'=>$record->id,
                'relationship'=>$record->relationship,
                'name'=>$record->name,
                'lastname'=>$record->lastname,
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
                'lastname' => new external_value(PARAM_TEXT, 'Last name of the family member')
            ])
        ]);
    }

}