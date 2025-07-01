<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class remove_ticketcategory extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'categoryId' => new external_value(PARAM_INT, 'category ID', VALUE_OPTIONAL),
                   'subcategoryId' => new external_value(PARAM_INT, 'subcategory ID', VALUE_OPTIONAL),
                ]),
                'Array of parameters',VALUE_OPTIONAL
            ) 
        ]);
    }


    public static function execute($params) {
        global $DB;
        
        // Validate parameters (params is optional, so it might be empty)
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        
        // Initialize result structure
        $result = [
            'categoryid' => null,
            'subcategories_deleted' => [],
            'total_tickets_removed' => 0
        ];
        
        // Check if params is empty or not provided
        if (empty($request['params'])) {
            throw new moodle_exception('missing_parameters', 'local_ticketmanagement', '', 'Debe proporcionar al menos categoryId o subcategoryId');
        }

        $first_param = $request['params'][0];
        $categoryid = $first_param['categoryId'] ?? null;
        $subcategoryid = $first_param['subcategoryId'] ?? null;

        // Start transaction
        $transaction = $DB->start_delegated_transaction();

        try {
            if ($subcategoryid && !$categoryid) {
                // Case: Only subcategoryid provided - delete just this subcategory
                return self::delete_single_subcategory($subcategoryid, $transaction);
            } elseif ($categoryid) {
                // Case: categoryid provided (with or without subcategoryid)
                return self::delete_category_and_subcategories($categoryid, $subcategoryid, $transaction);
            } else {
                throw new moodle_exception('missing_parameters', 'local_ticketmanagement', '', 'Debe proporcionar al menos categoryId o subcategoryId');
            }
        } catch (Exception $e) {
            $transaction->rollback($e);
            throw new moodle_exception('error_removing_category', 'local_ticketmanagement', '', $e->getMessage());
        }
    }

    private static function delete_single_subcategory($subcategoryid, $transaction) {
        global $DB;
        
        $result = [
            'categoryid' => null,
            'subcategories_deleted' => [],
            'total_tickets_removed' => 0
        ];
        
        $subcategory = $DB->get_record('ticket_subcategory', ['id' => $subcategoryid], '*', MUST_EXIST);
        
        $subcategory_result = [
            'subcategoryid' => $subcategory->id,
            'tickets_removed' => 0
        ];
        
        // Delete all tickets in this subcategory
        $tickets = $DB->get_records('ticket', ['subcategoryid' => $subcategory->id]);
        
        foreach ($tickets as $ticket) {
            $itemid = $DB->get_field('ticket', 'lastupdate', ['id' => $ticket->id]);
            
            if ($itemid) {
                $fs = get_file_storage();
                $contextid = \context_system::instance()->id;
                $files = $fs->get_area_files($contextid, 'local_ticketmanagement', 'sharedfiles', $itemid, 'sortorder', false);
                
                foreach ($files as $file) {
                    $file->delete();
                }
            }
            
            $DB->delete_records('ticket', ['id' => $ticket->id]);
            $subcategory_result['tickets_removed']++;
            $result['total_tickets_removed']++;
        }
        
        // Delete the subcategory
        $DB->delete_records('ticket_subcategory', ['id' => $subcategory->id]);
        $result['subcategories_deleted'][] = $subcategory_result;
        
        $transaction->allow_commit();
        return $result;
    }

    private static function delete_category_and_subcategories($categoryid, $specific_subcategoryid, $transaction) {
        global $DB;
        
        $result = [
            'categoryid' => $categoryid,
            'subcategories_deleted' => [],
            'total_tickets_removed' => 0
        ];
        
        // Get all subcategories (or specific one if provided)
        $subconditions = ['categoryid' => $categoryid];
        if ($specific_subcategoryid) {
            $subconditions['id'] = $specific_subcategoryid;
        }
        
        $subcategories = $DB->get_records('ticket_subcategory', $subconditions);
        
        foreach ($subcategories as $subcategory) {
            $subcategory_result = [
                'subcategoryid' => $subcategory->id,
                'tickets_removed' => 0
            ];
            
            // Delete all tickets in this subcategory
            $tickets = $DB->get_records('ticket', ['subcategoryid' => $subcategory->id]);
            
            foreach ($tickets as $ticket) {
                $itemid = $DB->get_field('ticket', 'lastupdate', ['id' => $ticket->id]);
                
                if ($itemid) {
                    $fs = get_file_storage();
                    $contextid = \context_system::instance()->id;
                    $files = $fs->get_area_files($contextid, 'local_ticketmanagement', 'sharedfiles', $itemid, 'sortorder', false);
                    
                    foreach ($files as $file) {
                        $file->delete();
                    }
                }
                
                $DB->delete_records('ticket', ['id' => $ticket->id]);
                $subcategory_result['tickets_removed']++;
                $result['total_tickets_removed']++;
            }
            
            // Delete the subcategory
            $DB->delete_records('ticket_subcategory', ['id' => $subcategory->id]);
            $result['subcategories_deleted'][] = $subcategory_result;
        }
        
        // Delete the main category only if we're not targeting a specific subcategory
        if (!$specific_subcategoryid) {
            $DB->delete_records('ticket_category', ['id' => $categoryid]);
        }
        
        $transaction->allow_commit();
        return $result;
    }

    public static function execute_returns() {
        return new external_single_structure([
            'categoryid' => new external_value(PARAM_INT, 'ID of the removed category'),
            'subcategories_deleted' => new external_multiple_structure(
                new external_single_structure([
                    'subcategoryid' => new external_value(PARAM_INT, 'ID of the removed subcategory'),
                    'tickets_removed' => new external_value(PARAM_INT, 'Number of tickets removed in this subcategory')
                ]), 'List of deleted subcategories and their ticket counts'
            ),
            'total_tickets_removed' => new external_value(PARAM_INT, 'Total number of tickets removed')
        ]);
    }

}