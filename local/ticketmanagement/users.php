<?php
require_once('../../config.php');

require_login();

global $USER;
$PAGE->set_url(new moodle_url('/local/ticketmanagement/index.php'));
$context=context_system::instance();
$PAGE->set_context($context);
//$PAGE->set_pagelayout('standard');
//Si es estudiante o cualquier otro rol cargamos un formulario de creación de ticket
//Si es logistico cargamos un formulario de creación de ticket y de búsqueda de tickets
$role=$USER->profile['role'];

//Si tiene permisos y pertenece al grupo de los logisticos
if (preg_match('/(logistic|manager)/i',$role) && has_capability('local/ticketmanagement:edituserprofile',$context)){
    //Muestra formulario para logistic
    $mform=new \local_ticketmanagement\form\user_form();
    $PAGE->requires->js_call_amd('local_ticketmanagement/usermanagement/init', 'loadTemplate');
}  else {
    echo $OUTPUT->header();       
    $message="<h1><strong>Error 403.</strong> You don't have permission to access to this content.</h1> <p>Contact with the admin for more information.</p>";
    echo html_writer::div($message);
    echo html_writer::div('<a class="btn btn-primary" href="'.$CFG->wwwroot.'">Go back</a>');       
    echo $OUTPUT->footer();   
    return;
}
$PAGE->requires->css(new moodle_url('/local/ticketmanagement/css/styles.scss'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageuser', 'local_ticketmanagement'));
$mform->display();

$data = [ 
   /* 
    'listadoTickets'=> [
        [
            'ticketnumber'=>'#7850002145',
            'username'=>'Antonio Lopez',
            'familyissue'=>'Yes',
            'date'=>'1729007003',
            'state'=>'open',
            'description'=>"hola mundo",
            'priority'=>'Medium',
            'assigned'=>'Kautar'
        ],
        [
            'ticketnumber'=>'#6850003145',
            'username'=>'Mohammed Ali',
            'familyissue'=>'Yes',
            'date'=>'1729007003',
            'state'=>'open',
            'description'=>"hola mundo",
            'priority'=>'High',
            'assigned'=>'Cristina'
        ]
        
        ],
    'pages'=>10,
                'num_total_records'=>50,
                'num_records'=>10,
                'order'=>'ASC',
                'orderbyticket'=>$orderby==='id'?true:false,
                'orderbyuser'=>$orderby==='userid'?true:false,
                'orderbypriority'=>$orderby==='priority'?true:false,
                'orderbyassigned'=>$orderby==='assigned'?true:false,
                
                'hidecontrolonsinglepage'=>true,
                'activepagenumber'=>$page,
                'barsize'=>'small',
                'previous'=>[
                    (object)['page'=>($page-1<1)?$page:$page-1,
                    'url'=>'']
                ],
                'next'=>[
                    (object)['page'=>($page+1>10)?$page:$page+1,
                    'url'=>'']
                ],
                'first'=>[
                    (object)['page'=>1,
                    'url'=>'']
                ],
                'last'=>[
                    (object)['page'=>10,
                    'url'=>'']
                ],
                */
];
if (preg_match('/(logistic|manager)/i',$role)){
    $render=$OUTPUT->render_from_template('local_ticketmanagement/users/users', $data);
} 
echo $render;


echo $OUTPUT->footer();
?>