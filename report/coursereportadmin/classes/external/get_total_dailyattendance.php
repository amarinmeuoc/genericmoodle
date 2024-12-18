<?php
namespace report_coursereportadmin\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_total_dailyattendance extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'request'=>new external_value(PARAM_TEXT,'Key for requesting the dailyattendance'),
                    'customerid'=>new external_value(PARAM_INT,'Customer id'),
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
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);

                
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
         $value=$request['params'][0]['request'];
         $customerid=$request['params'][0]['customerid'];
         $shortname='%';
         if ($value!=='dailyattendancereport')
            die;
        if ($customerid!==-1){
            $customer=$DB->get_record('customer', ['id'=>$customerid], 'shortname');
            $shortname=$customer->shortname;
        } 

$customerkey = $DB->get_record_sql(
            "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('shortname') . " = " . $DB->sql_compare_text(':customerkey'),
            ['customerkey' => 'customer']
        )->id;
        
        $groupkey = $DB->get_record_sql(
            "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('shortname') . " = " . $DB->sql_compare_text(':groupkey'),
            ['groupkey' => 'group']
        )->id;
 
        
        $billidkey = $DB->get_record_sql(
            "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('shortname') . " = " . $DB->sql_compare_text(':billidkey'),
            ['billidkey' => 'billid']
        )->id;        
        
        $sub_sql="(SELECT DISTINCT
            att.course AS courseid,
            c.shortname AS Nomducours,
            c.fullname AS nameCourse,
            att.name AS Module,
            attsess.id AS sessionid,
            attlog.studentid AS studentid,
            (
                CASE WHEN(info.fieldid = :customerkey) THEN info.data
            END
            ) AS Customer,
            (
                CASE WHEN(info.fieldid = :groupkey) THEN info.data
            END
            ) AS grouptrainee,
            (
                CASE WHEN(info.fieldid = :billidkey) THEN info.data
            END
            ) AS Billid,
            u.firstname AS Prenom,
            u.lastname AS Nom,
            attsess.sessdate AS DateAtt,
            attlog.id AS logid,
            attlog.statusid AS statusid,
            attsta.description AS description,
            attlog.remarks AS feedback
            FROM
                mdl_attendance att
            JOIN mdl_attendance_sessions attsess
            ON (attsess.attendanceid = att.id) JOIN mdl_attendance_log attlog
            ON (attlog.sessionid = attsess.id) JOIN mdl_attendance_statuses attsta
            ON (attsta.id = attlog.statusid) JOIN mdl_user u
            ON (u.id = attlog.studentid) JOIN mdl_course c
            ON (c.id = att.course) JOIN mdl_user_info_data info
            ON (u.id = info.userid) WHERE (u.suspended = 0)
            ORDER BY
                att.course,
                attsess.id,
                attlog.statusid) as daily_attendance";

        $sql="SELECT 
            MAX(Customer) as customercode,                                                   
            MAX(grouptrainee) as grouptrainee, 
            MAX(Billid) as billid, 
            UPPER(Prenom) as Prenom, 
            UPPER(Nom) as Nom, 
            UPPER(Nomducours) as Nomducours, 
            MAX(UPPER(nameCourse)) as nameCourse, 
            (DateAtt) as DateAtt, 
            (description) as description, 
            MAX(UPPER(feedback)) as feedback  FROM ".$sub_sql." 
            group by DateAtt, Nomducours, description, nom, Prenom
            having customercode like :shortname
            ORDER BY DateAtt ASC ";
        
      
        $list=[];
	$params=[
            'customerkey'=>$customerkey,
            'groupkey'=>$groupkey,
            'billidkey'=>$billidkey,
            'shortname'=>$shortname
        ];
        $listAttendance=$DB->get_recordset_sql($sql,$params,0,0);
        
        foreach ($listAttendance as $record) {
            $list[]=$record;
        }
        
        $result=[
            (object)[
                'attendance_list'=>$list,  
                ]
            ];

        $listAttendance->close();
        
        return $result;     
 
    }


    public static function execute_returns(){
        return new external_multiple_structure(
            new external_single_structure([
                'attendance_list'=>new external_multiple_structure(
                    new external_single_structure([
                        'customercode'=>new external_value(PARAM_TEXT, 'selected customer'),
                        'grouptrainee'=>new external_value(PARAM_TEXT, 'selected group'),
                        'billid'=>new external_value(PARAM_TEXT, 'selected billid'),
                        'prenom'=>new external_value(PARAM_TEXT, 'selected name'),
                        'nom'=>new external_value(PARAM_TEXT, 'selected lastname'),
                        'nomducours'=>new external_value(PARAM_TEXT, 'selected wbs'),
                        'namecourse'=>new external_value(PARAM_TEXT, 'selected coursename'),
                        'dateatt'=>new external_value(PARAM_INT, 'selected date'),
                        'description'=>new external_value(PARAM_TEXT, 'Attendance status'),
                        'feedback'=>new external_value(PARAM_TEXT, 'selected feedback'),
                    ])
                    ),
                
            ])
        );
    }

}
