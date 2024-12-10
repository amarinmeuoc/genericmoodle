<?php
namespace block_itp\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class updateTrainingPlanform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        $mform = $this->_form; // Don't forget the underscore!
        $mform->_attributes['id']="uploadtrainingplanform";
        $mform->disable_form_change_checker();
        //Se añade javascript
        $PAGE->requires->js('/blocks/itp/js/trainingplan_formJS.js', false);
        
        $idcustomer = optional_param('customerid', null, PARAM_INT);
        //Se crean los campos
        $list_of_customers=$DB->get_records('customer',null,'','id,name');
        foreach ($list_of_customers as $key => $customer) {
            $list_of_customers[$key]=$customer->name;
        }
        
        $select=$mform->addElement('select', 'tecustomer', get_string('tecustomer', 'block_itp'), $list_of_customers, '');
        $mform->setType('tecustomer', PARAM_INT);

        $selected_customer=$idcustomer;
        
        if ($idcustomer===null)
            $selected_customer=array_key_first($list_of_customers);
        else {
            
            $selected_customer=$idcustomer;
            $list_of_groups=$DB->get_records('grouptrainee',['customer'=>$selected_customer],'','id,name');
            $list_of_groups=array_values($list_of_groups);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($list_of_groups);
            exit();
            
            
        }
        
        $list_of_groups=$DB->get_records('grouptrainee',['customer'=>$selected_customer,'hidden'=>0],'','id,name');
        foreach ($list_of_groups as $key=>$group){
            $list_of_groups[$key]=$group->name;
        }
        
        //Añadir al principio de la lista el valor por defecto
        $list_of_groups=array('0'=>'All Groups')+$list_of_groups;
        
        
        $mform->addElement('select', 'tegroup', get_string('tegroup', 'block_itp'), $list_of_groups, '');
        $mform->setType('tegroup', PARAM_INT);

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