<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class load_itp extends \core_external\external_api {
/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'email'=>new external_value(PARAM_TEXT,'email user'),
                    'orderby'=>new external_value(PARAM_TEXT,'order by startdate, enddate, course, location, provider'),
                    'order'=>new external_value(PARAM_BOOL,'order asc or desc'),
                    'compacted'=>new external_value(PARAM_TEXT,'yes or no'),

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
        global $DB, $USER;
        
        // Validate parameters
        $request=self::validate_parameters(self::execute_parameters(), ['params'=>$params]);
        $params=$request['params'][0];
        
         // now security checks
         $context = \context_system::instance();
         self::validate_context($context);
         require_capability('webservice/rest:use', $context);

         // Extract parameters
        $email = $params['email'];
        $orderby = $params['orderby'];
        $order = $params['order'];
        $compacted = $params['compacted'];

        //Controlar que la salida no varia aunque el email sea nulo
        if (!$email) {
            $email = $USER->email;
        }
        
        $user = $DB->get_record('user', ['email' => $email]);

        
        $lastupdate = $DB->get_field('itptrainee', 'lastupdate', ['email' => $email]);
        
        if (!$lastupdate) {
            $lastupdate = time();
        }

        profile_load_custom_fields($user);
        $userdata = (object)[
            'firstname'=>$user->firstname,
            'lastname'=>$user->lastname,
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'billid' => $user->profile['billid'],
            'lastupdate' => $lastupdate
        ];

        if (!$userdata) {
            throw new invalid_parameter_exception('User not found');
        }

        $itp= new \block_itp\schedule($email);
        
        if ($order){ //Si es ascendente
            $itp->orderItp($orderby,'ASC');
        }else{
            $itp->orderItp($orderby,'DESC');
        }
        
        
        if ($compacted==='yes'){
            $itptable=$itp->getItp($compacted);
        } else {
            $itptable=$itp->getItp();
        }
        
        
        $itppopulate=[];
        foreach ($itptable as $key => $row) {
            //Populate $itptable
            $itppopulate[$key]['coursename']=$row->coursename;
            $itppopulate[$key]['shortcode']=$row->shortcode;
            $itppopulate[$key]['duration']=$row->duration;
            $itppopulate[$key]['startdate']=$row->startdate;
            $itppopulate[$key]['enddate']=$row->enddate;
            $itppopulate[$key]['location']=$row->location;
            $itppopulate[$key]['classroom']=$row->classroom;
            $itppopulate[$key]['schedule']=$row->schedule;
            $itppopulate[$key]['attendance']=is_null($row->attendance)?'' : $row->attendance;
            $itppopulate[$key]['assessment']=is_null($row->assessment)?'' : $row->assessment;
            $itppopulate[$key]['visible']=$row->visible;
            $itppopulate[$key]['courseUrl']=$row->courseUrl->__toString();
            $itppopulate[$key]['courseid']=$row->courseid;
            
        }

        $maxDate=array_reduce($itppopulate,function($acc,$currentValue){
            return ($acc>$currentValue['enddate'])?$acc:$currentValue['enddate'];
        },$itppopulate[0]['enddate']);

        
    // Build the response structure
    $response = [
        'personaldata' => $userdata,
        'itp' => $itppopulate,
        'itpState' => [
            'order' => $order,
            'orderby' => $orderby,
            'compacted' => $compacted,
            'orderbystartdate'=>$orderby==='startdate'?true:false,
            'orderbyenddate'=>$orderby==='enddate'?true:false,
            'orderbyatt'=>$orderby==='att'?true:false,
            'orderbyass'=>$orderby==='ass'?true:false,
            'orderbycourse'=>$orderby==='course'?true:false,
            'orderbyduration'=>$orderby==='duration'?true:false,
            'ifcertificate'=>($maxDate<time() && $maxDate!==NULL)?true:false
        ]
    ];

    // Populate itp


   

    return $response;
    }


    public static function execute_returns() {
        //Must show the WBS, Coursename, Start, End, Num Trainees, Assignation, Location, Provider, Download CSV, Send Email
        return new external_single_structure([
            'personaldata' => new external_single_structure([
                'firstname' => new external_value(PARAM_TEXT, 'Firstname of the user'),
                'lastname' => new external_value(PARAM_TEXT, 'Lastname of the user'),
                'name' => new external_value(PARAM_TEXT, 'Full name of the user'),
                'billid' => new external_value(PARAM_TEXT, 'Bill ID'),
                'email' => new external_value(PARAM_TEXT, 'Email of the user'),
                'lastupdate' => new external_value(PARAM_INT, 'Last update timestamp')
            ]),
            'itp' => new external_multiple_structure(
                new external_single_structure([
                    'coursename' => new external_value(PARAM_TEXT, 'Course name'),
                    'shortcode' => new external_value(PARAM_TEXT, 'WBS course'),
                    'duration' => new external_value(PARAM_INT, 'Course duration'),
                    'startdate' => new external_value(PARAM_INT, 'Course start date'),
                    'enddate' => new external_value(PARAM_INT, 'Course end date'),
                    'location' => new external_value(PARAM_TEXT, 'Course location'),
                    'classroom' => new external_value(PARAM_TEXT, 'Classroom'),
                    'schedule' => new external_value(PARAM_TEXT, 'Course schedule'),
                    'attendance' => new external_value(PARAM_FLOAT, 'Attendance'),
                    'assessment' => new external_value(PARAM_FLOAT, 'Assessment'),
                    'visible' => new external_value(PARAM_INT, 'hidden course'),
                    'courseUrl' => new external_value(PARAM_TEXT, 'Course URL'),
                    'courseid' => new external_value(PARAM_INT, 'Course ID')
                ])
            ),
            'itpState' => new external_single_structure([
                'order' => new external_value(PARAM_BOOL, 'Order'),
                'orderby' => new external_value(PARAM_TEXT, 'Order by'),
                'compacted' => new external_value(PARAM_TEXT, 'Compacted'),
                'orderbystartdate' => new external_value(PARAM_BOOL, 'Order by start date'),
                'orderbyenddate' => new external_value(PARAM_BOOL, 'Order by end date'),
                'orderbyatt' => new external_value(PARAM_BOOL, 'Order by attendance'),
                'orderbyass' => new external_value(PARAM_BOOL, 'Order by assessment'),
                'orderbycourse' => new external_value(PARAM_BOOL, 'Order by course'),
                'orderbyduration' => new external_value(PARAM_BOOL, 'Order by duration'),
                'ifcertificate' => new external_value(PARAM_BOOL, 'If certificate is available')
            ])
        ]);
    }
}