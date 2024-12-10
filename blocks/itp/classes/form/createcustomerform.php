<?php
namespace block_itp\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class createcustomerform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        
        //Se añaden javascript y CSS
        //Se añade javascript
        $PAGE->requires->js('/blocks/itp/js/customer_formJS.js', false);
        $PAGE->requires->css(new \moodle_url('/blocks/itp/css/styles.css'));
        
        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        //Se configura id de formulario
        $mform->_attributes['id']="customerformid";

        //Se carga la lista de clientes ya creados
        $customers=$DB->get_records('customer');
        $customer_list=array_values($customers);

        $options=array();
        foreach ($customer_list as $customer){
            $options[$customer->shortname]=$customer->id . ' - ' .$customer->shortname . ' - ' . $customer->name;
        }

        //Se crean los campos
        $customercode=$mform->addElement('text', 'customercode', get_string('customercode_text', 'block_itp'),[]);
        $mform->addRule('customercode','error, id is mandatory','required');
        $customercode->setType('customercode',PARAM_TEXT);
                
        $customername=$mform->addElement('text', 'customername', get_string('customername_text', 'block_itp'),[]);
        $mform->addRule('customername','error, name is mandatory','required');
        $customername->setType('customername',PARAM_TEXT);       
        
        $attributes=array('size'=>10);
        $mform->addElement('select', 'type', get_string('customer_select', 'block_itp'),$options,$attributes);

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        
        if ($token)
            $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   
        
        $mform->addElement('html',  '<div id="error-message" class="alert alert-danger" role="alert" style="display:none">');
        
        $mform->addElement('button', 'bosubmit', get_string('submit', 'block_itp'));
        $mform->addElement('button', 'boremove', get_string('remove', 'block_itp'));
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}
