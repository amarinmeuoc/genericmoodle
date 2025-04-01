<?php
namespace block_chart_percentaje_it\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;
use DateTime;


class get_states_percentage extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'type' => new external_value(PARAM_TEXT, 'Type of report (month/year)'),
            'year' => new external_value(PARAM_INT, 'Year to analyze'),
            'projectid' => new external_value(PARAM_INT, 'Project ID'),
            'month' => new external_value(PARAM_INT, 'Month (if type=month)', VALUE_DEFAULT, 0),
            'groupid' => new external_value(PARAM_INT, 'Group of pupil', VALUE_DEFAULT, 0)
        ]);
    }


    /**
     * Show Partial Training Plan
     * @param array A list of params for display the table
     * @return array Return a array of courses
     */
    public static function execute($type, $year, $projectid, $month = 0, $groupid = 0) {
        global $DB;
    
        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'type' => $type,
            'year' => $year,
            'projectid' => $projectid,
            'month' => $month,
            'groupid' => $groupid
        ]);
    
        // Verify tables exist (debugging only - remove in production)
        $tables = ['customer', 'grouptrainee', 'ticket'];
        foreach ($tables as $table) {
            if (!$DB->get_manager()->table_exists($table)) {
                throw new \moodle_exception("Table $table does not exist");
            }
        }
    
        // Get project shortname
        $project = $DB->get_record('customer', ['id' => $projectid], 'shortname', MUST_EXIST);
        $projectshortname = $project->shortname;
    
        // Initialize group condition
        $groupcondition = '';
        $groupparams = [];
        
        if ($groupid != 0) {
            $group = $DB->get_record('grouptrainee', ['id' => $groupid, 'customer' => $projectid], 'name', MUST_EXIST);
            $groupcondition = " AND " . $DB->sql_like('id', ':grouplike');
            $groupparams['grouplike'] = $projectshortname . '-' . $group->name . '-%';
        }
    
        // Date range calculation
        if ($type == 'year') {
            $starttime = mktime(0, 0, 0, 1, 1, $year);
            $endtime = mktime(23, 59, 59, 12, 31, $year);
        } else {
            $starttime = mktime(0, 0, 0, $month, 1, $year);
            $endtime = mktime(23, 59, 59, $month, date('t', $starttime), $year);
        }
    
        // Base SQL conditions
        $sql = "SELECT state, COUNT(*) as count 
                FROM {ticket} 
                WHERE " . $DB->sql_like('id', ':projectlike') . "
                $groupcondition
                AND dateticket BETWEEN :starttime AND :endtime
                GROUP BY state";
    
        $params = [
            'projectlike' => $projectshortname . '-%',
            'starttime' => $starttime,
            'endtime' => $endtime
        ];
        $params = array_merge($params, $groupparams);
    
        // Execute query
        $records = $DB->get_records_sql($sql, $params);
    
        // Process results
        $states = ['Open' => 0, 'Assigned' => 0, 'Closed' => 0, 'Cancelled' => 0];
        $total = 0;
        
        foreach ($records as $record) {
            if (array_key_exists($record->state, $states)) {
                $states[$record->state] = $record->count;
                $total += $record->count;
            }
        }
    
        // Calculate percentages
        $results = [];
        foreach ($states as $state => $count) {
            $results[$state] = ($total > 0) ? round(($count / $total) * 100, 2) : 0;
        }

        

        
        return $results;
    }


    public static function execute_returns() {
        return new external_single_structure([
            'Open' => new external_value(PARAM_FLOAT, 'Percentage of open tickets'),
            'Assigned' => new external_value(PARAM_FLOAT, 'Percentage of assigned tickets'),
            'Closed' => new external_value(PARAM_FLOAT, 'Percentage of closed tickets'),
            'Cancelled' => new external_value(PARAM_FLOAT, 'Percentage of cancelled tickets')
        ]);
    }

    
}

