<?php
namespace block_itp\form;
// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class filteritpform_observer extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $DB,$USER;
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!
        $mform->_attributes['id']="filteritpform";

        // Desactivar el chequeo de cambios del formulario
        $mform->disable_form_change_checker();

        //Se obtiene el valor del campo personalizado customer
        $customer=$USER->profile['customer'];

        //Se obtiene el id del cliente
        $customerid=$DB->get_field('customer', 'id', ['shortname'=>$customer], IGNORE_MISSING);

        //Selector de grupo
        $list_of_groups=$DB->get_records('grouptrainee',['customer'=>$customerid],'','id,name');
        foreach ($list_of_groups as $key=>$group){
            $list_of_groups[$key]=$group->name;
        }       
        
        $mform->addElement('select', 'tegroup', get_string('tegroup', 'block_itp'), $list_of_groups, '');
        $mform->setType('tegroup', PARAM_INT);

        //Se filtra por el rol de estudiante
        $role='student';
        
        // Obtener el primer valor del array asociativo
        $selected_groupname = reset($list_of_groups);           
        
        //List all trainees in the LMS
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
        $trainee_array[$elem->email]=$elem->groupname."_".$elem->billid." ".$elem->firstname.", ".$elem->lastname;
        }

        $options = array(                                                                                                           
        'multiple' => false,                                                  
        'noselectionstring' => 'Use the select box below to search a trainee',
        'placeholder'=>'Write a trainee billid or a name'                                                                
        );       

        $mform->addElement('autocomplete', 'list_trainees', 'Selected trainee', $trainee_array, $options);

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);  
        
        $mform->addElement('hidden', 'email', $USER->email);
        $mform->setType('email',PARAM_TEXT);  

        //Valor por defecto para el campo email
        $mform->setDefault('email', $USER->email);

        $mform->addElement('hidden', 'customer', $customer);
        $mform->setType('customer',PARAM_TEXT);  
        
        $button=$mform->addElement('button',  'boenviar',  get_string('filtrar', 'block_itp'));

        //Aplicar javascript solo para aplicar estilos al botón
        $mform->addElement('html', '<script type="text/javascript">
                                    const button=document.getElementById("id_boenviar");
                                    button.classList.remove("btn-secondary");
                                    button.classList.add("btn-primary");
                                    </script>');

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