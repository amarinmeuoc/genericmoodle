<?php

namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class check_reminders extends \core_external\external_api {
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    public static function execute() {
        global $DB, $USER;
        
        // Validate context and capabilities
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/ticketmanagement:viewreminders', $context);

        $time = time();
        // La siguiente consulta muestra las multas que esten marcadas como pendientes y su fecha de vencimiento sea posterior al 
        // dia actual y quede menos de 6 dias para que venzan
        $reminders = $DB->get_records_sql("
            SELECT f.id, f.ticketid, f.expiration_date 
            FROM {ticketmanagement_fines} f
            JOIN {ticket} t ON f.ticketid = t.id
            WHERE f.reminder = 1 
            AND f.expiration_date > ?
            AND f.expiration_date < ? + (6 * 24 * 60 * 60)
            AND t.assigned = ?
            AND f.status = 'pending'", 
            [$time, $time, $USER->id]);

        return [
            'reminders' => array_map(function($r) {
                return [
                    'ticketid' => $r->ticketid,
                    'expiration' => userdate($r->expiration_date)
                ];
            }, array_values($reminders))
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'reminders' => new external_multiple_structure(
                new external_single_structure([
                    'ticketid' => new external_value(PARAM_TEXT, 'Ticket ID'),
                    'expiration' => new external_value(PARAM_TEXT, 'Expiration date')
                ])
            )
        ]);
    }
}