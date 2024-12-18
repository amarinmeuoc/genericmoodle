<?php
namespace report_partialplan\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_courses extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'customerid'=>new external_value(PARAM_INT,'Customer Id'),
                    'bydate'=>new external_value(PARAM_TEXT,'The Date to filter by')
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
        
        // Extract parameters
        $customerid=$request['params'][0]['customerid'];
        $bydate=$request['params'][0]['bydate'];

        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        
        $sql= "SELECT * FROM {trainingplan}
                WHERE startdate<=:startdate AND enddate>=:enddate AND customerid=:customer";
        $courses=$DB->get_records_sql($sql,['customer'=>$customerid,'startdate'=>$bydate, 'enddate'=>$bydate]);

        $courses=array_values($courses);

         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);

        // Process data (if needed)
        // Replace this with your actual data processing logic based on the parameters

        // Return response data
    
        foreach ($courses as $key => $row) {
            # code...
            $courses[$key]->id=$row->id;
            $courses[$key]->customer=$row->customer;
            $courses[$key]->wbs=$row->wbs;
            $courses[$key]->coursename=$row->coursename;
            $courses[$key]->start=$row->startdate;
            $courses[$key]->end=$row->enddate;
            $courses[$key]->num_trainees=$row->num_trainees;
            $courses[$key]->assignation=$row->trainees;
            $courses[$key]->location=$row->location;
            $courses[$key]->provider=$row->provider;
            $courses[$key]->lastupdate=$row->lastupdate;
            
        }
        
        return $courses;
    }

    public static function execute_returns() {
        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        return new external_multiple_structure(
            new external_single_structure([
                'id'=>new external_value(PARAM_INT, 'Id'),
                'customer'=>new external_value(PARAM_INT, 'Customer id'),
                'wbs' => new external_value(PARAM_TEXT, 'Course WBS'),
                'coursename' => new external_value(PARAM_TEXT, 'Coursename'),
                'start'=>new external_value(PARAM_INT, 'Start date'),
                'end'=>new external_value(PARAM_INT, 'End date'),
                'num_trainees'=>new external_value(PARAM_INT, 'Num trainees'),
                'assignation'=>new external_value(PARAM_TEXT, 'List of trainees enroled in the course'),
                'location'=>new external_value(PARAM_TEXT, 'Location'),
                'provider'=>new external_value(PARAM_TEXT, 'Provider'),
                'lastupdate'=>new external_value(PARAM_INT, 'Updating date'),
            ])
        );
       
    }
}