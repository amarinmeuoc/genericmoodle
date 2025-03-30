<?php
namespace block_charts_responsetime\external;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_api;

class load_categories extends external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Get ticket categories
     * @return array Return array of categories
     */
    public static function execute() {
        global $DB;
        
        // Validate context and capabilities
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        // Get records from database
        $categories = $DB->get_records('ticket_category', null, 'id ASC', 'id,category');
        
        // Format the results properly
        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'subcategory' => $category->category  // Changed to match your return structure
            ];
        }
        
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Category id'),
                'subcategory' => new external_value(PARAM_TEXT, 'Category name')
            ])
        );
    }
}