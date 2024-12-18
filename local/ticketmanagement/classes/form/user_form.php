<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class user_form extends \moodleform {
// Add elements to form.
public function definition() {
    global $PAGE, $DB,$USER;
    
    //Se añaden javascript y CSS
    //Se añade javascript
    $PAGE->requires->js('/local/ticketmanagement/js/users_formJS.js', false);
    $PAGE->requires->css('/local/ticketmanagement/css/styles.scss');
    
    $mform = $this->_form; // Don't forget the underscore!
    $mform->disable_form_change_checker();

    $projects=$DB->get_records('customer', [], 'id ASC', '*');
    
    $project_arr=[];
    foreach ($projects as $key => $project) {
        
        # code...
        $project_arr[$project->id]=$project->name;
    }

    $mform->addElement('select', 'project', get_string('selectproject', 'local_ticketmanagement'),$project_arr);

    $keys=array_keys($project_arr);
    if (isset($keys[0]))
        $firstprojectid=$keys[0];

    //devuelve un array de objetos
    $vessel=$DB->get_records('grouptrainee', ['customer'=>$firstprojectid],'id ASC','id,name');

    $vessel_arr=[];
    foreach ($vessel as $key => $value) {

        # code...
        $vessel_arr[$value->id]=$value->name;
    }
    
    
   
    $mform->addElement('select', 'vessel', get_string('selectvessel', 'local_ticketmanagement'),$vessel_arr);

    //Se configura id de formulario
    $mform->_attributes['id']="manageticket";

    //Se obtiene el token del usuario y se guarda en un campo oculto
    $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                        INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                        WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
    $token=$token->token;

    $mform->addElement('hidden', 'token', $token);
    $mform->setType('token',PARAM_TEXT);   

    $order=1;

    $mform->addElement('hidden', 'order', $order);
    $mform->setType('order',PARAM_INT);   

    $orderby='billid';

    $mform->addElement('hidden', 'orderby', $orderby);
    $mform->setType('orderby',PARAM_TEXT);
    
    $page=1;
    $mform->addElement('hidden', 'page', $page);
    $mform->setType('page',PARAM_INT);

    


    
    $mform->addElement('button', 'boshow', get_string('showusers', 'local_ticketmanagement'));
    
    
}


// Custom validation should be added here.
function validation($data, $files) {
    return [];
}
}