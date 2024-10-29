<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class createsubcategoryform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        
        //Se añaden javascript y CSS
        //Se añade javascript
        $PAGE->requires->js(new \moodle_url('/local/ticketmanagement/js/subcategory_formJS.js'), false);
        $PAGE->requires->css(new \moodle_url('/local/ticketmanagement/css/styles.scss'));
        
        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        //Se configura id de formulario
        $mform->_attributes['id']="subcategoryformid";

        //Se carga la lista de clientes ya creados
        $category=$DB->get_records('ticket_category');
        $category_list=array_values($category);

        $options=array();
        foreach ($category_list as $elem){
            $options[$elem->id]=$elem->category;
        }

        //Se crean los campos       
        $mform->addElement('select', 'categorySelect', get_string('category_select', 'local_ticketmanagement'),$options);

        $mform->addElement('text', 'subcategoryname', get_string('subcategory', 'local_ticketmanagement'),[]);
        
        $mform->setType('subcategoryname',PARAM_TEXT);

        $attributes=array('size'=>10);
        $values=array_keys($options);
        $firstCategoryKey=$values[0];
        //Get the subcategories attached to the first selected category
        $subcategories=$DB->get_records('ticket_subcategory', ['categoryid'=>$firstCategoryKey],'','id,subcategory');
        $options=array();
        foreach ($subcategories as $elem){
            $options[$elem->id]=$elem->subcategory;
        }
        $mform->addElement('select', 'subcategorySelect', get_string('subcategory_select', 'local_ticketmanagement'),$options,$attributes);

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   
        
        $mform->addElement('html',  '<div id="error-message" class="alert alert-danger" role="alert" style="display:none">');
        $mform->addElement('html',  '</div>');
        $mform->addElement('button', 'bosubmit', get_string('new', 'local_ticketmanagement'));
        $mform->addElement('button', 'boedit', get_string('edit', 'local_ticketmanagement'));
        $mform->addElement('button', 'boremove', get_string('remove', 'local_ticketmanagement'));
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}