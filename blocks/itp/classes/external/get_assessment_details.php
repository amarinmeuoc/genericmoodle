<?php
namespace block_itp\external;

require_once($CFG->dirroot.'/user/profile/lib.php'); 

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_assessment_details extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'courseid'=>new external_value(PARAM_INT,'Course id'),
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
    
        // Validate parameters and extract them
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        $courseid = $request['params'][0]['courseid'];
        $email = $request['params'][0]['email'];
    
        // Security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);
    
        // Fetch user and course data
        $user = $DB->get_record('user', ['email' => $email]);
        $coursename = $DB->get_field('course', 'fullname', ['id' => $courseid]);
    
        // Load custom fields and prepare personal data
        profile_load_custom_fields($user);
        $personaldata = (object)[
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'billid' => $user->profile['billid'],
            'coursename' => $coursename
        ];
    
        // Fetch exams data
        $exams = $DB->get_records_sql('SELECT 
                                        i.itemname, 
                                        c.id, 
                                        c.fullname as coursename,
                                        i.itemtype, 
                                        FORMAT(g.finalgrade, 2) as finalgrade,
                                        g.feedback,
                                        g.timemodified
                                    FROM {course} AS c
                                    INNER JOIN {grade_items} as i on i.courseid = c.id
                                    INNER JOIN {grade_grades} as g on g.itemid = i.id
                                    INNER JOIN {user} as u on u.id = g.userid
                                    WHERE c.id = ? AND u.id = ? AND i.itemtype <> "category" 
                                    AND LOWER(i.itemtype) NOT LIKE "%attendance%" 
                                    AND i.itemtype <> "course"', 
                                    [$courseid, $user->id]);
    
        // Transform exams data
        $exams = array_map(function($elem) {
            $textValue = $elem->finalgrade;
            if ($elem->itemname === 'Attitude' || $elem->itemname === 'Participation') {
                $grades = [
                    '1' => 'None',
                    '2' => 'Poor',
                    '3' => 'Sufficient',
                    '4' => 'Good',
                    '5' => 'Very Good',
                    '6' => 'Excellent'
                ];
                $textValue = $grades[$elem->finalgrade] ?? 'None';
            }
            return (object)[
                'kpi' => $elem->itemname,
                'id' => $elem->id,
                'coursename' => $elem->coursename,
                'type' => $elem->itemtype,
                'score' => $textValue,
                'feedback' => strip_tags($elem->feedback),
                'timemodified' => $elem->timemodified
            ];
        }, array_values($exams));
    
        // Prepare result
        $result = (object)[
            'examenes' => $exams,
            'personaldata' => $personaldata
        ];
    
        return $result;
    }

    

    public static function execute_returns() {
        return new external_single_structure([
            'examenes' => new external_multiple_structure( // An array of course records
                new external_single_structure([
                    'kpi' => new external_value(PARAM_TEXT, 'type of KPI'),
                    'id' => new external_value(PARAM_INT, 'Course Id'),
                    'coursename'=>new external_value(PARAM_TEXT, 'Course name'),
                    'type'=>new external_value(PARAM_TEXT, 'If is a manual/automatic score'),
                    'score'=>new external_value(PARAM_TEXT, 'Achieved score'),
                    'feedback'=>new external_value(PARAM_TEXT, 'If there is any feedback'),
                    'timemodified'=>new external_value(PARAM_INT, 'Last updated'),
                ])
            ),
            'personaldata' => new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'Name'),
                'email' => new external_value(PARAM_TEXT, 'Email'),
                'billid' => new external_value(PARAM_TEXT, 'Bill Id'),
                'coursename' => new external_value(PARAM_TEXT, 'Course Name'),
            ])
        ]);
   
    }
}