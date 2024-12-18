<?php
namespace report_dailyattendance\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class Get_List_Courses extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params' => new external_multiple_structure(
                new external_single_structure([
                    'customerid' => new external_value(PARAM_INT, 'customer id'),
                    'groupid' => new external_value(PARAM_INT, 'group id'),
                    'billid' => new external_value(PARAM_TEXT, 'Trainee billid'),
                    'startdate' => new external_value(PARAM_INT, 'Start date'),
                    'enddate' => new external_value(PARAM_INT, 'End date'),
                    'attendanceStatus'=> new external_single_structure([
                            'statusPresent' => new external_value(PARAM_TEXT, 'Either is present or not'),
                            'statusAbsent' => new external_value(PARAM_TEXT, 'Either is absent or not'),
                            'statusLate' => new external_value(PARAM_TEXT, 'Either is Late or not'),
                            'statusExcused' => new external_value(PARAM_TEXT, 'Either is Excused or not'),
                        ]),
                    'offset'=> new external_value(PARAM_INT, 'offset page'),
                    'order' => new external_value(PARAM_TEXT, 'either is ASC/DESC'),
                    'orderby' => new external_value(PARAM_TEXT, 'name of the ordered field by'),
                    'page'=>new external_value(PARAM_INT,'page number selected')
                ])
            )
        ]);
    }

    public static function execute($formValue){
        global $CFG, $DB;
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$formValue]);

        // now security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);
        
        $order=$request['params'][0]['order'];
        $orderby=$request['params'][0]['orderby'];
        $offset=$request['params'][0]['offset'];
        $page=$request['params'][0]['page'];
        $customerid=$request['params'][0]['customerid'];
        $group=$request['params'][0]['groupid'];
        $billid=$request['params'][0]['billid'];
        $startdate=$request['params'][0]['startdate'];
        $enddate=$request['params'][0]['enddate'];
        $attendance_status=new \stdClass();
        $attendance_status->present=($request['params'][0]['attendanceStatus']['statusPresent']==='true')?true:false;
        $attendance_status->absent=($request['params'][0]['attendanceStatus']['statusAbsent']==='true')?true:false;
        $attendance_status->late=($request['params'][0]['attendanceStatus']['statusLate']==='true')?true:false;
        $attendance_status->excused=($request['params'][0]['attendanceStatus']['statusExcused']==='true')?true:false;

        $transaction =$DB->start_delegated_transaction();
        $attendance_list= new \report_dailyattendance\AttendanceList($customerid,$group,$billid,$startdate,$enddate,$attendance_status,$offset,$order,$orderby);
        if ($page!=='' || $page!==null)
            $data=$attendance_list->getDailyAttendance($page);
        else
            $data=$attendance_list->getDailyAttendance($page);
        
            $result=[
            (object)[
                'attendance_list'=>$data[0],
                'pages'=>$data[1],
                'num_total_records'=>$attendance_list->getTotalDailyAttendance(),
                'order'=>$order,
                'orderbydate'=>$orderby==='DateAtt'?true:false,
                'orderbybillid'=>$orderby==='billid'?true:false,
                'orderbygroup'=>$orderby==='group'?true:false,
                'orderbyname'=>$orderby==='Prenom'?true:false,
                'orderbylastname'=>$orderby==='Nom'?true:false,
                'orderbydescription'=>$orderby==='description'?true:false,
                'orderbywbs'=>$orderby==='Nomducours'?true:false,
                ]
        ];
        
        return $result;
    }

    public static function execute_returns(){
        return new external_multiple_structure(
            new external_single_structure([
                'attendance_list'=>new external_multiple_structure(
                    new external_single_structure([
                        'grouptrainee'=>new external_value(PARAM_TEXT, 'selected group'),
                        'billid'=>new external_value(PARAM_TEXT, 'selected billid'),
                        'prenom'=>new external_value(PARAM_TEXT, 'selected name'),
                        'nom'=>new external_value(PARAM_TEXT, 'selected lastname'),
                        'nomducours'=>new external_value(PARAM_TEXT, 'selected wbs'),
                        'namecourse'=>new external_value(PARAM_TEXT, 'selected coursename'),
                        'dateatt'=>new external_value(PARAM_INT, 'selected date'),
                        'description'=>new external_value(PARAM_TEXT, 'Attendance status'),
                        'feedback'=>new external_value(PARAM_TEXT, 'selected feedback'),
                    ])
                    ),
                'pages'=>new external_multiple_structure(
                    new external_single_structure([
                        'page'=>new external_value(PARAM_INT, 'page number'),
                        'active'=>new external_value(PARAM_BOOL, 'Either the page is active or not')
                    ])
                    ),
                'num_total_records'=>new external_value(PARAM_INT, 'Total records'),
                'order'=>new external_value(PARAM_TEXT, 'Order records'),
                'orderbydate'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbybillid'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbygroup'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbyname'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbylastname'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbydescription'=>new external_value(PARAM_BOOL, 'Order records'),
                'orderbywbs'=>new external_value(PARAM_BOOL, 'Order records'),
            ])
        );
    }
}