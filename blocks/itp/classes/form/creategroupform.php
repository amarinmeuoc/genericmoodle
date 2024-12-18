<?php
namespace block_itp\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class creategroupform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        //Se añade javascript
        $PAGE->requires->js('/blocks/itp/js/group_formJS.js', false);
        
        //Se crean los campos
        $mform->addElement('text', 'tegroup', get_string('tegroup', 'block_itp'), '');
        $mform->addRule('tegroup','Group code is required','required');
        $mform->setType('tegroup',PARAM_TEXT);

        $mform->addElement('advcheckbox', 'hiddengroup', get_string('hiddengroup', 'block_itp'), 'Select this checkbox to set an administrative group', [], array(0, 1));

        $list_of_customers=$DB->get_records('customer',null,'','id,name');
        foreach ($list_of_customers as $key => $customer) {
            $list_of_customers[$key]=$customer->name;
        }
        
        $select=$mform->addElement('select', 'tecustomer', get_string('tecustomer', 'block_itp'), $list_of_customers, '');

        
         
        $selectgroup=$mform->addElement('select','tegrouplist',get_string('tegrouplist', 'block_itp'), [], '');
        $selectgroup->setMultiple(true);

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        
        if ($token)
            $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   
        
        $mform->addElement('html',  '<div id="button_container" class="flex row m-3">');
        $mform->addElement('button', 'bosubmit', get_string('submit', 'block_itp'));
        $mform->addElement('button', 'boedit', get_string('update', 'block_itp'));
        $mform->addElement('button', 'boremove', get_string('remove', 'block_itp'));

        $mform->addElement('html',  '<div id="error-message" class="alert alert-danger" role="alert" style="display:none">');

       
        
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}