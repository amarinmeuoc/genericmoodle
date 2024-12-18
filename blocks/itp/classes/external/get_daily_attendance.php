<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_daily_attendance extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'courseid'=>new external_value(PARAM_INT,'Course id'),
                    'startdate'=>new external_value(PARAM_INT,'Start date'),
                    'enddate'=>new external_value(PARAM_INT,'End date'),
                    'email'=>new external_value(PARAM_TEXT,'email')
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
        $sql_normal='SELECT DISTINCT
                        attsess.id AS sessionid,
                        attsess.sessdate AS dateatt,
                        att.course AS courseid,
                        c.shortname AS shortname,
                        c.fullname AS fullname,
                        att.name AS Module,
                        attlog.studentid AS studentid,
                        u.firstname AS firstname,
                        u.lastname AS lastname,
                        attlog.id AS logid,
                        attlog.statusid AS statusid,
                        attsta.description AS description,
                        attlog.remarks AS feedback
                        FROM
                            {attendance} att
                                JOIN {attendance_sessions} attsess ON
                                    ( (attsess.attendanceid = att.id) )
                                                
                                JOIN {attendance_log} attlog ON
                                    ((attlog.sessionid = attsess.id))
                                            
                                JOIN {attendance_statuses} attsta ON
                                    ((attsta.id = attlog.statusid))
                                        
                                JOIN {user} u ON
                                    ((u.id = attlog.studentid))
                                    
                                JOIN {course} c ON
                                    ((c.id = att.course))
                                
                                JOIN {user_info_data} info ON
                                    ((u.id = info.userid))
                                
                                WHERE
                                    (u.suspended = 0 
                                    AND c.id=? 
                                    AND u.id=? 
                                    AND date(from_unixtime(attsess.sessdate))<=date(from_unixtime(?)) 
                                    AND date(from_unixtime(attsess.sessdate))>=date(from_unixtime(?))) 
                                    AND attsta.description not like "%Present%"
                                ORDER BY
                                att.course,
                                attsess.id,
                                attlog.statusid';
         
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        
        // Extract parameters
        $courseid=$request['params'][0]['courseid'];
        $email=$request['params'][0]['email'];
        $startdate=$request['params'][0]['startdate'];
        
        $enddate=$request['params'][0]['enddate'];

        $user = $DB->get_record('user', ['email' => $email]);
        
        $attendance_list=$DB->get_records_sql($sql_normal, array('courseid'=>$courseid, 'userid'=>$user->id, 'enddate'=>$enddate, 'startdate'=>$startdate));
        
        $attendance_list=array_values($attendance_list);
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);

        // Process data (if needed)
        // Replace this with your actual data processing logic based on the parameters
        
        // Return response data
        $data=[];
        foreach ($attendance_list as $key => $elem) {
            # code...
            $data[$key]=(object)array(
                'courseid'=>$elem->courseid,
                'coursename'=>$elem->fullname,
                'shortname'=>$elem->shortname,
                'date'=>$elem->dateatt,
                'firstname'=>$elem->firstname,
                'lastname'=>$elem->lastname,
                'description'=>$elem->description,
                'feedback'=>$elem->feedback
            );
        }

        // Fetch user and course data
        $coursename = $DB->get_field('course', 'fullname', ['id' => $courseid]);
    
        // Load custom fields and prepare personal data
        profile_load_custom_fields($user);
        $personaldata = (object)[
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'billid' => $user->profile['billid'],
            'coursename' => $coursename,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'message' => (count($attendance_list)>0)?false:true
        ];

        $result=new \stdClass();
        $result->attendance=$data;
        $result->personaldata=$personaldata;
       
        return $result;
    }

    public static function execute_returns() {
        return new external_single_structure([
            'attendance' => new external_multiple_structure(
                new external_single_structure([
                    'courseid' => new external_value(PARAM_TEXT, 'Course id'),
                    'coursename' => new external_value(PARAM_TEXT, 'Course name'),
                    'shortname' => new external_value(PARAM_TEXT, 'Course shortname'),
                    'date' => new external_value(PARAM_INT, 'Date of registered attendance'),
                    'firstname' => new external_value(PARAM_TEXT, 'First user name'),
                    'lastname' => new external_value(PARAM_TEXT, 'Last user name'),
                    'description' => new external_value(PARAM_TEXT, 'Status Description'),
                    'feedback' => new external_value(PARAM_TEXT, 'If there is any feedback')
                ])
            ),
            'personaldata' => new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'User name'),
                'billid' => new external_value(PARAM_TEXT, 'billid'),
                'email' => new external_value(PARAM_TEXT, 'User email'),
                'coursename' => new external_value(PARAM_TEXT, 'Course coursename'),
                'startdate' => new external_value(PARAM_INT, 'Course startdate'),
                'enddate' => new external_value(PARAM_INT, 'Course endate'),
                'message' => new external_value(PARAM_BOOL, 'Message')
            ])
        ]);
    }
}