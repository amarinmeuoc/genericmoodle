<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class uploadFamilyform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        
        //Se añaden javascript y CSS
        //Se añade javascript
        $PAGE->requires->js(new \moodle_url('/local/ticketmanagement/js/family_formJS.js'), false);
        $PAGE->requires->css(new \moodle_url('/local/ticketmanagement/css/styles.scss'));
        
        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        //Se configura id de formulario
        $mform->_attributes['id']="familyformid";

        $projects=$DB->get_records('customer', [], 'id ASC', '*');
    
    $project_arr=[];
    foreach ($projects as $key => $project) {
        # code...
        $project_arr[$key]=$project->name;
    }

    $mform->addElement('select', 'project', get_string('selectproject', 'local_ticketmanagement'),$project_arr);

    $keys=array_keys($project_arr);
    if (isset($keys[0]))
        $firstprojectid=$keys[0];

    
    $vessel=$DB->get_records('grouptrainee', ['customer'=>$firstprojectid],'id ASC','id,name');

    $vessel_arr=[];
    foreach ($vessel as $key => $value) {
        # code...
        $vessel_arr[$key]=$value->name;
    }
    $keys=array_keys($vessel_arr);
    if (isset($keys[0]))
        $firstvesselid=$keys[0];
    $mform->addElement('select', 'vessel', get_string('selectvessel', 'local_ticketmanagement'),$vessel_arr);

    //Se configura id de formulario
    $mform->_attributes['id']="manageticket";

    $customer=$projects[$firstprojectid]->shortname;
    $selected_groupname=$vessel_arr[$firstvesselid];
    $role='student';

    
                                                          
    $trainee_query=$DB->get_records_sql('SELECT u.id,username,firstname, lastname,email,
        MAX(if (uf.shortname="billid",ui.data,"")) as billid,
        MAX(if (uf.shortname="group",ui.data,"")) as groupname,
        MAX(if (uf.shortname="customer",ui.data,"")) as customer,
        MAX(IF(uf.shortname = "role", ui.data, "")) AS role_name
        FROM mdl_user AS u
        INNER JOIN mdl_user_info_data AS ui ON ui.userid=u.id
        INNER JOIN mdl_user_info_field AS uf ON uf.id=ui.fieldid
        GROUP by username,firstname, lastname
        HAVING role_name=:role_name AND customer=:customer AND groupname=:groupname',['role_name'=>$role,'customer'=>$customer, 'groupname'=>$selected_groupname]);
        $trainee_list=array_values($trainee_query);

        $trainee_array=Array();
        //$pattern='/(OF-\d+)|(EN-\d+)|(^\d+\s[A-Z][A-Z]$)|(RSNFTT-\d+)/i';
        $pattern='//i';
        foreach($trainee_list as $elem){
        if (preg_match($pattern, $elem->billid)==1)
        $trainee_array[$elem->id]=$elem->groupname."_".$elem->billid." ".$elem->firstname.", ".$elem->lastname;
        }

        $options = array(                                                                                                           
        'multiple' => false,                                                  
        'noselectionstring' => get_string('nouser', 'local_ticketmanagement'),
        'placeholder'=>'Write a trainee billid or a name'                                                                
        );                                                                                                             
                                                                                                                            
        
    $mform->addElement('autocomplete', 'userlist', get_string('user', 'local_ticketmanagement'), $trainee_array, $options);

    

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   
        
        $mform->addElement('html',  '<div id="error-message" class="alert alert-danger" role="alert" style="display:none">');
        $mform->addElement('html',  '</div>');
        $mform->addElement('button', 'bosubmit', get_string('select', 'local_ticketmanagement'));
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}