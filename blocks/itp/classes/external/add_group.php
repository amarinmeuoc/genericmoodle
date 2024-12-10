<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class add_group extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'customerid'=>new external_value(PARAM_INT,'Customer id'),
                    'groupname'=>new external_value(PARAM_TEXT, 'Group name'),
                    'ifhidden'=>new external_value(PARAM_INT, 'If is an addministrative group')
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
        $customerid=$request['params'][0]['customerid'];
        $groupname=strtoupper($request['params'][0]['groupname']);
        $ifhidden=strtoupper($request['params'][0]['ifhidden']);

        //Si el nombre del grupo está vacio devolvemos 0
        if (trim($groupname)===''){
            return 0;
        }
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         $dataobject=(object)['customer'=>$customerid,'name'=>$groupname,'hidden'=>$ifhidden];

         //Devuelve el ID del nuevo grupo creado
         $result=$DB->insert_record('grouptrainee', $dataobject, true,false);
         
        return [
            'result'=>$result,
            'groupname'=>$dataobject->name,
            'ifhidden'=>$dataobject->hidden
        ];
    }


    public static function execute_returns() {
        
        return new external_single_structure([
            'result' => new external_value(PARAM_INT, 'If was ok)'),
            'groupname' => new external_value(PARAM_TEXT, 'The name of the category'),
            'ifhidden' => new external_value(PARAM_INT, 'If the category is hidden (1 for hidden, 0 for visible)')
        ]);
    }
}