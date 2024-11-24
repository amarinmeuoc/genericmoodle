<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class manageticket_st extends \moodleform {
// Add elements to form.
public function definition() {
    global $PAGE, $DB, $USER;
    
    //Se añaden javascript y CSS
    //Se añade javascript
    $PAGE->requires->js('/local/ticketmanagement/js/user_form.js', false);
    $PAGE->requires->css('/local/ticketmanagement/css/styles.scss');
    
    $mform = $this->_form; // Don't forget the underscore!
    $mform->disable_form_change_checker();

    $mform->addElement('hidden',  'user',  $USER->id);  
    $mform->settype('user',PARAM_INT)                                                      ;
                                                                                                                            
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

    

    $selUserid=$USER->id;

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

    $mform->addElement('hidden', 'role', 'student');
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