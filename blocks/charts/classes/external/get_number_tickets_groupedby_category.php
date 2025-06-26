<?php
namespace block_charts\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;
use DateTime;

class get_number_tickets_groupedby_category extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'project'=>new external_value(PARAM_INT,'Project id'),
                    'year'=>new external_value(PARAM_INT,'Year'),
                    'month'=>new external_value(PARAM_INT,'Month'),
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
        $project=$request['params'][0]['project'];
        $year=$request['params'][0]['year'];
        $month=$request['params'][0]['month'];

        

        if ($month === 0) {
            $startdate = (new DateTime("$year-01-01 00:00:00"))->getTimestamp();
            $enddate = (new DateTime("$year-12-31 23:59:59"))->getTimestamp();
        } else {
            $daysInMonth = getDaysInMonth($month, $year);
            $startdate = (new DateTime("$year-$month-01 00:00:00"))->getTimestamp();
            $enddate = (new DateTime("$year-$month-$daysInMonth 23:59:59"))->getTimestamp();
        }
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);
        
        $project=$DB->get_record('customer', ['id'=>$project], 'shortname', IGNORE_MISSING);
        $projectname=$project->shortname;

        
        /*
        I need the following structure
        [
            label => selectedGroup,
            dataSet => 'All the collection for the selected group and year and category
            stack => 'selectedcategory'
        ]
        */
        $result = [];
        $countByGroupAndCategory = []; // Structure: $countByGroupAndCategory[group][category] = count
        
        // 1. Fetch all matching tickets
        $sql = "SELECT * FROM {ticket} 
                WHERE id LIKE :project AND dateticket >= :startdate AND dateticket <= :enddate";
        $rs = $DB->get_recordset_sql($sql, [
            'project' => $projectname . '%', // E.g., "PROJ-GROUP%"
            'startdate' => $startdate,
            'enddate' => $enddate
        ]);
        
        // 2. Count tickets by GROUP (from ID) and CATEGORY
        foreach ($rs as $record) {
            // Extract GROUP from ID (format: "project-GROUP-timestamp...")
            $idParts = explode('-', $record->id);
            $group = (count($idParts) >= 2) ? $idParts[1] : 'default';
        
            // Get CATEGORY hierarchy
            $subcategory = $DB->get_record('ticket_subcategory', ['id' => $record->subcategoryid], 'categoryid');
            $category = $DB->get_record('ticket_category', ['id' => $subcategory->categoryid], 'category');
            $categoryName = $category->category;
        
            // Initialize counts if not exists
            if (!isset($countByGroupAndCategory[$group])) {
                $countByGroupAndCategory[$group] = [];
            }
            if (!isset($countByGroupAndCategory[$group][$categoryName])) {
                $countByGroupAndCategory[$group][$categoryName] = 0;
            }
        
            // Increment count for this group/category
            $countByGroupAndCategory[$group][$categoryName]++;
        }
        $rs->close();
        
        // 3. Build the final result structure
        foreach ($countByGroupAndCategory as $group => $categories) {
            foreach ($categories as $categoryName => $count) {
                $result[] = [
                    'label' => $group,      // E.g., "GROUP1"
                    'dataSet' => $count,    // Number of tickets in this group/category
                    'stack' => $categoryName // E.g., "Hardware"
                ];
            }
        }

                
        return $result;
    }


    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'label' => new external_value(PARAM_TEXT, 'The group name extracted from the ticket ID'),
                'dataSet' => new external_value(PARAM_INT, 'Number of tickets in this group/category'),
                'stack' => new external_value(PARAM_TEXT, 'The category name'),
            ]),
            'Array of grouped ticket counts by group and category'
        );
    }

    
}

function getDaysInMonth(int $month, int $year): int {
    // Validate month input (1-12)
    if ($month < 1 || $month > 12) {
        throw new \InvalidArgumentException('Month must be between 1 and 12');
    }

    // For February, check if it's a leap year
    if ($month === 2) {
        if (($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0) {
            return 29; // Leap year
        }
        return 28; // Common year
    }

    // Months with 31 days
    if (in_array($month, [1, 3, 5, 7, 8, 10, 12])) {
        return 31;
    }

    // All remaining months have 30 days
    return 30;
}