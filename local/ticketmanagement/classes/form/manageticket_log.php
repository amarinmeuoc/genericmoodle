<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class manageticket_log extends \moodleform {
// Add elements to form.
public function definition() {
    global $PAGE, $DB,$USER;
    
    //Se añaden javascript y CSS
    //Se añade javascript
    $PAGE->requires->js('/local/ticketmanagement/js/manage_ticket_formJS.js', false);
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

    
    $vessel=$DB->get_records('grouptrainee', ['customer'=>$firstprojectid],'id ASC','id,name');

    $vessel_arr=[];
    foreach ($vessel as $key => $value) {
        # code...
        $vessel_arr[$value->id]=$value->name;
    }

    $vessel_arr=[0=>'PCO']+$vessel_arr;
    $keys=array_keys($vessel_arr);
    if (isset($keys[0]))
        $firstvesselid=$keys[0];


    $mform->addElement('select', 'vessel', get_string('selectvessel', 'local_ticketmanagement'),$vessel_arr);

    //Se configura id de formulario
    $mform->_attributes['id']="manageticket";

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

    

    $category=$DB->get_records('ticket_category',[],'id ASC','id,category');

    $category_arr=[];
    foreach ($category as $key => $cat) {
        # code...
        $category_arr[$key]=$cat->category;
    }
    
    $mform->addElement('select', 'category', get_string('selectCategory', 'local_ticketmanagement'),$category_arr);

    
    $keys=array_keys($category_arr);
    
    if (isset($keys[0]))
        $firstcategoryid=$keys[0];
    else
        $firstcategoryid=0;

        
    $subcategory=$DB->get_records('ticket_subcategory', ['categoryid'=>$firstcategoryid],'id ASC','id,subcategory');

    
    $subcategory_arr=[];
    foreach ($subcategory as $key => $value) {
        # code...
        $subcategory_arr[$key]=$value->subcategory;
    }
    

    $mform->addElement('select', 'subcategory', get_string('selectsubcategory', 'local_ticketmanagement'),$subcategory_arr);

   

    $mform->addElement('select', 'familyissue',  get_string('familyissue', 'local_ticketmanagement'),  ['no'=>'No', 'yes'=>'Yes']);

    reset($trainee_array);

    $selUserid=key($trainee_array);

    $family=$DB->get_records('family',['userid'=>$selUserid],'id ASC','*');

    $family_arr=[];
    foreach ($family as $key => $value) {
        # code...
        $family_arr[$key]="$value->name, $value->lastname";
    }

    // Add the second select box (initially hidden)
    $mform->addElement('select', 'familiar', 'Select an option:', $family_arr);

    // Use hideIf() to hide the second select box if the first select box is not "Yes"
    $mform->hideIf('familiar', 'familyissue', 'eq', 'no');

    
    $mform->addElement('editor', 'description', get_string('editortext', 'local_ticketmanagement'));
    
    $mform->setType('description', PARAM_RAW);
    
/*
    $mform->addElement(
        'filemanager',
        'attachments',
        get_string('attachment', 'local_ticketmanagement'),
        null,
        [
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'areamaxbytes' => 10485760,
            'maxfiles' => 50,
            'accepted_types' => ['document'],
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
        ]
    );
*/
    
    
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

    $orderby='dateticket';

    $mform->addElement('hidden', 'orderby', $orderby);
    $mform->setType('orderby',PARAM_TEXT);
    
    $page=1;
    $mform->addElement('hidden', 'page', $page);
    $mform->setType('page',PARAM_INT);

    $mform->addElement('hidden', 'role', 'controller');
    $mform->setType('role',PARAM_TEXT);

    $mform->addElement('hidden',  'gestorid',  $USER->id);
    $mform->setType('gestorid',PARAM_INT);
    
    $mform->addElement('button', 'bocreate', get_string('create', 'local_ticketmanagement'));
    
    
}


// Custom validation should be added here.
function validation($data, $files) {
    return [];
}
}