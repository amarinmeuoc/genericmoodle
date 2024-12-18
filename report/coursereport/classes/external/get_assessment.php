<?php
namespace report_coursereport\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;



class get_assessment extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'customerid'=>new external_value(PARAM_INT,'Customer Id'),
                    'groupid'=>new external_value(PARAM_INT,'group id'),
                    'billid'=>new external_value(PARAM_TEXT,'Billid'),
                    'wbs'=>new external_value(PARAM_TEXT,'WBS'),
                    'startdate'=>new external_value(PARAM_INT,'Course startdate'),
                    'offset'=>new external_value(PARAM_INT,'Total registres'),
                    'order'=>new external_value(PARAM_TEXT,'ASC / DESC'),
                    'orderby'=>new external_value(PARAM_TEXT,'The column to order by'),
                    'queryType'=>new external_value(PARAM_INT,'If 1 completed courses, if 0 on going courses'),
                    'page'=>new external_value(PARAM_INT,'Page number'),
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
        
        $url=new \moodle_url('/report/coursereport/index.php');
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);

         $customerid=$request['params'][0]['customerid'];
         $groupid=$request['params'][0]['groupid'];
         $billid=$request['params'][0]['billid'];
         $wbs=$request['params'][0]['wbs'];
         $startdate=$request['params'][0]['startdate'];
         $offset=$request['params'][0]['offset'];
         $order=filter_var($request['params'][0]['order'],FILTER_VALIDATE_BOOLEAN);
         $orderby=$request['params'][0]['orderby'];
         $status=$request['params'][0]['queryType'];
         $page=$request['params'][0]['page'];
         
        

         $assessment_list=new \report_coursereport\AssessmentList($customerid,$groupid,$billid,$wbs,$startdate, $offset, $order, $orderby,$status);
         
        $data=$assessment_list->getAssessment($page);
         
        
         $result=[
            (object)[
                'assessment_list'=>$data[0],
                'pages'=>$data[1],
                'num_total_records'=>$assessment_list->getTotalAssessment(),
                'num_records'=>count($data[0]),
                'order'=>$order,
                'orderbystartdate'=>$orderby==='startdate'?true:false,
                'orderbyenddate'=>$orderby==='enddate'?true:false,
                'orderbybillid'=>$orderby==='billid'?true:false,
                'orderbycustomer'=>$orderby==='customercode'?true:false,
                'orderbygroup'=>$orderby==='groupname'?true:false,
                'orderbyname'=>$orderby==='firstname'?true:false,
                'orderbylastname'=>$orderby==='lastname'?true:false,
                'orderbywbs'=>$orderby==='shortname'?true:false,
                'orderbycoursename'=>$orderby==='fullname'?true:false,
                'orderbyatt'=>$orderby==='attendance'?true:false,
                'orderbyass'=>$orderby==='assessment'?true:false,
                'hidecontrolonsinglepage'=>true,
                'activepagenumber'=>$page,
                'barsize'=>'small',
                'previous'=>[
                    (object)['page'=>($page-1<1)?$page:$page-1,
                    'url'=>'']
                ],
                'next'=>[
                    (object)['page'=>($page+1>count($data[1]))?$page:$page+1,
                    'url'=>'']
                ],
                'first'=>[
                    (object)['page'=>1,
                    'url'=>'']
                ],
                'last'=>[
                    (object)['page'=>count($data[1]),
                    'url'=>'']
                ],
            ]
        ];
        
        return $result;     
        
        
    }


    public static function execute_returns(){
        return new external_multiple_structure(
            new external_single_structure([
                'assessment_list'=>new external_multiple_structure(
                    new external_single_structure([
                        'customerid'=>new external_value(PARAM_INT, 'customerid'),
                        'groupid'=>new external_value(PARAM_INT, 'groupid'),
                        'customercode'=>new external_value(PARAM_TEXT,'Customer shortcode'),
                        'groupname'=>new external_value(PARAM_TEXT, 'selected group'),
                        'billid'=>new external_value(PARAM_TEXT, 'selected billid'),
                        'firstname'=>new external_value(PARAM_TEXT, 'selected name'),
                        'lastname'=>new external_value(PARAM_TEXT, 'selected lastname'),
                        'shortname'=>new external_value(PARAM_TEXT, 'selected wbs'),
                        'fullname'=>new external_value(PARAM_TEXT, 'selected coursename'),
                        'startdate'=>new external_value(PARAM_INT, 'start date'),
                        'enddate'=>new external_value(PARAM_INT, 'end date'),
                        'email'=>new external_value(PARAM_TEXT, 'email'),
                        'attendance'=>new external_value(PARAM_TEXT, 'Attendance score'),
                        'assessment'=>new external_value(PARAM_TEXT, 'Assessment score'),
                    ]) 
                    ),
                'pages'=>new external_multiple_structure(
                    new external_single_structure([
                        'page'=>new external_value(PARAM_INT, 'page number'),
                        'active'=>new external_value(PARAM_BOOL, 'Either the page is active or not')
                    ])
                    ),
                'num_total_records'=>new external_value(PARAM_INT, 'Total records'),
                'num_records'=>new external_value(PARAM_INT, 'num records'),
                'order'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbystartdate'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbyenddate'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbybillid'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbycustomer'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbygroup'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbyname'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbylastname'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbywbs'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbycoursename'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbyatt'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbyass'=>new external_value(PARAM_BOOL, 'Order records'),
                'hidecontrolonsinglepage'=>new external_value(PARAM_BOOL, 'Order records'),
                'activepagenumber'=>new external_value(PARAM_INT, 'active page number'),
                'barsize'=>new external_value(PARAM_TEXT, 'small / large navbar size'),
                'previous'=>new external_multiple_structure(
                    new external_single_structure([
                        'page'=>new external_value(PARAM_INT, 'page number'),
                        'url'=>new external_value(PARAM_TEXT, 'url')
                    ])
                    ),
                'next'=>new external_multiple_structure(
                    new external_single_structure([
                        'page'=>new external_value(PARAM_INT, 'page number'),
                        'url'=>new external_value(PARAM_TEXT, 'url')
                    ])
                    ),
                'first'=>new external_multiple_structure(
                    new external_single_structure([
                        'page'=>new external_value(PARAM_INT, 'page number'),
                        'url'=>new external_value(PARAM_TEXT, 'url')
                    ])
                    ),
                'last'=>new external_multiple_structure(
                    new external_single_structure([
                        'page'=>new external_value(PARAM_INT, 'page number'),
                        'url'=>new external_value(PARAM_TEXT, 'url')
                    ])
                    ),
            ])
        );
    }

}