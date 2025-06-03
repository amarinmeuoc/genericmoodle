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


class HouseFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $mform->setAttributes(['id' => 'house_form']);
    
        $userid = $this->_ajaxformdata['userid'];
    
        // Obtener información del usuario
       // $selecteduser = $DB->get_record('user', ['id' => $userid, 'suspended'=>1], 'id, email, firstname, lastname, phone1, phone2, address, city');
       
        //$mform->addElement('static', 'useridtitle', get_string('showuser', 'local_ticketmanagement'), $selecteduser->firstname);
        
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
    
        //Show all houses of the selected user in a grid
        $houses = $DB->get_records('ticketmanagement_address', ['userid' => $userid]); 
        

        if ($houses) {
            $table_html = '<div class="table-responsive-xl">
            <table class="table generaltable house-table">
                <thead>
                    <tr>
                        <th>' . get_string('Type', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('Address', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('Entrydate', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('Departuredate', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('View', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('Remove', 'local_ticketmanagement') . '</th>
                        
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($houses as $house) {
                debugging("fecha dump: " . print_r($house->departure_date, true), DEBUG_DEVELOPER);
                
                $table_html .= '<tr id="house_'.$house->id.'">
                    <td><span class="badge badge-secondary">' . s($house->type) . '</span></td>
                    <td><span class="text-muted">' . s($house->address) . '</span></td>
                    <td class="text-center">' . userdate($house->entry_date, get_string('strftimedate', 'langconfig')) . '</td>
                    <td class="text-center">' . (($house->departure_date == 0 || empty($house->departure_date)) ? '-' : userdate($house->departure_date, get_string('strftimedate', 'langconfig'))) . '</td>
                    <td>
                        <button type="button" class="view-house btn btn-secondary" data-id="' . $house->id . '">' . get_string('View', 'local_ticketmanagement') . '</button>
                    </td>
                    <td>
                        <button type="button" class="remove-house btn btn-secondary" data-id="' . $house->id . '">' . get_string('remove', 'local_ticketmanagement') . '</button>
                    </td>
                </tr>';
            }
        
            $table_html .= '</tbody></table></div>';
        
            $mform->addElement('html', $table_html);
        } else {
            $mform->addElement('static', 'nohouse', '', get_string('nohouse', 'local_ticketmanagement'));
        }

        $mform->addElement('header', 'addhouseheader', get_string('addnewhouse', 'local_ticketmanagement'));

        define('ADDRESS_TYPES', ['Internal', 'External']);

        // Dropdown for selecting address type (Internal/External)
        $mform->addElement('select', 'type', get_string('addresstype', 'local_ticketmanagement'), ADDRESS_TYPES, []);

        // 1. Select dropdown (for Internal addresses)
        $mform->addElement('select', 'internal_address', get_string('internaladdress', 'local_ticketmanagement'), ['Carraca -Cuatro torres-', 'Carraca -Houses-'], []);
        $mform->hideIf('internal_address', 'type', 'neq', '0'); // Show only when "Internal" (0) is selected

        // 2. Text input (for External addresses)
        $mform->addElement('text', 'town', get_string('town', 'local_ticketmanagement'));
        $mform->setType('town', PARAM_TEXT);
        $mform->setDefault('town', 'San Fernando'); // Set default value
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
     

        // 3. Select dropdown (for Cuatro torres value)
        $mform->addElement('select', 'floor', get_string('floor', 'local_ticketmanagement'), ['Ground Floor', 'First Floor', 'Second Floor'], []);
        $mform->hideIf('floor', 'internal_address', 'neq', '0'); // Show only when "Cuatro torres" (0) is selected
        $mform->hideIf('floor', 'type', 'neq', '0'); // Show only when "Cuatro torres" (0) is selected

        // 3. Select dropdown (for Viviendas value)
        $mform->addElement('select', 'house', get_string('house', 'local_ticketmanagement'), ['House1', 'House2', 'House3','House4', 'House5', 'House6','House7', 'House8', 'House9'], []);
        $mform->hideIf('house', 'internal_address', 'neq', '1'); // Show only when "Cuatro torres" (0) is selected
        $mform->hideIf('house', 'type', 'neq', '0'); // Show only when "Cuatro torres" (0) is selected
        
        $mform->addElement('text', 'numero', get_string('numero', 'local_ticketmanagement'));
        $mform->setType('numero', PARAM_INT);

        $mform->addElement('date_selector', 'entry_date', get_string('entry_date', 'local_ticketmanagement'),[]);
        $mform->addElement('date_selector', 'departure_date', get_string('departure_date', 'local_ticketmanagement'),['optional'=>true]);
        
         //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                                    INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                                    WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   

        
        

        
    }
    

    // This method processes the submitted data
    public function process_data($data) {
        
    }

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

        $addressObj = new \stdClass();
        $addressObj->type = ($data->type === '0') ? 'Internal' : 'External';
        $addressObj->address = ($data->type === '0') 
            ? (($data->internal_address === '0') ? 'Carraca -Cuatro Torres-' : 'Carraca -Houses-')
            : ($data->external_address ?? ''); // Usar operador null coalescente

        // Asignar valores por defecto si no existen
        $addressObj->house = $data->house ?? 0;
        $addressObj->floor = $data->floor ?? 0;
        $addressObj->block = $data->bloque ?? '';
        $addressObj->door = $data->puerta ?? '';
        $addressObj->number = $data->numero ?? 0;
        $addressObj->town = $data->town ?? 'San Fernando';
        $addressObj->entry_date = $data->entry_date ?? 0;
        $addressObj->departure_date = $data->departure_date ?? 0;
        $addressObj->userid = $data->userid;

        // Validar campos obligatorios
        if (empty(trim($addressObj->address))) {
            throw new \moodle_exception('error_address_required', 'local_ticketmanagement');
        }

        // Insertar en la base de datos
        try {
            $DB->insert_record('ticketmanagement_address', $addressObj);
            return ['status' => 'success', 'message' => get_string('house_added', 'local_ticketmanagement')];
        } catch (\Exception $e) {
            error_log("Error al insertar dirección: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
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

        // Obtener el userid desde los datos proporcionados
        $userid = $this->_ajaxformdata['userid'];

       

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