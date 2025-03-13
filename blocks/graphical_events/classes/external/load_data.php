<?php
namespace block_graphical_events\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class load_data extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params' => new external_multiple_structure(
                new external_single_structure([
                    'projectid' => new external_value(PARAM_INT, 'ID of the project', VALUE_REQUIRED),
                    'groupid' => new external_value(PARAM_INT, 'ID of the group', VALUE_REQUIRED),
                    'userid' => new external_value(PARAM_INT, 'ID of the user', VALUE_OPTIONAL, 0),
                ])
            )
        ]);
    }

    /**
     * Fetch and return ticket counts based on the provided parameters
     * @param array $params Contains projectid, groupid, and userid
     * @return array Ticket counts by state
     */
    public static function execute($params) {
        global $DB;

        // Validate the parameters
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        $params = $request['params'][0];

        // Validate the context (optional but recommended)
        $context = \context_system::instance();
        self::validate_context($context);

        // Extract parameters
        $projectid = $params['projectid'];
        $groupid = $params['groupid'];
        $userid = $params['userid'] ?? 0; // Default to 0 if userid is not provided

        // Get the project shortname from mdl_customer
        $project = $DB->get_record('customer', ['id' => $projectid], 'shortname');
        if (!$project) {
            throw new \moodle_exception('Project not found');
        }
        $project_shortname = $project->shortname;

        //Get the group name from mdl_grouptrainee
        $group=$DB->get_record('grouptrainee',['id'=>$groupid],'name');
        if (!$group) {
            throw new \moodle_exception('Group not found');
        }

        $group_name = $group->name;

        $sql = "SELECT state, COUNT(*) as count 
        FROM {ticket} 
        WHERE id LIKE :id 
        GROUP BY state";

        $conditions = [
            'id' => '%' . $project_shortname . '%' . $group_name . '%',
        ];

        if ($userid!==0){
            //Si se muestran los tickets del usuario se cambia la consulta
            $sql = "SELECT state, COUNT(*) as count 
            FROM {ticket} 
            WHERE id LIKE :id AND assigned=:assigned
            GROUP BY state";  

            $conditions = [
                'id' => '%' . $project_shortname . '%' . $group_name . '%',
                'assigned'=>$userid
            ];
        }

        // Fetch all ticket counts grouped by state
        $ticket_counts = $DB->get_records_sql($sql, $conditions);

        // Initialize counts for each state
        $totaltickets_open = 0;
        $totaltickets_cancelled = 0;
        $totaltickets_closed = 0;
        $totaltickets_assigned = 0;

        // Process the results
        foreach ($ticket_counts as $record) {
            switch ($record->state) {
                case 'Open':
                    $totaltickets_open = $record->count;
                    break;
                case 'Cancelled':
                    $totaltickets_cancelled = $record->count;
                    break;
                case 'Closed':
                    $totaltickets_closed = $record->count;
                    break;
                case 'Assigned':
                    $totaltickets_assigned = $record->count;
                    break;
            }
        }

        // Calculate total tickets
        $totaltickets = $totaltickets_open + $totaltickets_cancelled + $totaltickets_closed + $totaltickets_assigned;

        if ($totaltickets===0){
            $sql = "SELECT COUNT(*) as count 
                FROM {ticket} 
                WHERE id LIKE :id";

                $conditions = [
                    'id' => '%' . $project_shortname . '%' . $group_name . '%',
                ];
            $totaltickets=$DB->count_records_sql($sql,$conditions);
            
        }

        // Prepare the result
        $result = [
            [
                'totaltickets_open' => $totaltickets_open,
                'totaltickets_cancelled' => $totaltickets_cancelled,
                'totaltickets_closed' => $totaltickets_closed,
                'totaltickets_assigned' => $totaltickets_assigned,
                'totaltickets' => $totaltickets,
            ],
        ];

        return $result;
    }

    /**
     * Returns description of method return value
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'totaltickets_open' => new external_value(PARAM_INT, 'Number of tickets with state field equal to: Open'),
                'totaltickets_cancelled' => new external_value(PARAM_INT, 'Number of tickets with state field equal to: Cancelled'),
                'totaltickets_closed' => new external_value(PARAM_INT, 'Number of tickets with state field equal to: Closed'),
                'totaltickets_assigned' => new external_value(PARAM_INT, 'Number of tickets with state field equal to: Assigned'),
                'totaltickets' => new external_value(PARAM_INT, 'Total tickets within a project'),
            ])
        );
    }
}