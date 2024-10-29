<?php
namespace report_partialplan\external;
require_once($CFG->dirroot.'/user/profile/lib.php');
use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_trainees extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'customerid'=>new external_value(PARAM_INT,'Customer Id'),
                    'group'=>new external_value(PARAM_TEXT,'group'),
                    'billid'=>new external_value(PARAM_TEXT,'Billid')
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
        global $DB,$USER;
        
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);

         $arr=[];
        // Extract parameters
        foreach ($request['params'] as $key => $value) {
            $customerid=$value['customerid'];
            $group=$value['group'];
            $billid=$value['billid'];
            
            //get the shortname
            $customercode=$DB->get_record('customer',array('id'=>$customerid),'shortname')->shortname;

            $user=new \stdClass();
            $sql="SELECT {user}.id, shortname, data
                    FROM {user} 
                        INNER JOIN {user_info_data} ON {user_info_data}.userid={user}.id
                        INNER JOIN {user_info_field} ON {user_info_field}.id={user_info_data}.fieldid
                    WHERE data =:billid AND shortname='billid'";

            $query= $DB->get_records_sql($sql,array('billid'=>$billid));


            foreach ($query as $row) {
                //Given a user
                $userid=$row->id;
                $user = $DB->get_record('user', array('id' => $userid));
                profile_load_custom_fields($user);
                
                $groupid= $DB->get_record('grouptrainee',array('name'=>$group,'customer'=>$customerid),'id');
               
                               //Check if is the user we are looking for
                if ($billid==$user->profile['billid'] && $group==$user->profile['group'] && $customercode==$user->profile['customer']){
                    //$user->id=$userid;
                    $user->billid=$billid;
                    //$user->customer=$customerid;
                    $user->group=$group;
                    $user->rol=$user->profile['rol'];
                    $user->type=$user->profile['type'];
                    $user->department=$user->profile['department'];
                    $user->passport=$user->profile['passport'];
                    $user->personal_email=$user->profile['personal_email'];
                    $user->nie=$user->profile['nie'];
                    $user->firstname=$user->firstname;
                    $user->lastname=$user->lastname;
                    $user->email=$user->email;
                    break;
                }      
            }

            $formatedUser=(object)[
                //'id'=>$user->id,
                //'customer'=>$user->customer,
                'group'=>$user->profile['group'],
                'billid'=>$user->profile['billid'],
                'firstname'=>$user->firstname,
                'lastname'=>$user->lastname,
                'email'=>$user->email
            ];
            
            $arr[]=$formatedUser;
        }
        

        
        
        return $arr;
    }

    public static function execute_returns() {
        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        return new external_multiple_structure(
            new external_single_structure([
                //'id'=>new external_value(PARAM_INT, 'Id'),
                //'customer'=>new external_value(PARAM_INT, 'Customer id'),
                'group' => new external_value(PARAM_TEXT, 'group'),
                'billid' => new external_value(PARAM_TEXT, 'Billid'),
                'firstname' => new external_value(PARAM_TEXT, 'Coursename'),
                'lastname'=>new external_value(PARAM_TEXT, 'Start date'),
                'email'=>new external_value(PARAM_TEXT, 'End date'),
            ])
        );
       
    }

}