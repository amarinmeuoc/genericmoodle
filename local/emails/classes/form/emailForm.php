<?php
namespace local_emails\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class emailForm extends \moodleform {
// Add elements to form.
public function definition() {
    global $PAGE, $DB,$USER;
   
    $PAGE->requires->js('/local/emails/js/manage_formJS.js', false);
    $PAGE->requires->css('/local/emails/css/styles.css');
    
    $mform = $this->_form; // Don't forget the underscore!
    $mform->disable_form_change_checker();

    // Project selection
    $projects=$DB->get_records('customer', [], 'id ASC', '*');
    
    $project_arr=[];
    foreach ($projects as $key => $project) {
        
        # code...
        $project_arr[$project->id]=$project->name;
    }

    $mform->addElement('select', 'project', get_string('selectproject', 'local_emails'),$project_arr);

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
    
    $firstvesselid=0;
    $keys=array_keys($vessel_arr);
    if (isset($keys[0]))
        $firstvesselid=$keys[0];
    
   
    $mform->addElement('select', 'vessel', get_string('selectvessel', 'local_emails'),$vessel_arr);

    //Se configura id de formulario
    $mform->_attributes['id']="emailform";

    $customer=$projects[$firstprojectid]->shortname;
    $selected_groupname=$vessel_arr[$firstvesselid];
    if ($selected_groupname==='PCO')
        $role='observer';
    else
        $role='student';

    
                                                          
    $trainee_query=$DB->get_records_sql('SELECT u.id,username,firstname, lastname,email,
        MAX(if (uf.shortname="billid",ui.data,"")) as billid,
        MAX(if (uf.shortname="group",ui.data,"")) as groupname,
        MAX(if (uf.shortname="customer",ui.data,"")) as customer,
        MAX(IF(uf.shortname = "role", ui.data, "")) AS role_name
        FROM mdl_user AS u
        INNER JOIN mdl_user_info_data AS ui ON ui.userid=u.id
        INNER JOIN mdl_user_info_field AS uf ON uf.id=ui.fieldid
        WHERE u.suspended=0
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
        'multiple' => true,                                                  
        'noselectionstring' => get_string('nouser', 'local_emails'),
        'placeholder'=>'Write a trainee billid or a name'                                                                
        );                                                                                                             
                                                                                                                            
        
    $mform->addElement('autocomplete', 'userlist', get_string('user', 'local_emails'), $trainee_array, $options);

    $mform->addRule('userlist', get_string('required'), 'required', null, 'client');

    $mform->addElement('advcheckbox','emailcheck',get_string('emailcheck','local_emails'),'Show a response email address.');
    
    // Email subject
    $mform->addElement('text', 'subject', get_string('subject', 'local_emails'));
    $mform->setType('subject', PARAM_TEXT);
    $mform->addRule('subject', get_string('required'), 'required', null, 'client');

    // Email message editor
    $mform->addElement('editor', 'message_editor', get_string('message', 'local_emails'), null, $this->get_editor_options());
    $mform->setType('message_editor', PARAM_RAW);
    $mform->addRule('message_editor', get_string('required'), 'required', null, 'client');

    // En la función definition() del formulario:
    $mform->addElement('filemanager', 'attachments', get_string('attachments', 'local_emails'), null, 
        [
            'subdirs' => 0, 
            'maxbytes' => 10485760, // 10MB
            'maxfiles' => 1,
            'accepted_types' => ['*'], // Permitir cualquier tipo de archivo
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL
        ]
    );

    $mform->setType('attachments', PARAM_INT); // ¡esto es crítico!

    // Form ID for JavaScript
    $mform->_attributes['id'] = "emailform";

    // Hidden fields
    $mform->addElement('hidden', 'token', $this->get_logistic_token());
    $mform->setType('token', PARAM_TEXT);

    $this->add_action_buttons(true, get_string('send', 'local_emails'));

}

    private function get_editor_options() {
        return [
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 0,
            'changeformat' => 1,
            'context' => \context_system::instance(),
            'noclean' => 1,
            'trusttext' => 1,
            'enable_filemanagement' => true
        ];
    }

    private function get_logistic_token() {
        global $DB;
        $token = $DB->get_record_sql("
            SELECT t.token
            FROM {external_tokens} t
            INNER JOIN {external_services} s ON s.id = t.externalserviceid
            INNER JOIN {user} u ON u.id = t.userid
            WHERE u.username = :username
            AND s.shortname = :servicename
            LIMIT 1",
        [
            'username' => 'logisticwebservice',
            'servicename' => 'email_navantiaservices'
        ]);
        return $token ? $token->token : '';
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if (empty($data['subject'])) {
            $errors['subject'] = get_string('erroremptysubject', 'local_emails');
        }
        
        if (empty($data['message_editor']['text'])) {
            $errors['message_editor'] = get_string('erroremptymessage', 'local_emails');
        }
        
        if (empty($data['userlist'])) {
            $errors['userlist'] = get_string('errorselectusers', 'local_emails');
        }
        
        return $errors;
    }

}