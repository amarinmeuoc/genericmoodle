<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class add_client extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
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
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        $shortname=strtoupper($request['params'][0]['shortname']);
        $customerfullname=$request['params'][0]['proyectname'];
        
        if (trim($shortname)==='' || trim($customerfullname)===''){
            return 0;
        }
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         $dataobject=(object)['shortname'=>$shortname, 'name'=>$customerfullname];

         //Se comprueba que el shortname sea único
         $result=$DB->get_record('customer',['shortname'=>$shortname]);
         
         if (!$result){
            //Si por algun motivo la inserción falla, devolverá 0 y se asegura devolver un entero
            $result=intval($DB->insert_record('customer', $dataobject, true, false));
         } else {
            $result=0;
         }
         
         
         
        return $result;
    }


    public static function execute_returns() {
        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        return new external_value(PARAM_INT,'Returns the Id of the operation');
    }

}