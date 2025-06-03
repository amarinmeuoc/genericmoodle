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


class ViewCarFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB;
        $mform = $this->_form;
  
        $mform->addElement('text', 'brand', get_string('brand', 'local_ticketmanagement'));
        $mform->setType('brand', PARAM_TEXT);

        $mform->addElement('text', 'model', get_string('model', 'local_ticketmanagement'));
        $mform->setType('model', PARAM_TEXT);

        $mform->addElement('text', 'color', get_string('color', 'local_ticketmanagement'));
        $mform->setType('color', PARAM_TEXT); 

        
        
        $mform->addElement('text', 'platenumber', get_string('platenumber', 'local_ticketmanagement'));
        $mform->setType('platenumber', PARAM_TEXT);

        

        $mform->addElement('date_selector', 'delivery_date', get_string('delivery_date', 'local_ticketmanagement'),['optional'=>false]);
       
        $mform->addElement('date_selector', 'refund_date', get_string('refund_date', 'local_ticketmanagement'),['optional'=>true]);
       
        

         //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                                    INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                                    WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   

        $carid = $this->_ajaxformdata['carid'] ?? null;
        $mform->addElement('hidden', 'carid',  $carid);
        $mform->settype('carid',PARAM_INT);

    }
    

    // This method processes the submitted data
    public function process_data($data) {
        
    }

    // Custom validation if needed
    public function validation($data, $files) {
        $errors = [];

        // Validar que el rol es obligatorio
        if (empty($data['brand'])) {
            $errors['brand'] = get_string('required', 'local_ticketmanagement');
        }

        // Validar que el nombre no esté vacío y tenga al menos 2 caracteres
        if (empty($data['model']) || strlen($data['model']) < 2) {
            $errors['model'] = get_string('modelrequired', 'local_ticketmanagement');
        }

        // Validar que el apellido no esté vacío
        if (empty($data['platenumber'])) {
            $errors['platenumber'] = get_string('platenumberrequired', 'local_ticketmanagement');
        }

       

        // Validar que la fecha de llegada sea anterior a la de salida
        if (!empty($data['delivery_date']) && !empty($data['refund_date']) && $data['delivery_date'] > $data['refund_date']) {
            $errors['refund_date'] = get_string('refund_dateinvalid', 'local_ticketmanagement');
        }

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

        $data = $this->get_data();

        
            // Insertar un nuevo miembro de familia
            $updated=$DB->update_record('ticketmanagement_cars', [
                'id' => $data->carid,
                'brand' => $data->brand,
                'model' => $data->model,
                'platenumber' => $data->platenumber,
                'color' => $data->color,
                'delivery_date' => $data->delivery_date,
                'refund_date' => $data->refund_date,
            ]);

            if ($updated) {
                // Recuperar los datos actualizados para devolverlos
                $updated_record = $DB->get_record('ticketmanagement_cars', ['id' => $data->carid]);
        
                // Retornar los datos como un objeto
                return [
                    'status' => 'success',
                    'message' => get_string('update_success', 'local_ticketmanagement'),
                    'data' => $updated_record
                ];
            } else {
                // Retornar un mensaje de error en caso de fallo
                return [
                    'status' => 'error',
                    'message' => get_string('update_failed', 'local_ticketmanagement')
                ];
            }
    
        
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
        // Si el formulario ya fue enviado, no ejecutamos esta lógica.
        // Si el formulario ya fue enviado, obtener el ticketid desde los datos del formulario
        if ($this->is_submitted()) {
            $data = $this->get_data();
            $carid = $data->carid ?? null;
        } else {
            // Si el formulario se está cargando por primera vez, obtener el ticketid desde _ajaxformdata
            $carid = $this->_ajaxformdata['carid'] ?? null;
        }


        if ($carid) {
            // Consultar los datos del usuario en la base de datos
            $selecteduser = $DB->get_record('ticketmanagement_cars', ['id' => $carid], '*');
            

            $this->set_data([
                'id' => $selecteduser->id,
                'brand' => $selecteduser->brand,
                'model' => $selecteduser->model,
                'platenumber' => $selecteduser->platenumber,
                'color' => $selecteduser->color,
                'delivery_date' => $selecteduser->delivery_date,
                'refund_date' => $selecteduser->refund_date,
            ]);
        }

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