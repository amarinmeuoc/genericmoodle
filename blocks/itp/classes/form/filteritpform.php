<?php
namespace block_itp\form;
// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class filteritpform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $DB,$USER;
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!
        $mform->_attributes['id']="filteritpform";

        // Desactivar el chequeo de cambios del formulario
        $mform->disable_form_change_checker();

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);  
        
        $mform->addElement('hidden', 'email', $USER->email);
        $mform->setType('email',PARAM_TEXT);  

        $mform->addElement('select', 'compacted', get_string('compacted', 'block_itp'), ['no'=>get_string('ungrouped', 'block_itp'),'yes'=>get_string('grouped', 'block_itp')]);

        $mform->addElement('hidden', 'order', '1'); //True para ordenar de forma ascendente y false para descendente
        $mform->setType('order',PARAM_BOOL);  

        $mform->addElement('hidden', 'orderby', 'startdate');
        $mform->setType('orderby',PARAM_TEXT);  
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}