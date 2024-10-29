<?php
namespace report_partialplan\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_assessment extends \core_external\external_api {
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
                    'billid'=>new external_value(PARAM_TEXT,'Billid'),
                    'wbs'=>new external_value(PARAM_TEXT,'WBS'),
                    'startdate'=>new external_value(PARAM_INT,'Course startdate'),
                    'enddate'=>new external_value(PARAM_INT,'Course enddate'),
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

         $list_trainees=[];
        // Extract parameters
        foreach ($request['params'] as $key => $value) {
            $customerid=$value['customerid'];
            $wbs=$value['wbs'];
            $group=$value['group'];
            $billid=$value['billid'];
            $startdate=$value['startdate'];
            $enddate=$value['enddate'];
            
            //get the shortname
            $customercode=$DB->get_record('customer',array('id'=>$customerid),'shortname')->shortname;

            
            $sql="SELECT {user}.id, shortname, data
                    FROM {user} 
                        INNER JOIN {user_info_data} ON {user_info_data}.userid={user}.id
                        INNER JOIN {user_info_field} ON {user_info_field}.id={user_info_data}.fieldid
                    WHERE data =:billid AND shortname='billid'";
            //devuelve los id de los usuarios
            $query= $DB->get_records_sql($sql,array('billid'=>$billid));
                        
            foreach ($query as $row) {
                //Given a user
                $userid=$row->id;
                $user = $DB->get_record('user', array('id' => $userid));
                //Se carga toda la información del usuario
                profile_load_custom_fields($user);
                $formatedUser=new \stdClass();

                
                //Check if is the user we are looking for
                if ($billid==$user->profile['billid'] && $group==$user->profile['group'] && $customercode==$user->profile['customer']){
                    $formatedUser->id=$userid;
                    $formatedUser->billid=$billid;
                    $formatedUser->customer=$customerid;
                    $formatedUser->group=$group;
                    $formatedUser->rol=$user->profile['rol'];
                    $formatedUser->type=$user->profile['type'];
                    $formatedUser->department=$user->profile['department'];
                    $formatedUser->passport=$user->profile['passport'];
                    $formatedUser->personal_email=$user->profile['personal_email'];
                    $formatedUser->nie=$user->profile['nie'];
                    $formatedUser->firstname=$user->firstname;
                    $formatedUser->lastname=$user->lastname;
                    $formatedUser->email=$user->email;
                    //Get the courseid from its shortname
                    $course=$DB->get_record('course', ['shortname'=>$wbs], 'id,fullname');
                    $courseid=$course->id;
                    $coursefullname=$course->fullname;

                    $formatedUser->wbs=$wbs;
                    $formatedUser->coursename=$coursefullname;
                    $formatedUser->startdate=$startdate;
                    $formatedUser->enddate=$enddate;
                    //Get the trainee assessment and attendance from userid and courseid
                    $sql="SELECT max(itemid) as id, AVG(finalgrade) as finalgrade, MAX(itemtype) as itemtype  FROM (SELECT u.id,u.firstname,u.lastname,grades.finalgrade,grades.itemid, items.courseid, items.itemname, items.itemtype FROM mdl_user as u
                        inner join mdl_grade_grades as grades on grades.userid=u.id
                        inner join mdl_grade_items as items on items.id=grades.itemid
                        where items.courseid=:courseid 
                        and grades.userid=:userid 
                        and (items.itemtype='manual' || (items.itemtype='mod' and items.itemname REGEXP 'attendance')) 
                        and items.itemname NOT REGEXP '[Aa]ttitude' 
                        and items.itemname NOT REGEXP '[Pp]articipation' 
                        and items.itemname NOT REGEXP '[Aa]chieved [Ll]evel' 
                        and grades.finalgrade!='NULL') AS RESULT
                        GROUP BY itemtype";
                    $datos_academicos=$DB->get_records_sql($sql, ['courseid'=>$courseid, 'userid'=>$userid]);
                    $datos_academicos=array_values($datos_academicos);
                                        
                    $formatedUser->att=array_values(array_filter($datos_academicos,function($row){
                        return ($row->itemtype==='mod');
                    }))[0]->finalgrade;
                    $formatedUser->ass=array_values(array_filter($datos_academicos,function($row){
                        return ($row->itemtype==='manual');
                    }))[0]->finalgrade;

                    
                    
                    array_push($list_trainees,$formatedUser);
                }      
            }

            

            
        }
        
             
        
        return $list_trainees;
    }


    public static function execute_returns() {
        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        return new external_multiple_structure(
            new external_single_structure([
                'id'=>new external_value(PARAM_INT,'id'),
                'billid' => new external_value(PARAM_TEXT, 'Billid'),
                'customer'=>new external_value(PARAM_INT,'customer id'),
                'group' => new external_value(PARAM_TEXT, 'group shortname'),
                'rol'=>new external_value(PARAM_TEXT,'Full coursename'),
                'type'=>new external_value(PARAM_TEXT,'Type'),
                'department'=>new external_value(PARAM_TEXT,'Department'),
                'passport'=>new external_value(PARAM_TEXT,'trainee passport'),
                'personal_email'=>new external_value(PARAM_TEXT, 'Personal email'),
                'nie'=>new external_value(PARAM_TEXT,'Nie'),
                'firstname' => new external_value(PARAM_TEXT, 'trainee firstname'),
                'lastname'=>new external_value(PARAM_TEXT, 'trainee lastname'),
                'email'=>new external_value(PARAM_TEXT,'email'),
                'att'=>new external_value(PARAM_TEXT,'Attendance'),
                'ass'=>new external_value(PARAM_TEXT,'Assessment'),
                'wbs'=>new external_value(PARAM_TEXT,'Course wbs'),
                'coursename'=>new external_value(PARAM_TEXT,'course name'),
                'startdate'=>new external_value(PARAM_INT,'Course startdate'),
                'enddate'=>new external_value(PARAM_INT,'Course enddate'),

                
            ])
        );
       
    }

}