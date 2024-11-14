<?php
namespace local_ticketmanagement\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class uploadFamilyformcsv extends \moodleform {
    // Add elements to form.
    public function definition() {
        global $PAGE, $DB, $USER;
        
        //Se añaden javascript y CSS
        //Se añade javascript
        //$PAGE->requires->js(new \moodle_url('/local/ticketmanagement/js/family_formJS.js'), false);
        //$PAGE->requires->css(new \moodle_url('/local/ticketmanagement/css/styles.scss'));
        
        $mform = $this->_form; // Don't forget the underscore!
        $mform->disable_form_change_checker();
        //Se configura id de formulario
        $mform->_attributes['id']="familyformid";

        $maxbytes='10M';

        $mform->addElement('filepicker',  'familycsv',  'Upload family csv',  null, array('maxbytes' => $maxbytes,  'accepted_types' => '.csv'));

         // Control para seleccionar el formato del archivo CSV
         $mform->addElement('select', 'format', 'Seleccione el formato de CSV', [
            '\t' => 'Tab',
            ';' => ';',
            ',' => ',',
            ':' => ':'
        ]);
        $mform->setDefault('format', ';'); // Establecer formato por defecto si deseas

        // Añadir botones de 'Save changes' y 'Cancel'
        $this->add_action_buttons(true, 'Save changes');
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Ejemplo: Validación personalizada para el archivo CSV
        if (!isset($files['familycsv'])) {
            $errors['familycsv'] = 'Por favor, suba un archivo CSV válido.';
        }
        
        return $errors;
    }
}