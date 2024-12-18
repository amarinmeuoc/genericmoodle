<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class createcategoryform extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB;
        
        //Se añaden javascript y CSS
        //Se añade javascript
        $PAGE->requires->js(new \moodle_url('/local/ticketmanagement/js/category_formJS.js'), false);
        $PAGE->requires->css(new \moodle_url('/local/ticketmanagement/css/styles.scss'));
        
        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        //Se configura id de formulario
        $mform->_attributes['id']="categoryformid";

        //Se carga la lista de categorias ya creadas
        $category=$DB->get_records('ticket_category');
        $category_list=array_values($category);

        $options=array();
        $hidden_values = []; // Aquí guardamos los valores `hidden` de cada categoría
        foreach ($category_list as $elem){
            $options[$elem->id]=$elem->category;
            $hidden_values[$elem->id] = $elem->hidden; // Guardar el valor `hidden` de la categoría
        }

        //Se crean los campos
        $mform->addElement('text', 'categoryname', get_string('category', 'local_ticketmanagement'),[]);
        $mform->addRule('categoryname','error, this field is required','required');
        $mform->setType('categoryname',PARAM_TEXT);

        $mform->addElement('advcheckbox', 'hiddencategory', get_string('hiddencategory', 'local_ticketmanagement'), 'Select this checkbox to set a hidden category', [], array(0, 1));
        
        $attributes=array('size'=>10);
        $mform->addElement('select', 'categorySelect', get_string('category_select', 'local_ticketmanagement'),$options,$attributes);

        $category_element = $mform->getElement('categorySelect');

        
        // Agregar el atributo `data-hidden` a cada opción
        foreach ($category_element->_options as &$option) {
            
            $id = $option['attr']['value']; // El id de la categoría es el valor de la opción
            if (isset($hidden_values[$id])) {
                $option['attr']['data-hidden'] = $hidden_values[$id]; // Agregar el atributo        
            }
            
        }
        
        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                            INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                            WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
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