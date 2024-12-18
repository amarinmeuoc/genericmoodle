<?php

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_ticketmanagement_uploadfamily');
$context=context_system::instance();
 if (!has_capability('moodle/site:config',$context)) {
    echo $OUTPUT->header();
    $message=get_string('error','local_ticketmanagement');
    \core\notification::error($message);
    echo $OUTPUT->footer();
    return;
 }

 $mform=new \local_ticketmanagement\form\uploadFamilyform();

 echo $OUTPUT->header();

 echo $OUTPUT->heading(get_string('uploadFamily', 'local_ticketmanagement'));

 $mform->display();
 
 $data = [ 
    
    'listadoFamily'=> [
        [
            'id'=>'1',
            'relationship'=>'Wife',
            'name'=>'Manolo',
            'lastname'=>'Contreras',
        ],
        [
            'id'=>'2',
            'relationship'=>'Son',
            'name'=>'Haya',
            'lastname'=>'Maralla',
        ],
        
    ],
    'user'=>'Mohamed Contreras'
    ];

    $render=$OUTPUT->render_from_template('local_ticketmanagement/family', $data);

echo $render;



 echo $OUTPUT->footer();