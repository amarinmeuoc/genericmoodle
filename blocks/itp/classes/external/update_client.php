<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class update_client extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'original_shortname'=>new external_value(PARAM_TEXT,'original Customer shortname'),
                    'shortname'=>new external_value(PARAM_TEXT,'Customer shortname'),
                    'proyectname'=>new external_value(PARAM_TEXT,'Customer fullname'),
                    
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
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        $original_shortname = strtoupper($request['params'][0]['original_shortname']);
        $shortname = strtoupper($request['params'][0]['shortname']);
        $customerfullname = $request['params'][0]['proyectname'];
    
        if (trim($shortname) === '' || trim($customerfullname) === '') {
            return 0;
        }
    
        // Security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        // Crear el cliente primero en la base de datos
        $dataobject = (object)[
            'shortname' => $shortname,
            'name' => $customerfullname
        ];
    
        // get the id
        $result = $DB->get_record('customer', ['shortname' => $original_shortname]);
        if (!$result) {
            return [
                'id'=>0,
                'shortname'=>'',
                'name'=>''
            ];
        }
        
        $dataobject->id=$result->id;
        
        $customerid = $DB->update_record('customer', $dataobject, true);
    
        return $dataobject;
    }
    
    
    
    


    public static function execute_returns() {
        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        return new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id project'),
                'shortname' => new external_value(PARAM_TEXT, 'shortname'),
                'name' => new external_value(PARAM_TEXT, 'projectname'),
                
        ]);
    }

}