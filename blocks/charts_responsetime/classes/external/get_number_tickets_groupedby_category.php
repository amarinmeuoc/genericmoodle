<?php
namespace block_charts_responsetime\external;

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
            
            'type' => new external_value(PARAM_TEXT, 'Type of report (month/year)'),
            'year' => new external_value(PARAM_INT, 'Year to analyze'),
            'projectid' => new external_value(PARAM_INT, 'Project ID'),
            'month' => new external_value(PARAM_INT, 'Month (if type=month)', PARAM_RAW, 0)
        ]);
    }

    /**
     * Show Partial Training Plan
     * @param array A list of params for display the table
     * @return array Return a array of courses
     */
    public static function execute($type, $year, $projectid, $month = null) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'type' => $type,
            'year' => $year,
            'projectid' => $projectid,
            'month' => $month
        ]);

        // Get project shortname
        $project = $DB->get_field('customer', 'shortname', ['id' => $projectid]);
        if (!$project) {
            throw new \invalid_parameter_exception('Invalid project ID');
        }

        // Prepare labels based on type
        if ($type === 'month') {
            if (!$month || $month===0) {
                throw new \invalid_parameter_exception('Month parameter required for monthly report');
            }
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $labels = range(1, $daysInMonth);
        } else {
            $labels = ['January', 'February', 'March', 'April', 'May', 'June', 
                      'July', 'August', 'September', 'October', 'November', 'December'];
        }

        // Get all categories through their subcategories
        $sql = "SELECT DISTINCT c.id, c.category
                FROM {ticket_category} c
                JOIN {ticket_subcategory} sc ON sc.categoryid = c.id
                JOIN {ticket} t ON t.subcategoryid = sc.id
                WHERE t.id LIKE :project";
        $categories = $DB->get_records_sql($sql, ['project' => $project . '-%']);

        $datasets = [];

        foreach ($categories as $category) {
            // Initialize data array for this category
            $data = array_fill(0, count($labels), 0);
            $counts = array_fill(0, count($labels), 0);

            // Get all tickets for this category and project WITHIN THE SPECIFIED TIME PERIOD
$sql = "SELECT t.id, t.dateticket 
FROM {ticket} t
JOIN {ticket_subcategory} sc ON t.subcategoryid = sc.id
WHERE sc.categoryid = :catid
AND t.id LIKE :project";

$params = ['catid' => $category->id, 'project' => $project . '-%'];

// Add date filtering based on report type
if ($type === 'month') {
$startDate = strtotime("$year-$month-01 00:00:00");
$endDate = strtotime("last day of $year-$month 23:59:59");

$sql .= " AND t.dateticket BETWEEN :startdate AND :enddate";
$params['startdate'] = $startDate;
$params['enddate'] = $endDate;
} else { // year
$sql .= " AND FROM_UNIXTIME(t.dateticket, '%Y') = :year";
$params['year'] = $year;
}

$tickets = $DB->get_records_sql($sql, $params);

            foreach ($tickets as $ticket) {
                // Get all actions for this ticket, ordered by date
                $actions = $DB->get_records('ticket_action', 
                    ['ticketid' => $ticket->id], 'dateaction ASC');

                // Calculate time between consecutive actions
                $prevAction = null;
                $firstAction=array_values($actions)[0];
                foreach ($actions as $action) {
                    if ($prevAction) {
                        $actionTime = $action->dateaction - $prevAction->dateaction;
                        $actionHours = round($actionTime / 3600, 2);

                        // Determine which period this belongs to
                        //$date = getdate($action->dateaction);
                        $date = getdate($firstAction->dateaction);
                        if ($type === 'month') {
                            $index = $date['mday'] - 1; // day of month
                        } else {
                            $index = $date['mon'] - 1; // month of year
                        }

                        // Only add if within our reporting period
                        if (isset($data[$index])) {
                            $data[$index] += $actionHours;
                            $counts[$index]++;
                        }
                    }
                    $prevAction = $action;
                }
            }

            // Calculate averages
            foreach ($data as $i => $value) {
                if ($counts[$i] > 0) {
                    $data[$i] = round($value / $counts[$i], 2);
                } else {
                    $data[$i] = 0; // No data for this period
                }
            }

            $datasets[] = [
                'label' => $category->category,
                'data' => $data,
                'backgroundColor' => self::generate_color($category->id, 0.1),
                'borderColor' => self::generate_color($category->id, 1),
                'borderWidth' => 2,
                'tension' => 0.1,
                'fill' => true
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }

    private static function generate_color($seed, $opacity = 0.6) {
        mt_srand($seed);
        $r = mt_rand(0, 255);
        $g = mt_rand(0, 255);
        $b = mt_rand(0, 255);
        return "rgba($r, $g, $b, $opacity)";
    }

    public static function execute_returns() {
        return new external_single_structure([
            'labels' => new external_multiple_structure(new external_value(PARAM_TEXT, 'Time period labels')),
            'datasets' => new external_multiple_structure(
                new external_single_structure([
                    'label' => new external_value(PARAM_TEXT, 'Category name'),
                    'data' => new external_multiple_structure(new external_value(PARAM_FLOAT, 'Average action time in hours')),
                    'backgroundColor' => new external_value(PARAM_TEXT, 'Background color'),
                    'borderColor' => new external_value(PARAM_TEXT, 'Border color'),
                    'borderWidth' => new external_value(PARAM_INT, 'Border width'),
                    'tension' => new external_value(PARAM_FLOAT, 'Line tension'),
                    'fill' => new external_value(PARAM_BOOL, 'Fill under line')
                ])
            )
        ]);
    
    }
    
}

