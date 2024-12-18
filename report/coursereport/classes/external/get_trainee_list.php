<?php
namespace report_coursereport\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_trainee_list extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'groupid'=>new external_value(PARAM_INT,'group id'),
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

        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);
        
        // Extract parameters
        $groupid=$request['params'][0]['groupid'];

        $grouptrainee=$DB->get_record('grouptrainee',['id'=>$groupid],'customer,name');
        
        //Obtain the group name
        $groupname=$grouptrainee->name;
        
        //Obtain the customer id
        $customerid=$grouptrainee->customer;
        
        //Obtain the customer code
        $customercode=$DB->get_field('customer', 'shortname', array('id'=>$customerid), IGNORE_MISSING);
        
        $role='student';
        $trainee_query=$DB->get_records_sql('SELECT u.id,username,firstname, lastname,email,
                                    MAX(if (uf.shortname="billid",ui.data,"")) as billid,
                                    MAX(if (uf.shortname="group",ui.data,"")) as groupname,
                                    MAX(if (uf.shortname="customer",ui.data,"")) as customer,
                                    MAX(IF(uf.shortname = "role", ui.data, "")) AS role_name
                                    FROM mdl_user AS u
                                    INNER JOIN mdl_user_info_data AS ui ON ui.userid=u.id
                                    INNER JOIN mdl_user_info_field AS uf ON uf.id=ui.fieldid
                                    GROUP by username,firstname, lastname
                                    HAVING role_name=:role_name AND customer=:customer AND groupname=:groupname',
                                    ['role_name'=>$role,'customer'=>$customercode, 'groupname'=>$groupname]);

        $trainee_list=array_values($trainee_query);
 
        //build the response and populate result
        $response = [];
        foreach ($trainee_list as $trainee) {
            $response[] = [
                'id' => $trainee->id,
                'username' => $trainee->username,
                'firstname' => $trainee->firstname,
                'lastname' => $trainee->lastname,
                'billid' => $trainee->billid,
                'groupname' => $groupname
            ];
        }
        
 
        return $response;
    }

    public static function execute_returns() {
        
        return 
            new external_multiple_structure(
                new external_single_structure([
                    'id'=>new external_value(PARAM_INT,'user id'),
                    'username'=>new external_value(PARAM_TEXT,'username'),
                    'firstname'=>new external_value(PARAM_TEXT,'firstname'),
                    'lastname'=>new external_value(PARAM_TEXT,'lastname'),
                    'billid'=>new external_value(PARAM_TEXT,'billid'),
                    'groupname'=>new external_value(PARAM_TEXT,'groupname')
                ])
                );
       
        
       
    }
}