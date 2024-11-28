<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class add_ticketsubcategory extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'categoryid'=>new external_value(PARAM_INT,'Category id'),
                    'subcategory'=>new external_value(PARAM_TEXT, 'Subcategory name'),
                    'ifhidden'=>new external_value(PARAM_INT,'If hidden subcategory'),
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
        $subcategoryname=strtoupper($request['params'][0]['subcategory']);
        $ifhidden=$request['params'][0]['ifhidden'];

        //Si el nombre de la subcategoria está vacio devolvemos 0
        if (trim($subcategoryname)===''){
            return 0;
        }
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         $dataobject=(object)['categoryid'=>$categoryid,'subcategory'=>$subcategoryname,'hidden'=>$ifhidden];

         //Devuelve el ID del nuevo grupo creado
         $result=intval($DB->insert_record('ticket_subcategory', $dataobject, true,false));
         
         return [
            'ok'=>$result,
            'subcategoryname' => $subcategoryname,
            'ifhidden' => $ifhidden
        ];
    }


    public static function execute_returns() {
        return new external_single_structure([
            'ok' => new external_value(PARAM_INT, 'If was ok)'),
            'subcategoryname' => new external_value(PARAM_TEXT, 'The name of the category'),
            'ifhidden' => new external_value(PARAM_INT, 'If the category is hidden (1 for hidden, 0 for visible)')
        ]);
    }
}