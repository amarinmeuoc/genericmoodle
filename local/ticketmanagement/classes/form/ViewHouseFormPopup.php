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


class ViewHouseFormPopup extends \core_form\dynamic_form {


    public function definition() {
        global $DB;
        $mform = $this->_form;
        $mform->setAttributes(['id' => 'view_house_form']);
        $houseid = $this->_ajaxformdata['houseid'] ?? null;
        
        $selectedaddress = $DB->get_record('ticketmanagement_address', ['id' => $houseid]);
        $type = ($selectedaddress->type==='Internal')?0:1;
        $address=$selectedaddress->address;
        $town=$selectedaddress->town;
    
        define('ADDRESS_TYPES', ['Internal', 'External']);

        // Tipo de dirección
        $mform->addElement('select', 'type', get_string('addresstype', 'local_ticketmanagement'), ADDRESS_TYPES, []);
        $mform->setType('type', PARAM_INT);
        $mform->setDefault('type', $type); 
        

        // Dirección interna
        $mform->addElement('select', 'internal_address', get_string('internaladdress', 'local_ticketmanagement'), 
            ['Carraca -Cuatro Torres-', 'Carraca -Houses-'], []);
        $mform->setType('internal_address', PARAM_INT);
        if ($address==='Carraca -Cuatro Torres-'){
            $mform->setDefault('internal_address',0);
        }
        if ($address==='Carraca -Houses-'){
            $mform->setDefault('internal_address',1);
        }
        $mform->hideIf('internal_address', 'type', 'neq', '0'); // Show only when "Internal" (0) is selected
        
        // Dirección externa
        $mform->addElement('text', 'town', get_string('town', 'local_ticketmanagement'));
        $mform->setType('town', PARAM_TEXT);
        $mform->addRule('town', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->hideIf('town', 'type', 'neq', '1'); // Show only when "External" (1) is selected
        
        $mform->addElement('text', 'external_address', get_string('externaladdress', 'local_ticketmanagement'));
        $mform->setType('external_address', PARAM_TEXT);
        $mform->addRule('external_address', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->hideIf('external_address', 'type', 'neq', '1'); // Show only when "External" (1) is selected

        
        $mform->addElement('text', 'bloque', get_string('bloque', 'local_ticketmanagement'));
        $mform->setType('bloque', PARAM_TEXT);
        $mform->addRule('bloque', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
        $mform->hideIf('bloque', 'type', 'neq', '1');
        
        $mform->addElement('text', 'puerta', get_string('puerta', 'local_ticketmanagement'));
        $mform->setType('puerta', PARAM_TEXT);
        $mform->addRule('puerta', get_string('maximumchars', '', 10), 'maxlength', 10, 'client');
        $mform->hideIf('puerta', 'type', 'neq', '1');
        
        // Pisos (solo para Cuatro torres)
        $mform->addElement('select', 'floor', get_string('floor', 'local_ticketmanagement'), 
            ['First Floor', 'Second Floor', 'Third Floor'], []);
        $mform->hideIf('floor', 'internal_address', 'neq', '0'); // Show only when "Cuatro torres" (0) is selected
        $mform->hideIf('floor', 'type', 'neq', '0'); // Show only when "Cuatro torres" (0) is selected
        
        // Casas (solo para Viviendas)
        $mform->addElement('select', 'house', get_string('house', 'local_ticketmanagement'), 
            ['House1', 'House2', 'House3', 'House4', 'House5', 'House6', 'House7', 'House8', 'House9'], []);
        $mform->hideIf('house', 'internal_address', 'neq', '1'); // Show only when "Cuatro torres" (0) is selected
        $mform->hideIf('house', 'type', 'neq', '0'); // Show only when "Cuatro torres" (0) is selected
        
        $mform->addElement('text', 'numero', get_string('numero', 'local_ticketmanagement'));
        $mform->setType('numero', PARAM_INT);

        $mform->addElement('date_selector', 'entry_date', get_string('entry_date', 'local_ticketmanagement'), []);
        $mform->addElement('date_selector', 'departure_date', get_string('departure_date', 'local_ticketmanagement'), ['optional' => true]);
        
        // Token y houseid
        $token = $DB->get_field_sql("SELECT token FROM {external_tokens} 
                                   INNER JOIN {user} ON {user}.id = {external_tokens}.userid
                                   WHERE username = :username LIMIT 1", 
                                   ['username' => 'logisticwebservice']);
        
        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token', PARAM_TEXT);
        
        $mform->addElement('hidden', 'houseid',$houseid);
        $mform->setType('houseid', PARAM_INT);

        $userid=$selectedaddress->userid;
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
    }


   
    

    // This method processes the submitted data
    public function process_data($data) {
        
    }

    // Custom validation if needed
   // Custom validation if needed
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Validate external address fields if type is External (1)
        if ($data['type'] == 1) {
            if (empty(trim($data['town']))) {
                $errors['town'] = get_string('error_town_required', 'local_ticketmanagement');
            }
            
            if (empty(trim($data['external_address']))) {
                $errors['external_address'] = get_string('error_external_address_required', 'local_ticketmanagement');
            }
 
            // Validate external_address length
            if (!empty($data['external_address']) && strlen(trim($data['external_address'])) > 255) {
                $errors['external_address'] = get_string('error_address_too_long', 'local_ticketmanagement');
            }

            // Validate town length
            if (!empty($data['town']) && strlen(trim($data['town'])) > 255) {
                $errors['town'] = get_string('error_town_too_long', 'local_ticketmanagement');
            }
            
            // Validate block length
            if (!empty($data['bloque']) && strlen(trim($data['bloque'])) > 100) {
                $errors['bloque'] = get_string('error_block_too_long', 'local_ticketmanagement');
            }

            // Validate door length
            if (!empty($data['puerta']) && strlen(trim($data['puerta'])) > 10) {
                $errors['puerta'] = get_string('error_door_too_long', 'local_ticketmanagement');
            }
        }
        
           
        // Validate house number
        if (empty($data['numero']) || !is_numeric($data['numero'])) {
            $errors['numero'] = get_string('error_invalid_number', 'local_ticketmanagement');
        }
        
        // Validate entry date
        if (empty($data['entry_date'])) {
            $errors['entry_date'] = get_string('error_entry_date_required', 'local_ticketmanagement');
        }
        
        // Validate departure date is after entry date if provided
        if (!empty($data['departure_date']) && $data['departure_date'] < $data['entry_date']) {
            $errors['departure_date'] = get_string('error_departure_before_entry', 'local_ticketmanagement');
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

        // Debug: Ver qué datos estamos recibiendo
        error_log("Datos recibidos en process_dynamic_submission: " . print_r($data, true));

        $addressObj = new \stdClass();
        $addressObj->id = $data->houseid ?? 0;
        $addressObj->type = ($data->type === '0') ? 'Internal' : 'External';
        
        // Manejo seguro de campos condicionales
        $addressObj->address = ($data->type === '0') 
            ? (($data->internal_address === '0') ? 'Carraca -Cuatro Torres-' : 'Carraca -Houses-')
            : ($data->external_address ?? '');
        
        // Usar el operador null coalescente para campos opcionales
        $addressObj->house = $data->house ?? 0;
        $addressObj->floor = $data->floor ?? 0;
        $addressObj->block = $data->bloque ?? '';
        $addressObj->door = $data->puerta ?? '';
        $addressObj->number = $data->numero ?? '';
        $addressObj->town = $data->town ?? 'San Fernando';
        $addressObj->entry_date = $data->entry_date ?? 0;
        $addressObj->departure_date = $data->departure_date ?? 0;
        $addressObj->userid = $data->userid;

        // Validación más robusta
        if (empty(trim($addressObj->address))) {
            return [
                'status' => 'error',
                'message' => get_string('address_required', 'local_ticketmanagement')
            ];
        }

        if (empty(trim($addressObj->number))) {
            return [
                'status' => 'error',
                'message' => get_string('number_required', 'local_ticketmanagement')
            ];
        }

        try {
            $updated = $DB->update_record('ticketmanagement_address', $addressObj);
            
            if ($updated) {
                return [
                    'status' => 'success',
                    'message' => get_string('update_success', 'local_ticketmanagement'),
                    'data' => $addressObj
                ];
            } else {
                // No se actualizó ningún registro (posiblemente porque los datos son iguales)
                return [
                    'status' => 'info',
                    'message' => get_string('no_changes_detected', 'local_ticketmanagement'),
                    'data' => $addressObj
                ];
            }
        } catch (\Exception $e) {
            error_log("Error al actualizar dirección: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => get_string('update_failed', 'local_ticketmanagement') . ': ' . $e->getMessage()
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
    
    $houseid = $this->_ajaxformdata['houseid'] ?? null;
    
    if ($houseid) {
        $selectedaddress = $DB->get_record('ticketmanagement_address', ['id' => $houseid]);
        
        if ($selectedaddress) {
            $data = [
                'houseid' => $selectedaddress->id,
                'type' => ($selectedaddress->type==='Internal')?0:1,
                'internal_address' => ($selectedaddress->address == 'Carraca -Cuatro Torres-') ? 0 : 1,
                'town' => ($selectedaddress->type == 'External') ? $selectedaddress->town : null,
                'external_address' => ($selectedaddress->type == 'External') ? $selectedaddress->address : null,
                'bloque' => $selectedaddress->block,
                'puerta' => $selectedaddress->door,
                'floor' => $selectedaddress->floor,
                'house' => $selectedaddress->house,
                'numero' => $selectedaddress->number,
                'entry_date' => $selectedaddress->entry_date,
                'departure_date' => $selectedaddress->departure_date
            ];
            
            // Debug para verificar los datos
            debugging("Datos a establecer: " . print_r($data, true), DEBUG_DEVELOPER);
            
            $this->set_data($data);
        }
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
