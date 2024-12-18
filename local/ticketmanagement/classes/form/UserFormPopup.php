<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the local_createcustomer plugin code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    local_ticketmanagement
 * @copyright  2024 Alberto Marín Mendoza (http://myhappycoding.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ticketmanagement\form;


class UserFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB;
        $mform = $this->_form;
    
        $userid = $this->_ajaxformdata['userid'];
    
        // Obtener información del usuario
        $user = $DB->get_record('user', ['id' => $userid, 'suspended'=>1], 'id, email, firstname, lastname, phone1, phone2, address, city');
    
        // Mostrar información estática del usuario
        $mform->addElement('static', 'useridtitle', get_string('showuser', 'local_ticketmanagement'), $user->firstname . ' ' . $user->lastname);
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
    
        // Campos del formulario
        $fields = [
            ['name' => 'passport', 'type' => PARAM_TEXT],
            ['name' => 'nie', 'type' => PARAM_TEXT],
            ['name' => 'personalemail', 'type' => PARAM_EMAIL],
            ['name' => 'phone1', 'type' => PARAM_TEXT],
            ['name' => 'phone2', 'type' => PARAM_TEXT],
            ['name' => 'address', 'type' => PARAM_TEXT],
            ['name' => 'city', 'type' => PARAM_TEXT],
            ['name' => 'insurance_card_number', 'type' => PARAM_TEXT],
            ['name' => 'shoesize', 'type' => PARAM_TEXT],
            ['name' => 'overallsize', 'type' => PARAM_TEXT],
        ];
    
        foreach ($fields as $field) {
            $mform->addElement('text', $field['name'], get_string($field['name'], 'local_ticketmanagement'));
            $mform->setType($field['name'], $field['type']);
            $mform->addRule($field['name'], get_string('maxlength', 'local_ticketmanagement', 250), 'maxlength', 250, 'client');
        }
    
        // Validaciones específicas
        $mform->addRule('shoesize', get_string('integer', 'local_ticketmanagement'), 'numeric', null, 'client');
        $mform->addRule('overallsize', get_string('integer', 'local_ticketmanagement'), 'numeric', null, 'client');
    
        $mform->addRule('phone1', get_string('validphone', 'local_ticketmanagement'), 'regex', '/^\+?\d{9,15}$/', 'client');
        $mform->addRule('phone2', get_string('validphone', 'local_ticketmanagement'), 'regex', '/^\+?\d{9,15}$/', 'client');
    
        $mform->addRule('personalemail', get_string('validemail', 'local_ticketmanagement'), 'email', null, 'client');
    
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_ticketmanagement'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('notes', PARAM_TEXT);
    
        // Campos de fecha
        $mform->addElement('date_selector', 'niedate', get_string('niedate', 'local_ticketmanagement'));
        $mform->addElement('date_selector', 'birthdate', get_string('birthdate', 'local_ticketmanagement'));
        $mform->addElement('date_selector', 'arrival_date', get_string('arrival_date', 'local_ticketmanagement'));
        $mform->addElement('date_selector', 'departure_date', get_string('departure_date', 'local_ticketmanagement'));
    }
    

    // This method processes the submitted data
    public function process_data($data) {
        
    }

    // Custom validation if needed
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Add any custom validation if necessary
        return $errors;
    }

    // Return any additional data after form submission (optional)
    public function get_return_data() {
        // Return data after form submission if necessary
        return ['success' => true];
    }

     /**
     * Check if current user has access to this form, otherwise throw exception
     *
     * Sometimes permission check may depend on the action and/or id of the entity.
     * If necessary, form data is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     */
    protected function check_access_for_dynamic_submission(): void {
        return;
    }

    /**
     * Returns form context
     *
     * If context depends on the form data, it is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     *
     * @return \context
     */
    protected function get_context_for_dynamic_submission(): \context {
      
        return \context_system::instance();
    }

    /**
     * File upload options
     *
     * @return array
     * @throws \coding_exception
     */
    protected function get_options(): array {
        return [];
    }

 
    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * This method can return scalar values or arrays that can be json-encoded, they will be passed to the caller JS.
     *
     * Submission data can be accessed as: $this->get_data()
     *
     * @return mixed
     */
    public function process_dynamic_submission() {
        global $DB;

        // Obtener los datos enviados del formulario.
        $data = $this->get_data();
        if (!$data) {
            return false; // Si no hay datos, finalizar el proceso.
        }

        // Validar el ID del usuario
        if (empty($data->userid) || !$user = $DB->get_record('user', ['id' => $data->userid])) {
            throw new moodle_exception('invaliduserid', 'local_ticketmanagement');
        }

        // Extraer el ID del usuario.
        $userid = $data->userid;

        // Actualiza los campos estándar del usuario.
        $userfields = [
            'phone1', 
            'phone2', 
            'address', 
            'city'
        ];

        $userrecord = new \stdClass();
        $userrecord->id = $userid;

        foreach ($userfields as $field) {
            if (isset($data->{$field})) {
                $userrecord->{$field} = $data->{$field};
            }
        }

        // Actualizar el registro en la tabla de usuarios.
        $DB->update_record('user', $userrecord);

        // Actualizar los campos personalizados.
        $profilefields = [
            'passport',
            'nie',
            'niedate',
            'birthdate',
            'personalemail',
            'insurance_card_number',
            'shoesize',
            'overallsize',
            'arrival_date',
            'departure_date',
            'notes'
        ];

        foreach ($profilefields as $field) {
            if (isset($data->{$field})) {
                // Verifica si el campo es de tipo fecha.
                if (in_array($field, ['arrival_date', 'departure_date', 'birthdate','niedate'])) {
                    // Convierte el valor de fecha a un timestamp UNIX si es una cadena.
                    $data->{$field} = is_numeric($data->{$field}) ? (int)$data->{$field} : strtotime($data->{$field});
                    if ($data->{$field} === false) {
                        $data->{$field} = null; // Opcionalmente, establece como null si no se puede convertir
                    }
                }

                        
                // Verifica si el campo ya existe para este usuario.
                $record = $DB->get_record('user_info_field', ['shortname' => $field]);
                if ($record) {
                    $fieldid = $record->id;
        
                    // Comprueba si ya existe un valor para este campo y usuario.
                    $datainfo = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $fieldid]);
        
                    if ($datainfo) {
                        // Actualiza el registro existente.
                        $datainfo->data = $data->{$field};
                        $datainfo->dataformat = FORMAT_PLAIN;
                        $DB->update_record('user_info_data', $datainfo);
                    } else {
                        // Inserta un nuevo registro.
                        $datainfo = new \stdClass();
                        $datainfo->userid = $userid;
                        $datainfo->fieldid = $fieldid;
                        $datainfo->data = $data->{$field};
                        $datainfo->dataformat = FORMAT_PLAIN;
                        $DB->insert_record('user_info_data', $datainfo);
                    }
                }
            }
        }
        
       

        return $this->get_data(); // Indica que la actualización fue exitosa.
        
    }

    

    /**
     * Load in existing data as form defaults
     *
     * Can be overridden to retrieve existing values from db by entity id and also
     * to preprocess editor and filemanager elements
     *
     * Example:
     *     $this->set_data(get_entity($this->_ajaxformdata['id']));
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        $mform = $this->_form;

        $userid = $this->_ajaxformdata['userid'];
        
        // Obtener todos los campos personalizados
        $customfields = $DB->get_records('user_info_field', null, '', 'id, shortname');
        
        // Crear un mapa de fieldid a shortname
        $fieldmap = [];
        foreach ($customfields as $field) {
            $fieldmap[$field->id] = $field->shortname;
        }

        // Obtener los datos personalizados del usuario
        $userdata = $DB->get_records('user_info_data', ['userid' => $userid], '', 'fieldid, data');

        // Inicializar arreglo de datos del formulario
        $data = ['userid' => $userid];

        // Asignar valores a cada campo basado en shortname
        foreach ($userdata as $record) {
            if (isset($fieldmap[$record->fieldid])) {
                $shortname = $fieldmap[$record->fieldid];
                
                $data[$shortname] = $record->data;
            }
        }

        $fields=['phone1', 'phone2', 'address','city'];
        $userinfo=$DB->get_record('user',['id'=>$userid],'phone1,phone2,address,city');
        $data['phone1']=$userinfo->phone1;
        $data['phone2']=$userinfo->phone2;
        $data['address']=$userinfo->address;
        $data['city']=$userinfo->city;

        // Asegurarse de que todos los campos existan, incluso si no hay datos
        $fields = ['passport', 'nie', 'niedate', 'birthdate', 'personalemail', 'insurance_card_number', 'shoesize', 'overallsize', 'arrival_date', 'departure_date', 'notes'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = ($field === 'niedate' || $field === 'birthdate' || $field === 'arrival_date' || $field === 'departure_date') ? 0 : '';
            }
        }

        // Pasar los datos al formulario
        $this->set_data($data);
        

    }

    public function get_description_text_options() : array {
        global $CFG;
        require_once($CFG->libdir.'/formslib.php');
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'context' => \context_system::instance()
        ];
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * This is used in the form elements sensitive to the page url, such as Atto autosave in 'editor'
     *
     * If the form has arguments (such as 'id' of the element being edited), the URL should
     * also have respective argument.
     *
     * @return \moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): \moodle_url {
                
        return new \moodle_url('/local_ticketmanagement/index.php');
    }

}