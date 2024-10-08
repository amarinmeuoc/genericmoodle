<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class manageticket_log extends \moodleform {
// Add elements to form.
public function definition() {
    global $PAGE, $DB;
    
    //Se añaden javascript y CSS
    //Se añade javascript
    $PAGE->requires->js('/local/ticketmanagement/js/user_form.js', false);
    $PAGE->requires->css('/local/ticketmanagement/css/styles.scss');
    
    $mform = $this->_form; // Don't forget the underscore!
    $mform->disable_form_change_checker();
    //Se configura id de formulario
    $mform->_attributes['id']="manageticket";
                                                          
    $userlist = array('33'=>'C1_EN-111_Mohammed, Almaki','34'=>'C1_EN-112_Hamas, Aleui','44'=>'C2_EN-311_Alowi, Mustafa');                                                                                                       
                                                                                                                            
    $options = array(                                                                                                           
        'multiple' => false,                                                  
        'noselectionstring' => get_string('allareas', 'search'),                                                                
    );         
    $mform->addElement('autocomplete', 'areaids', get_string('searcharea', 'search'), $userlist, $options);

    $category=[
        '1'=>'Administration',
        '2'=>'Office',
        '3'=>'Health insurance',
        '4'=>'Travel'
    ];
    
    $mform->addElement('select', 'category', get_string('selectCategory', 'local_ticketmanagement'),$category);

    $subcategory=[
        '1'=>'Registration',
        '2'=>'Payment of a fine'
    ];

    $mform->addElement('select', 'subcategory', get_string('selectsubcategory', 'local_ticketmanagement'),$subcategory);

    $mform->addElement('select', 'wayofcontact',  get_string('wayofcontact', 'local_ticketmanagement'),  ['0'=>'Mobile', '1'=>'Whatsapp','3'=>'Email']);

    $mform->addElement('select', 'familyissue',  get_string('familyissue', 'local_ticketmanagement'),  ['0'=>'No', '1'=>'Yes']);

    // Add the second select box (initially hidden)
    $mform->addElement('select', 'second_select', 'Select an option:', ['0' => 'Mohammed', '1' => 'Noujou']);

    // Use hideIf() to hide the second select box if the first select box is not "Yes"
    $mform->hideIf('second_select', 'familyissue', 'eq', '0');

    
    $mform->addElement('editor', 'fieldname', get_string('editortext', 'local_ticketmanagement'));
    $mform->setType('fieldname', PARAM_RAW);

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
    
    //Se obtiene el token del usuario y se guarda en un campo oculto
    $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                        INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                        WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
    $token=$token->token;

    $mform->addElement('hidden', 'token', $token);
    $mform->setType('token',PARAM_TEXT);   
    
    $mform->addElement('button', 'bocreate', get_string('create', 'local_ticketmanagement'));
    
}

// Custom validation should be added here.
function validation($data, $files) {
    return [];
}
}