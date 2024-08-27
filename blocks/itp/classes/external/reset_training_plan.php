<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class reset_training_plan extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'op'=>new external_value(PARAM_TEXT,'Operacion'),
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
        $op=$request['params'][0]['op'];
        
        if ($op!=='reset')
            return false;
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         $result=$DB->delete_records('trainingplan',[]);
         
        
        return $result;
    }


    public static function execute_returns() {
        //Devuelve si la operación se ha llevado a cabo con exito
        return new external_value(PARAM_BOOL,'If it was successful or not');
    }

}