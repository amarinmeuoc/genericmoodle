<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Config changes report
 *
 * @package    report_dailyattendance
 * @subpackage traineereport
 * @copyright  2024 Alberto Marín
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require(__DIR__.'/../../config.php');
global $PAGE, $OUTPUT,$USER,$DB,$CFG;

require_login();

//$PAGE->requires->js('/report/dailyattendance/js/config.js',true);
//$PAGE->requires->js('/report/informes/js/FileSaver.min.js',true);
//$PAGE->requires->js('/report/informes/js/blob-util.min.js',true);
$PAGE->requires->js_call_amd('report_dailyattendance/loadModules', 'init');
//$PAGE->requires->js_call_amd('report_informes/index', 'selectAssignment');

$attendance_status=new stdClass();
$attendance_status->present=true;
$attendance_status->absent=false;
$attendance_status->late=false;
$attendance_status->excused=false;


$d=date('Y-m-d',time());

$selected_date_start=strtotime($d); //Time in unixtime according to database format
$selected_date_end=strtotime($d);

use report_dailyattendance;

$context=\context_course::instance(1);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title('Daily Attendance report');
$PAGE->requires->css(new \moodle_url('/report/dailyattendance/css/styles.css'));
$url=new moodle_url("/report/dailyattendance");
$PAGE->set_url($url);

if (!has_capability('report/dailyattendance:view',$context)){   
    echo $OUTPUT->header();       
    $message="<h1>Error: Access forbidden!!.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}




$customer_shortname=isset($USER->profile['customer'])?$USER->profile['customer']:'';
$customer=$DB->get_record('customer',['shortname'=>$customer_shortname],'id');
$customerid=isset($customer->id)?$customer->id:'';
$showCustomerSelect=false;

if ($customer_shortname===''){
    //No es cliente y por tanto se muestra un desplegable de clientes donde el $customerid seleccionado por defecto es la primera opción del desplegable cliente
    $customer_list=get_customer_list();
    $group_list=get_group_list($customer_list->first_customer_selected);
    $showCustomerSelect=true; //Show the customer selection control
} else {
    //Es cliente y por tanto no se muestra el desplegable cliente. El customerid no se cambia. La lista de groups es asociada al customerid
    $customer_list=get_customer_list($customer_shortname);
    $group_list=get_group_list($customerid);
    $showCustomerSelect=false;
}

$group_selected=isset($group_list[0]->id)?$group_list[0]->id:'';
$billid=null;
$order=true;
$orderby='DateAtt';
$page=1;
$offset=50;

$attendance = new \report_dailyattendance\AttendanceList($customerid,$group_selected,$billid,$selected_date_start,$selected_date_end,$attendance_status,$offset,$order,$orderby);
$attendance_list=$attendance->getDailyAttendance()[0];
$pages=$attendance->getDailyAttendance()[1];

$userSessionId=\core\session\manager::get_login_token();

$token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
   

echo $OUTPUT->header();

$data=[
    'token'=>($token)?$token->token:'',
    'orderbydate'=>$orderby==='DateAtt'?true:false,
    'orderbygroup'=>$orderby==='group'?true:false,
    'orderbybillid'=>$orderby==='billid'?true:false,
    'orderbynom'=>$orderby==='Prenom'?true:false,
    'orderbydescription'=>$orderby==='Description'?true:false,
    'showCustomerSelect'=>$showCustomerSelect,
    'customerid'=>$customerid,
    'customer_list'=>$customer_list,
    'group_list'=>$group_list,
    'attendance_list'=>$attendance_list,
    'num_records'=>$attendance->get_numRecords(),
    'num_total_records'=>$attendance->getTotalDailyAttendance(),
    'selected_date'=>$d,
    'attendance_status'=>$attendance_status,
    'order'=>$order,
    'orderby'=>$orderby,
    'offset'=>$offset,
    'pages'=>$pages,
    'hidecontrolonsinglepage'=> true,
    'activepagenumber'=>$page,
    'barsize'=>'small',
    'previous'=>[
            'page'=>($page-1<1)?$page:$page-1,
            'url'=>$url
        ],
    'next'=>[
        'page'=>($page+1>$totalPages)?$page:$page+1,
        'url'=>$url
    ],
    'first'=>[
        'page'=>1,
        'url'=>$url
    ],
    'last'=>[
        'page'=>$totalPages,
        'url'=>$url
    ],
   
];


echo $OUTPUT->render_from_template('report_dailyattendance/content', $data);

echo $OUTPUT->footer();


function get_customer_list($customerid=null){
    global $DB;

    $result=new stdClass();
    if ($customerid==null)
        $customer_list=$DB->get_records('customer', [], 'shortname ASC', 'id, shortname');
    else
        $customer_list=$DB->get_records('customer', ['shortname'=>$customerid], 'shortname ASC', 'id, shortname');
    $customer_list=array_values($customer_list);
    $first_customer_selected=array_key_first($customer_list);
    $result->customer_list=$customer_list;
    $result->first_customer_selected=$customer_list[$first_customer_selected]->id;
    return $result;
}

function get_group_list($customerid){
    global $DB;
    $group_list=$DB->get_records('grouptrainee',['customer'=>$customerid], 'name ASC', 'id, name');
    $group_list=array_values($group_list);
    return $group_list;
}




