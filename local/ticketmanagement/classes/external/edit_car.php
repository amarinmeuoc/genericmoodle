<?php
namespace local_ticketmanagement\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class edit_car extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                   'id' => new external_value(PARAM_INT, 'User ID'),
                    'platenumber' => new external_value(PARAM_TEXT, 'Plate number of the car', VALUE_OPTIONAL),
                    'model' => new external_value(PARAM_TEXT, 'Car model'),
                    'brand' => new external_value(PARAM_TEXT, 'Car brand'),
                    'color' => new external_value(PARAM_TEXT, 'Car color', VALUE_OPTIONAL),
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
        
       // $params=self::validate_parameters(self::execute_parameters(), ['newTicket' => $newTicket]);
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        
        $id=$request['params'][0]['id'];   
        $platenumber=isset($request['params'][0]['platenumber'])?$request['params'][0]['platenumber']:null;
        $model=$request['params'][0]['model'];   
        $brand=$request['params'][0]['brand'];
        $color=isset($request['params'][0]['color'])?$request['params'][0]['color']:null;
   
        
        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        $record= new \stdClass();
        $record->id=$id;
        if (!is_null($platenumber))
            $record->platenumber=$platenumber;
        $record->model=$model;
        $record->brand=$brand;
        if (!is_null($color))
            $record->color=$color;

        $DB->update_record('ticketmanagement_cars', $record);
        
        $ObjReturn=[
            'listadoCar'=>[
                'id'=>$record->id,
                'platenumber'=>$record->platenumber,
                'brand'=>$record->brand,
                'model'=>$record->model,
                'color'=>$record->color
            ]
        ];

        // Retornar una respuesta (ej. el ID del nuevo ticket creado)
        return $ObjReturn;
    }


    public static function execute_returns() {
        return new external_single_structure([
            'listadoCar' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID of cars table'),
                'platenumber' => new external_value(PARAM_TEXT, 'platenumber of the car'),
                'brand' => new external_value(PARAM_TEXT, 'brand of the car'),
                'model' => new external_value(PARAM_TEXT, 'model of the car'),
                'color' => new external_value(PARAM_TEXT, 'color of the car'),
            ])
        ]);
    }

}