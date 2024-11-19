<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_logistic_users extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'dummy'=>new external_value(PARAM_TEXT,'dummy name'),
                    
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

        // Validación de parámetros (aunque estén vacíos)
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);

        // Comprobaciones de seguridad
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

         

$sql='SELECT u.id,username,firstname, lastname,email,
        MAX(IF(uf.shortname = "role", ui.data, "")) AS role_name
        FROM mdl_user AS u
        INNER JOIN mdl_user_info_data AS ui ON ui.userid=u.id
        INNER JOIN mdl_user_info_field AS uf ON uf.id=ui.fieldid
        GROUP by id,username,firstname, lastname
        HAVING role_name=:role_name';
        
        // Parámetros para la consulta
        $params = [
            'role_name' => 'logistic', // Nombre del campo de perfil personalizado
             // Valor del campo de perfil
        ];

         $users=$DB->get_records_sql($sql, $params);
         
        // Formatear los resultados en un array adecuado
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'name' => $user->firstname . ' ' . $user->lastname,
                // Agregar cualquier otro dato específico que necesites
            ];
        }

        return $result;
    }


    public static function execute_returns() {
        // Estructura de retorno para la función
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'User ID'),
                'name' => new external_value(PARAM_TEXT, 'User full name'),
                // Agrega otros campos si es necesario, ajustando el formato
            ])
        );
    }
    
}