<?php
namespace report_coursereport\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_course_list extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'customerid'=>new external_value(PARAM_INT,'customer id'),
                ])
            ) 
        ]);
    }

        /**
     * Order ITP
     * @param array A list of params for sorting the ITP (with keys orderby and order)
     * @return array A ITP Row
     */
    public static function execute($params) {
        global $DB;
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        
        // Extract parameters
       
       
        
        $customerid=$request['params'][0]['customerid'];
        
        $customercode=$DB->get_record('customer', ['id'=>$customerid], 'shortname');
        $customercode=$customercode->shortname;
        
        
        $course_query=$DB->get_records_sql('SELECT id,shortname,fullname FROM mdl_course WHERE shortname like :customercode',['customercode'=>$customercode.'_%']);
        $course_list=array_values($course_query);
        

         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);

        // Process data (if needed)
        // Replace this with your actual data processing logic based on the parameters

        
 
        return $course_list;
    }

    public static function execute_returns() {
        
        return 
            new external_multiple_structure(
                new external_single_structure([
                    'id'=>new external_value(PARAM_INT,'user id'),
                    'shortname'=>new external_value(PARAM_TEXT,'username'),
                    'fullname'=>new external_value(PARAM_TEXT,'firstname')
                ])
                );
       
        
       
    }
}