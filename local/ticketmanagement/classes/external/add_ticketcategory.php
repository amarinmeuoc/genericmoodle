<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class add_ticketcategory extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'category'=>new external_value(PARAM_TEXT,'Category name'),
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
        $categoryname=strtoupper($request['params'][0]['category']);
        
        
        if (trim($categoryname)==='' || trim($categoryname)===''){
            return 0;
        }
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         

         //Se comprueba que la categoria sea unica
         $result=$DB->get_record('ticket_category',['category'=>$categoryname]);

         $dataobject=(object)['category'=>$categoryname];
         
         if (!$result){
            //Si por algun motivo la inserción falla, devolverá 0 y se asegura devolver un entero
            $result=intval($DB->insert_record('ticket_category', $dataobject, true, false));
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