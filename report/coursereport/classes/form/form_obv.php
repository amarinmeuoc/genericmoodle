<?php

namespace report_coursereport\form;
// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class form_obv extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $DB,$USER,$PAGE;
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!
        $mform->_attributes['id']="filtercoursereport";
        $mform->_attributes['class']="mform";
        
        //Añade el comportamiento del formulario al cambiar de valores
        $PAGE->requires->js('/report/coursereport/js/observer_formJS.js', false);

        // Desactivar el chequeo de cambios del formulario
        $mform->disable_form_change_checker();

        //Cargar los campos personalizados
        profile_load_custom_fields($USER);

        $customer_shortname=$USER->profile['customer'];

        $customerid=$DB->get_field('customer', 'id', ['shortname'=>$customer_shortname], IGNORE_MISSING);

        $mform->addElement('hidden', 'selCustomer', $customerid);
        $mform->setType('selCustomer',PARAM_INT);

                
        //Selector de grupo
        $list_of_groups=$DB->get_records('grouptrainee',['customer'=>$customerid],'','id,name');
        
        foreach ($list_of_groups as $key=>$group){
            $list_of_groups[$key]=$group->name;
        }       

        // Add group select.
        $mform->addElement('select', 'grouptrainee', get_string('grouptraineeselect', 'report_coursereport'), $list_of_groups, []);
        $mform->setType('grouptrainee', PARAM_INT);

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
                                    HAVING role_name=:role_name AND customer=:customer AND groupname=:groupname',
                                    ['role_name'=>$role,'customer'=>$customer_shortname, 'groupname'=>$selected_groupname]);
        $trainee_list=array_values($trainee_query);

        $trainee_array=Array();
        
        $pattern='//i';
        foreach($trainee_list as $elem){
            if (preg_match($pattern, $elem->billid)==1)
                $trainee_array[$elem->billid]=$elem->groupname."_".$elem->billid." ".$elem->firstname.", ".$elem->lastname;
        }
        

        $options = array(                                                                                                           
            'multiple' => false,                                                  
            'noselectionstring' => 'Use the select box below to search a trainee',
            'placeholder'=>'Write a trainee billid or a name'                                                                
        );        
        
        $mform->addElement('autocomplete', 'list_trainees', 'Selected trainee', $trainee_array, $options);
        
       
        //Adding course autocomplete list
        $course_query=$DB->get_records('course', [], 'fullname ASC', 'id,shortname,fullname');
        $course_list=array_values($course_query);
        $course_array=Array();
        
        $pattern='/'.$customer_shortname.'_./i';
        foreach($course_list as $elem){
            if (preg_match($pattern, $elem->shortname)==1)
                $course_array[$elem->shortname]=$elem->shortname."_".$elem->fullname;
        }


        $options = array(                                                                                                           
            'multiple' => false,                                                  
            'noselectionstring' => 'Use the select box below to search a course',
            'placeholder'=>'Search a course name by wbs or fullname'                                                                
        ); 
        
        $mform->addElement('autocomplete', 'list_courses', 'Selected course', $course_array, $options);
        
        
        //Adding start date selector
        $mform->addElement('date_selector', 'startdate', get_string('from'));

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'status', '', get_string('completed','report_coursereport'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'status', '', get_string('on_going','report_coursereport'), 0, $attributes);
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
        $mform->setDefault('status', 1);
        
        //Adding end date selector
        //$mform->addElement('date_selector', 'enddate', get_string('to'));

        $mform->addElement('button', 'bosubmit', get_string('send','report_coursereport'));

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);  

        $mform->addElement('hidden', 'order', '1'); //True para ordenar de forma ascendente y false para descendente
        $mform->setType('order',PARAM_BOOL);  

        $mform->addElement('hidden', 'orderby', 'startdate');
        $mform->setType('orderby',PARAM_TEXT);

        $mform->addElement('hidden', 'page', '1');
        
        
    }

    function getId($shortname){
        global $DB;
        $customer=$DB->get_record('customer', ['shortname'=>$shortname], 'id');
        return $customer->id;
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}