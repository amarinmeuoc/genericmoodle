<?php
namespace block_chart_percentaje_it\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class load_vessels extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'projectid' => new external_value(PARAM_INT, 'ID del proyecto', VALUE_REQUIRED),
        ]);
    }



        /**
     * Show Partial Training Plan
     * @param array A list of params for display the table
     * @return array Return a array of courses
     */
    public static function execute($projectid) {
        global $DB;

        // Validar el parámetro de entrada
        $params = self::validate_parameters(self::execute_parameters(), ['projectid' => $projectid]);

        // Validar el contexto del sistema (opcional, pero recomendado)
        $context = \context_system::instance();
        self::validate_context($context);

        // Consultar la tabla mdl_grouptrainee
        $groups = $DB->get_records('grouptrainee', ['customer' => $params['projectid']], '', 'id, name');

        // Formatear los resultados
        $result = [];
        foreach ($groups as $group) {
            $result[] = [
                'id' => $group->id,
                'name' => $group->name,
            ];
        }

        return $result;

    }

    public static function execute_returns() {
        // Define the return structure as an array of associative arrays
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID del grupo'),
                'name' => new external_value(PARAM_TEXT, 'Nombre del grupo'),
            ])
        );
    }


}