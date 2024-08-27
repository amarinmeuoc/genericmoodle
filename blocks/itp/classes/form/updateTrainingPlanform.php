<?php
namespace block_itp\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class updateTrainingPlanform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        $mform = $this->_form; // Don't forget the underscore!
        
        //Se añade javascript
        $PAGE->requires->js('/blocks/itp/js/trainingplan_formJS.js', false);
        
        //Se crean los campos
        $list_of_customers=$DB->get_records('customer',null,'','id,name');
        foreach ($list_of_customers as $key => $customer) {
            $list_of_customers[$key]=$customer->name;
        }
        
        $select=$mform->addElement('select', 'tecustomer', get_string('tecustomer', 'block_itp'), $list_of_customers, '');

        $maxbytes=255;
        $mform->addElement(
            'filepicker',
            'csv_file',
            get_string('file'),
            null,
            [
                'maxbytes' => $maxbytes,
                'accepted_types' => '.csv',
            ]
        );


         // Add radio options for CSV delimiter
         $delimiter_options = array(
            ';' => get_string('semicolon', 'block_itp'),
            ',' => get_string('comma', 'block_itp'),
            "\t" => get_string('tab', 'block_itp'),
            ':' => get_string('colon', 'block_itp'),
        );

        $mform->addElement('select', 'selectdelimiter', get_string('csvdelimiter', 'block_itp'), $delimiter_options, '');

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   
        
        $mform->addElement('html',  '<div id="button_container" class="flex row m-3">');
        $mform->addElement('button', 'boremove', get_string('reset', 'block_itp'));

        $mform->addElement('html',  '</div>');

        $this->add_action_buttons();

        $mform->addElement('html',  '<div id="error-message" class="alert alert-danger" role="alert" style="display:none">');

       
        
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}