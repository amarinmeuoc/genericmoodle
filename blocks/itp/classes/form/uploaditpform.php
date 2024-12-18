<?php
namespace block_itp\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php"); 

class uploaditpform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        $mform = $this->_form; // Don't forget the underscore!
        $mform->_attributes['id']="uploaditpform";
        $mform->disable_form_change_checker();
        //Se añade javascript
        $PAGE->requires->js('/blocks/itp/js/uploaditp_formJS.js', false);
        //$PAGE->requires->js_call_amd('block_itp/uploaditp_formJS', 'init');
        
        $idcustomer = optional_param('customerid', null, PARAM_INT);
        //Se crean los campos
        $list_of_customers=$DB->get_records('customer',null,'','id,name');
        foreach ($list_of_customers as $key => $customer) {
            $list_of_customers[$key]=$customer->name;
        }
        
        $mform->addElement('select', 'tecustomer', get_string('tecustomer', 'block_itp'), $list_of_customers, '');
        $mform->setType('tecustomer', PARAM_INT);

        $selected_customer=$idcustomer;
        
        if ($idcustomer===null)
            $selected_customer=array_key_first($list_of_customers);
        else {
            
            $selected_customer=$idcustomer;
            $list_of_groups=$DB->get_records('grouptrainee',['customer'=>$selected_customer,'hidden'=>0],'','id,name');
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

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'email', '', get_string('email_yes','block_itp'), 'yes', []);
        $radioarray[] = $mform->createElement('radio', 'email', '', get_string('email_no','block_itp'), 'no', []);
        $mform->addGroup($radioarray, 'radioemail', 'Send an email after the ITP has been uploaded: ', array(' '), false);
        $mform->setDefault('email', 'yes');

        $mform->addElement('text', 'subject', get_string('subject', 'block_itp'), ['placeholder'=>'Email subject...']);
        $mform->setType('subject',PARAM_TEXT);
        $mform->setDefault('subject','New ITP Update available from the LMS');

        // Configuración personalizada para el editor TinyMCE
        $editoroptions = array(
            'maxfiles' => 0, // No permitir subir archivos
            'maxbytes' => 0, // Limitar el tamaño de los archivos
            'trusttext' => true, 
            'noclean' => true, 
            'subdirs' => false,
            'enable_filemanagement' => false, // Desactivar gestión de archivos
            'return_types' => FILE_INTERNAL, // Configuración para archivos internos
            'format' => FORMAT_HTML,
            
        );

        // Agregar el elemento de editor con las opciones personalizadas
        $mform->addElement('editor', 'email_editor', get_string('labeltextemaileditor', 'block_itp'), null, $editoroptions);
        $mform->setType('email_editor', PARAM_RAW);
        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   
        
        $mform->addElement('html',  '<div id="button_container" class="flex row m-3">');
        $mform->addElement('button', 'boremove', get_string('reset_itp', 'block_itp'));

        $mform->addElement('html',  '</div>');

        $this->add_action_buttons();
        //$mform->addElement('button',  'bosubmit',get_string('save', 'block_itp'),[]);

        $mform->addElement('html',  '<div id="error-message" class="alert alert-danger" role="alert" style="display:none">');

    
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}