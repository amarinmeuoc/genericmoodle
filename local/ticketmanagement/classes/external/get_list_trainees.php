<?php
namespace local_ticketmanagement\external;

require_once($CFG->dirroot.'/user/profile/lib.php'); 

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_list_trainees extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'groupid'=>new external_value(PARAM_TEXT,'Group name'),
                    'customerid'=>new external_value(PARAM_TEXT,'Customer shortname'),
                    'role'=>new external_value(PARAM_TEXT,'Role, in general student'),
                    
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
    
        // Validate parameters and extract them
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        $groupid = $request['params'][0]['groupid'];
        $customerid = $request['params'][0]['customerid'];
        $role = $request['params'][0]['role'];
    
        // Security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        $customer=$DB->get_field('customer','shortname',['id'=>$customerid]);
    
        if ($groupid!=='0')
            $groupname=$DB->get_field('grouptrainee','name',['id'=>$groupid]);
        else
            $groupname='PCO';

        
        
        //List all trainees in the LMS
        $trainee_query=$DB->get_records_sql('SELECT u.id,username,firstname, lastname,email,
                MAX(if (uf.shortname="billid",ui.data,"")) as billid,
                MAX(if (uf.shortname="group",ui.data,"")) as groupname,
                MAX(if (uf.shortname="customer",ui.data,"")) as customer,
                MAX(IF(uf.shortname = "role", ui.data, "")) AS role_name
                FROM mdl_user AS u
                INNER JOIN mdl_user_info_data AS ui ON ui.userid=u.id
                INNER JOIN mdl_user_info_field AS uf ON uf.id=ui.fieldid
                WHERE u.suspended=0
                GROUP by username,firstname, lastname
                HAVING role_name=:role_name AND customer=:customer AND groupname=:groupname',['role_name'=>$role,'customer'=>$customer, 'groupname'=>$groupname]);
                $trainee_list=array_values($trainee_query);
        
        
        
        return $trainee_list;
    }

    

    public static function execute_returns() {
        //Devuelve un array de objetos
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'firstname' => new external_value(PARAM_TEXT, 'First name'),
                'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                'email' => new external_value(PARAM_TEXT, 'Email'),
                'billid' => new external_value(PARAM_TEXT, 'Bill ID'),
                'groupname' => new external_value(PARAM_TEXT, 'Group name'),
                'customer' => new external_value(PARAM_TEXT, 'Customer'),
                'role_name' => new external_value(PARAM_TEXT, 'Role name'),
            ])
        );
   
    }
}