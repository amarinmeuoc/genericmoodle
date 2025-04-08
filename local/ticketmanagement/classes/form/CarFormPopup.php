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


class CarFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $mform->setAttributes(['id' => 'car_form']);
    
        $userid = $this->_ajaxformdata['userid'];
    
        // Obtener información del usuario
        $selecteduser = $DB->get_record('user', ['id' => $userid, 'suspended'=>1], 'id, email, firstname, lastname, phone1, phone2, address, city');
       
        //$mform->addElement('static', 'useridtitle', get_string('showuser', 'local_ticketmanagement'), $selecteduser->firstname);
        
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
    
        //Show all cars of the selected user in a grid
        $cars = $DB->get_records('ticketmanagement_cars', ['userid' => $userid]); 

        if ($cars) {
            $table_html = '<div class="table-responsive-xl">
            <table class="table generaltable car-table">
                <thead>
                    <tr>
                        <th>' . get_string('brand', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('model', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('delivery_date', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('refund_date', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('save', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('view', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('remove', 'local_ticketmanagement') . '</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($cars as $car) {
                $refund_date=(!is_null($car->refund_date) && $car->refund_date!=='0')?
                                userdate($car->refund_date, get_string('strftimedate', 'langconfig')):'';
                $table_html .= '<tr id="car_'.$car->id.'">
                    
                    <td><input class="form-control" type="text" name="tecarbrand" value="' . s($car->brand) . '"></td>
                    <td><input class="form-control" type="text" name="tecarmodel" value="' . s($car->model) . '"></td>
                    <td>' . userdate($car->delivery_date, get_string('strftimedate', 'langconfig')) . '</td>
                    <td>' . $refund_date . '</td>
                    <td>
                        <button type="button" class="edit-car btn btn-secondary" data-id="' . $car->id . '">' . get_string('save', 'local_ticketmanagement') . '</button>
                    </td>
                    <td>
                        <button type="button" class="view-car btn btn-secondary" data-id="' . $car->id . '">' . get_string('View', 'local_ticketmanagement') . '</button>
                    </td>
                    <td>
                        <button type="button" class="remove-car btn btn-secondary" data-id="' . $car->id . '">' . get_string('remove', 'local_ticketmanagement') . '</button>
                    </td>
                    
                </tr>';
            }
        
            $table_html .= '</tbody></table></div>';
        
            $mform->addElement('html', $table_html);
        } else {
            $mform->addElement('static', 'nocar', '', get_string('nocar', 'local_ticketmanagement'));
        }

        $mform->addElement('header', 'addcarheader', get_string('addnewcar', 'local_ticketmanagement'));

        

        $mform->addElement('text', 'brand', get_string('brand', 'local_ticketmanagement'));
        $mform->setType('brand', PARAM_TEXT);

        $mform->addElement('text', 'model', get_string('model', 'local_ticketmanagement'));
        $mform->setType('model', PARAM_TEXT);

        $mform->addElement('text', 'color', get_string('color', 'local_ticketmanagement'));
        $mform->setType('color', PARAM_TEXT);

        $mform->addElement('text', 'platenumber', get_string('platenumber', 'local_ticketmanagement'));
        $mform->setType('platenumber', PARAM_TEXT);

        $mform->addElement('date_selector', 'delivery_date', get_string('delivery_date', 'local_ticketmanagement'),[]);
        $mform->addElement('date_selector', 'refund_date', get_string('refund_date', 'local_ticketmanagement'),['optional'=>true]);
        
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

        $data = $this->get_data();

        // Check if all three fields are either empty or contain only whitespace
        $allEmpty = empty(trim($data->model ?? '')) 
        && empty(trim($data->brand ?? '')) 
        && empty(trim($data->platenumber ?? ''))
        && empty(trim($data->color ?? ''));


        // If not all are empty, proceed with the insertion
        if (!$allEmpty) {
            // Insert a new car record
            $DB->insert_record('ticketmanagement_cars', [
                'userid' => $data->userid,
                'brand' => $data->brand,
                'model' => $data->model,
                'color' => $data->color,
                'platenumber' => $data->platenumber,
                'delivery_date' => $data->delivery_date,
                'refund_date' => $data->refund_date
            ]);

            return $this->get_data();
        }
        

        // Aquí puedes manejar lógica para edición y eliminación si se envían datos relacionados
        // por ejemplo, identificadores de familiares para editar o eliminar.
        
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