<?php
namespace block_charts\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class load_projects extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        // No parameters are needed, so return an empty external_function_parameters object
        return new external_function_parameters([]);
    }



        /**
     * Show Partial Training Plan
     * @param array A list of params for display the table
     * @return array Return a array of courses
     */
    public static function execute() {
        global $DB,$USER;

        // Validate the context and capabilities if needed
        $context = \context_user::instance($USER->id);
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);


        // Query the database to fetch project shortnames
        // Assuming the table is `mdl_customer` and the column for shortnames is `shortname`
        $projects = $DB->get_records('customer', [], '', 'id, shortname');

        // Format the results into an array of associative arrays
        $result = [];
        foreach ($projects as $project) {
            $result[] = [
                'id' => $project->id,
                'shortname' => $project->shortname,
            ];
        }

        return $result;

    }

    public static function execute_returns() {
        // Define the return structure as an array of associative arrays
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Project ID'),
                'shortname' => new external_value(PARAM_TEXT, 'Project shortname'),
            ])
        );
    }


}