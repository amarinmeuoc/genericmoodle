<?php
namespace block_gantt_diagram\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_gantt extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'customerid'=>new external_value(PARAM_INT,'Customer id'),
                    'groupid'=>new external_value(PARAM_INT,'group id'),
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
        
        // Extract parameters
        $customerid = $request['params'][0]['customerid'];
        $groupid = $request['params'][0]['groupid'];

        // Preparar la condición para el grupo
        $groupCondition = '';
        if ($groupid != 0) {
            $groupCondition = 'AND groupid = :groupid';
                    // Fetch all tasks
                    $sql = "SELECT groupid, MIN(startdate) as startdate, MAX(enddate) as enddate, GROUP_CONCAT(trainees ORDER BY trainees SEPARATOR ', ') as trainees
                    FROM {trainingplan}
                    WHERE customerid = :customerid
                    $groupCondition
                    ORDER BY startdate";
                    $params = ['customerid' => $customerid];
                    $params['groupid'] = $groupid;
                    $tasks = $DB->get_records_sql($sql, $params);
                    if ($tasks) {
                        $group=$DB->get_record('grouptrainee',['id'=>$groupid],'id,name');
                        $trainee_arr = explode(',', $tasks[$group->id]->trainees);
    
                        // Remove duplicates from the array
                        $trainee_arr = array_unique(array_map('trim', $trainee_arr));
        
                        // Use array_reduce to concatenate the unique values
                        $tasks[$group->id]->trainees = array_reduce($trainee_arr, function ($carry, $item) {
                            if (empty($carry)) {
                                return $item; // Start with the first item
                            }
                            return $carry . ', ' . $item; // Concatenate with a comma and space
                        }, '');
        
                        $tasks[$group->id]->name=$group->name." Training";
                    }
                    
        } else {
            $groups=$DB->get_records('grouptrainee',['customer'=>$customerid,'hidden'=>0],'name ASC','id,name');
            // Fetch tasks for all groups
            $tasks = [];
            foreach ($groups as $group) {
                $sql = "SELECT groupid, MIN(startdate) as startdate, MAX(enddate) as enddate, GROUP_CONCAT(trainees ORDER BY trainees SEPARATOR ', ') as trainees
                        FROM {trainingplan}
                        WHERE customerid = :customerid
                        AND groupid = :groupid
                        GROUP BY groupid
                        ORDER BY startdate";
                $params = ['customerid' => $customerid, 'groupid' => $group->id];
                $groupTasks = $DB->get_records_sql($sql, $params);
                if ($groupTasks) {
                    $trainee_arr = explode(',', $groupTasks[$group->id]->trainees);

                    // Remove duplicates from the array
                    $trainee_arr = array_unique(array_map('trim', $trainee_arr));
    
                    // Use array_reduce to concatenate the unique values
                    $groupTasks[$group->id]->trainees = array_reduce($trainee_arr, function ($carry, $item) {
                        if (empty($carry)) {
                            return $item; // Start with the first item
                        }
                        return $carry . ', ' . $item; // Concatenate with a comma and space
                    }, '');
    
                    $groupTasks[$group->id]->name=$group->name." Training";
                }
                
                
                // Merge tasks for all groups
                $tasks = array_merge($tasks, $groupTasks);
            }
        }

         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
        
       // Formatear los datos según execute_returns
    $formattedTasks = [];
    $cont=1;
    /*
                const ganttData = [
                    {
                        id: 'Task_1',
                        name: 'Task 1',
                        start: '2023-10-01',
                        end: '2023-10-05',
                        progress: 50 // 50% complete
                    },
                    {
                        id: 'Task_2',
                        name: 'Task 2',
                        start: '2023-10-06',
                        end: '2023-10-10',
                        progress: 10, // 20% complete
                        dependencies: ['Task_1'] // Task 2 depends on Task 1
                    },
                    {
                        id: 'Task_3',
                        name: 'Task 3',
                        start: '2023-10-11',
                        end: '2023-10-15',
                        progress: 80, // 0% complete
                        dependencies: ['Task_2'] // Task 3 depends on Task 2
                    }
                ];
                */

                foreach ($tasks as $task) {
                    // Get the current timestamp
                    $currentDate = time();
                
                    // Calculate the total duration in seconds
                    $totalDuration = $task->enddate - $task->startdate;
                
                    // Calculate the elapsed duration in seconds
                    $elapsedDuration = $currentDate - $task->startdate;
                
                    // Calculate the progress percentage
                    if ($totalDuration > 0) {
                        $progress = ($elapsedDuration / $totalDuration) * 100;
                        // Ensure progress is between 0% and 100%
                        $progress = max(0, min(100, $progress)); // Clamp between 0 and 100
                    } else {
                        $progress = 0; // If total duration is 0 or invalid, set progress to 0
                    }
                
                    // Add the formatted task to the array
                    $formattedTasks[] = (object)[
                        'id' => (int)$cont++,
                        'name' => $task->name,
                        'start' => date('Y-m-d', $task->startdate),
                        'end' => date('Y-m-d', $task->enddate),
                        'progress' => (int)$progress, // Cast to integer
                        'description' => $task->trainees,
                    ];
                }

    return $formattedTasks;
       
        
    }
    

    public static function execute_returns() { 
        return new external_multiple_structure(
            new external_single_structure([
                'id'=>new external_value(PARAM_INT, 'Course id'),
               
                'name'=>new external_value(PARAM_TEXT,'Course name'),
                'start'=>new external_value(PARAM_TEXT,'Start date in unix time'),
                'end'=>new external_value(PARAM_TEXT,'End date in unix time'),
                'description'=>new external_value(PARAM_TEXT,'Trainees enroled in the course'),
                'progress'=>new external_value(PARAM_INT,'Last update'),
                
            ])
            
        );
       
    }
}
