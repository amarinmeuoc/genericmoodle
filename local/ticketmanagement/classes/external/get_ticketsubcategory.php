<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_ticketsubcategory extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'categoryid'=>new external_value(PARAM_TEXT,'Category id'),
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
        $categoryid=$request['params'][0]['categoryid'];
        
        
        if (!$categoryid){
            return [];
        }
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         

         //Se comprueba que la categoria sea unica
         $result=$DB->get_records('ticket_subcategory',['categoryid'=>$categoryid]);

                  
        return $result;
    }


    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Subcategory ID'),
                    'subcategory' => new external_value(PARAM_TEXT, 'Subcategory name'),
                    'hidden'=>new external_value(PARAM_INT, 'If hidden'),
                )
            )
        );
    }
    

}