<?php
namespace report_dailyattendance\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class Get_List_Group extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params' => new external_multiple_structure(
                new external_single_structure([
                    'customerid' => new external_value(PARAM_INT, 'customer id'),
                ])
            )
        ]);
    }

    public static function execute($formValue){
        global $CFG, $DB;
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$formValue]);

        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);
        
        $customerid=$request['params'][0]['customerid'];

        $group_list=$DB->get_records('grouptrainee',['customer'=>$customerid],'id,name');
        $group_list=array_values($group_list);
        
        $transaction =$DB->start_delegated_transaction();

       
        
        return $group_list;
    }

    public static function execute_returns(){
        return new external_multiple_structure(
            new external_single_structure([
                'id'=>new external_value(PARAM_TEXT, 'selected group'),
                'name'=>new external_value(PARAM_TEXT, 'selected billid'),
            ])
        );
    }
}