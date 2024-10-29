<?php
namespace report_coursereportadmin\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_total_trainee_report extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'request'=>new external_value(PARAM_TEXT,'Choosen option'),
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
         

         if ($value!=='traineereport')
            die;
        
        $sql="SELECT id, customerid, groupid, customercode, groupname, billid, shortname, fullname,  max(firstname) as firstname, max(lastname) as lastname, max(email) as email, max(attendance) as attendance, avg(assessment) as assessment 
        FROM (SELECT DISTINCT
            itp.id as id,
            itp.customerid as customerid,
            customer.shortname as customercode,
            itp.groupid as groupid,
            grouptrainee.name as groupname,
            itp.billid as billid,
            courses.shortname as shortname,
            courses.fullname as fullname,
            itp.startdate AS start_date,
            itp.enddate AS end_date,
            users.firstname as firstname,
            users.lastname as lastname,
            users.email as email,
            grades.userid as userid,
            items.courseid as courseid,
            CASE WHEN items.itemname REGEXP 'attendance' then grades.finalgrade end as attendance,
            case when items.itemname not REGEXP 'attendance' and items.itemname not like 'attitude' then grades.finalgrade end as assessment,
            case when items.itemname like 'attitude' and info.fieldid=1 then substr(grades.feedback, 1, 250) end as comments,
            items.itemname as itemname,
            items.itemtype as itemtype,
            items.itemmodule as itemmodule
            FROM
                mdl_itptrainee itp
                join mdl_user users on (itp.email=users.email)
                left join mdl_grade_grades grades on (grades.userid=users.id)
                join mdl_grade_items items on (items.id=grades.itemid)
                join mdl_user_info_data info on (info.userid=users.id)
                join mdl_course courses on (courses.id=items.courseid and itp.course=courses.shortname)
                join mdl_customer customer on (customer.id=itp.customerid)
                join mdl_grouptrainee grouptrainee on (grouptrainee.id=itp.groupid)
            WHERE
                (items.itemtype='manual' || (items.itemtype='mod' and items.itemname REGEXP 'attendance')) 
                                and items.itemname NOT REGEXP '[Aa]ttitude' 
                                and items.itemname NOT REGEXP '[Pp]articipation' 
                                and items.itemname NOT REGEXP '[Aa]chieved [Ll]evel' 
                                and grades.finalgrade!='NULL') 
        AS RESULT
            GROUP BY customerid,groupid,billid,shortname";
        
        if ($customerid!==-1){
            $sql .= " HAVING customerid=:customerid";
            $listAssessment=$DB->get_recordset_sql($sql, ['customerid'=>$customerid], 0, 0);
        } else {
            $listAssessment=$DB->get_recordset_sql($sql, [], 0, 0);
        }
            
         
        
         
         function isDuplicate($object, $seenObjects) {
            // Create a unique hash for the object (e.g., using serialization or custom hashing)
            $objectHash = serialize($object); // Example using serialization
            return isset($seenObjects[$objectHash]); // Check if hash exists in seenObjects
          }
          
        $seenObjects = []; // To store seen object hashes
        $uniqueObjects = []; // To store unique objects

         foreach ($listAssessment as $record) {
            if (!isDuplicate($record, $seenObjects)) {
                $uniqueObjects[] = $record;
                $seenObjects[serialize($record)] = true; // Store the hash for the current object
              }
            
        }

         $result=[
            (object)[
                'assessment_list'=>$uniqueObjects,  
                ]
        ];

        
        
        $listAssessment->close();
        
        return $result;     
        
        
    }


    public static function execute_returns(){
        return new external_multiple_structure(
            new external_single_structure([
                'assessment_list'=>new external_multiple_structure(
                    new external_single_structure([
                        'customerid'=>new external_value(PARAM_INT, 'customerid'),
                        'groupid'=>new external_value(PARAM_INT, 'groupid'),
                        'customercode'=>new external_value(PARAM_TEXT,'Customer shortcode'),
                        'groupname'=>new external_value(PARAM_TEXT, 'selected group'),
                        'billid'=>new external_value(PARAM_TEXT, 'selected billid'),
                        'firstname'=>new external_value(PARAM_TEXT, 'selected name'),
                        'lastname'=>new external_value(PARAM_TEXT, 'selected lastname'),
                        'shortname'=>new external_value(PARAM_TEXT, 'selected wbs'),
                        'fullname'=>new external_value(PARAM_TEXT, 'selected coursename'),
                        
                        'email'=>new external_value(PARAM_TEXT, 'email'),
                        'attendance'=>new external_value(PARAM_TEXT, 'Attendance score'),
                        'assessment'=>new external_value(PARAM_TEXT, 'Assessment score'),
                    ])
                    ),
                
            ])
        );
    }

}