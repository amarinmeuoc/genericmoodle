<?php
namespace report_partialplan\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_training_plan extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'customerid'=>new external_value(PARAM_INT,'Customer id'),
                    'groupid'=>new external_value(PARAM_INT,'group id'),
                    'billid'=>new external_value(PARAM_TEXT,'Billid'),
                    'unixtime'=>new external_value(PARAM_INT,'Start date in unix time'),
                    'orderby'=>new external_value(PARAM_TEXT,'what ordering has been applied'),
                    'order'=>new external_value(PARAM_BOOL,'if is true or false')
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
        global $DB,$USER;
        
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        
        // Extract parameters
        $customer=$request['params'][0]['customerid'];
        
        
        $groupid=$request['params'][0]['groupid'];
        $billid=$request['params'][0]['billid'];
        $unixtime=$request['params'][0]['unixtime'];
        $orderby=$request['params'][0]['orderby'];
        $order=$request['params'][0]['order'];
             
        
        $training_plan= new \report_partialplan\TrainingPlan($order,$orderby);
        $formatedDate=date('d-m-Y',$unixtime); 
        
        //Se extrae el customer shortname para obtener el training plan
        $customer=$DB->get_field('customer','shortname',['id'=>$customer]);

        $training_plan->setTrainingPlan($customer,$unixtime);
        
        
        $list_of_courses=$training_plan->getTrainingPlanFiltered($groupid,$billid);
        
        $list_of_group=$training_plan->group;
        
        //Getting the total dates of the training plan
        //get customer id from customer table
        $customerid=$DB->get_field('customer','id',['shortname'=>$customer]);
        $dates_trainingplan=$DB->get_records('trainingplan', ['customerid'=>$customerid], 'startdate', 'startdate,enddate');
        $dates_trainingplan=array_values($dates_trainingplan);
        
        $minStartdate=time();
        $maxEnddate=time();

        if (!empty($dates_trainingplan)){ 
            $startdate_arr=array_map(function($item){
                return $item->startdate;
            },$dates_trainingplan);
            
            $enddate_arr=array_map(function($item){
                return $item->enddate;
            },$dates_trainingplan);
            
            $minStartdate=min($startdate_arr);
            $maxEnddate=max($enddate_arr);
        }
        
        
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);
        
       $data=[
                (object)[
                    'formatedDate'=>$formatedDate,
                    'customerid'=>$customerid,
                    'group'=>$list_of_group,
                    'courses'=>$list_of_courses,
                    'orderbystartdate'=>$orderby==='startdate'?true:false,
                    'orderbyenddate'=>$orderby==='enddate'?true:false,
                    'orderby'=>$orderby,
                    'order'=>$order,
                    'minstartdate'=>$minStartdate,
                    'maxenddate'=>$maxEnddate
                ],
       ];
       
        return $data;
    }
    

    public static function execute_returns() { 
        return new external_multiple_structure(
                new external_single_structure([
                    'formatedDate'=>new external_value(PARAM_TEXT,'Formated date'),
                    'customerid'=>new external_value(PARAM_INT,'Customer Id'),
                    'group'=>new external_multiple_structure(
                        new external_single_structure([
                        'id'=>new external_value(PARAM_INT,'group id'),
                        'name'=>new external_value(PARAM_TEXT,'group name'),
                        ]),
                    ),
                    'courses'=>new external_multiple_structure(
                        new external_single_structure([
                            'id'=>new external_value(PARAM_INT, 'Course id'),
                            'customerid'=>new external_value(PARAM_INT, 'Customer id'),
                            'groupid'=>new external_value(PARAM_INT,'group id'),
                            'wbs'=>new external_value(PARAM_TEXT,'Course shortname'),
                            'course'=>new external_value(PARAM_TEXT,'Course name'),
                            'startdate'=>new external_value(PARAM_INT,'Start date in unix time'),
                            'enddate'=>new external_value(PARAM_INT,'End date in unix time'),
                            'num_trainees'=>new external_value(PARAM_INT,'Num trainees'),
                            'trainees'=>new external_value(PARAM_TEXT,'Trainees enroled in the course'),
                            'location'=>new external_value(PARAM_TEXT,'location'),
                            'provider'=>new external_value(PARAM_TEXT,'Provider'),
                            'lastupdate'=>new external_value(PARAM_INT,'Last update'),
                            'terminado'=>new external_value(PARAM_BOOL,'If course has finished'),
                        ])
                    ),
                    'orderbystartdate'=>new external_value(PARAM_BOOL, 'if ordering by startdate'),
                    'orderbyenddate'=>new external_value(PARAM_BOOL,'If ordergin by enddate'),
                    'orderby'=>new external_value(PARAM_TEXT,'what ordering has been applied'),
                    'order'=>new external_value(PARAM_BOOL,'if is true or false'),
                    'minstartdate'=>new external_value(PARAM_TEXT,'Begining of the project'),
                    'maxenddate'=>new external_value(PARAM_TEXT,'Ending of the project')
                ]),
            
        );
       
    }
}
