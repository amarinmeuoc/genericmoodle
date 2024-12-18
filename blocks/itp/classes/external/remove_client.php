<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class remove_client extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'shortname'=>new external_value(PARAM_TEXT,'Customer shortname'),
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
        $shortname=$request['params'][0]['shortname'];
        
        // Obtenemos el id del cliente
        $customerid=$DB->get_record('customer',['shortname'=>$shortname],'id')->id;
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         $result=$DB->delete_records('customer',['shortname'=>$shortname]);
         
         //INTEGRIDAD REFERENCIAL: Se borran los grupos asociados al cliente
         $result=$DB->delete_records('grouptrainee',['customer'=>$customerid]);
         
        
        return $result;
    }


    public static function execute_returns() {
        //Devuelve si la operación se ha llevado a cabo con exito
        return new external_value(PARAM_BOOL,'If it was successful or not');
    }

}