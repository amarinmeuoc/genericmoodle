<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class edit_ticketsubcategory extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'subcategory'=>new external_value(PARAM_TEXT,'subcategory name'),
                    'id'=>new external_value(PARAM_INT,'id'),
                    'categoryid'=>new external_value(PARAM_INT,'Category id'),
                    'ifhidden'=>new external_value(PARAM_INT,'if category is hidden')
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
        $subcategory=$request['params'][0]['subcategory'];
        $id=$request['params'][0]['id'];
        $ifhidden=$request['params'][0]['ifhidden'];
        
        
        if (!$categoryid || !$id || trim($subcategory)===''){
            return [];
        }
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         
         $dataobject=(object)['id'=>$id,'subcategory'=>$subcategory,'categoryid'=>$categoryid,'hidden'=>$ifhidden];

         //Se comprueba que la categoria sea unica
         $result=$DB->update_record('ticket_subcategory', $dataobject, $bulk);

                  
        return $result;
    }


    public static function execute_returns() {
        return new external_value(PARAM_INT,'Returns 1 if the operation was successfull');
    }
    

}