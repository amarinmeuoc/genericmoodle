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


class FineFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $mform->setAttributes(['id' => 'car_form']);
    
        $userid = $this->_ajaxformdata['userid'];
        $carid = $this->_ajaxformdata['carid'];
    
        $mform->addElement('hidden', 'carid', $carid);
        $mform->setType('carid', PARAM_INT);
        
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
    
        //Show all fines of the selected user and car in a grid
        $fines = $DB->get_records('ticketmanagement_fines', ['userid' => $userid, 'carid'=>$carid]); 

        if ($fines) {
            $table_html = '<div class="table-responsive-xl">
            <table class="table generaltable fine-table">
                <thead>
                    <tr>
                        <th>' . get_string('ticket', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('expiration_date', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('status', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('reminder', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('save', 'local_ticketmanagement') . '</th>
                        <th>' . get_string('remove', 'local_ticketmanagement') . '</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($fines as $fine) {
                $expiration_date=(!is_null($fine->expiration_date) && $fine->expiration_date!=='0')?
                                userdate($fine->expiration_date, get_string('strftimedate', 'langconfig')):'';
                $table_html .= '<tr id="fine_'.$fine->id.'">
                    
                    <td><input class="form-control" type="text" name="tefineticket" value="' . s($fine->ticketid) . '"></td>
                    <td>' . $expiration_date . '</td>
                    <td> 
                    <select class="custom-select" name="selstatus">
                        <option value="pending" ' . $this->isSelected($fine->status, 'not-paid') . '>Pending</option>
                        <option value="paid" ' . $this->isSelected($fine->status, 'paid') . '>Paid</option>
                        <option value="cancelled" ' . $this->isSelected($fine->status, 'cancelled') . '>Cancelled</option>
                    </select>
                    </td>
                    <td><label><input type="checkbox" name="reminder" value="active" ' . $this->isChecked($fine->reminder, 1) . '>Activate reminder</label></td>
                    <td>
                        <button type="button" class="edit-fine btn btn-secondary" data-id="' . $fine->id . '">' . get_string('save', 'local_ticketmanagement') . '</button>
                    </td>
                    <td>
                        <button type="button" class="remove-fine btn btn-secondary" data-id="' . $fine->id . '">' . get_string('remove', 'local_ticketmanagement') . '</button>
                    </td>
                                        
                </tr>';
            }
        
            $table_html .= '</tbody></table></div>';
        
            $mform->addElement('html', $table_html);
        } else {
            $mform->addElement('static', 'nofines', '', get_string('nofines', 'local_ticketmanagement'));
        }

        $mform->addElement('header', 'addfineheader', get_string('addnewfine', 'local_ticketmanagement'));

        $mform->addElement('text', 'ticketnumber', get_string('ticketnumber', 'local_ticketmanagement'));
        $mform->setType('ticketnumber', PARAM_TEXT);

        $mform->addElement('select', 'status', get_string('status', 'local_ticketmanagement'),['pending'=>'Pending','paid'=>'Paid','cancelled'=>'Cancelled']);
        $mform->setType('status', PARAM_TEXT);
        $mform->setDefault('status','pending');

        $mform->addElement('date_selector', 'expiration_date', get_string('expiration_date', 'local_ticketmanagement'),[]);
        $mform->addElement('date_selector', 'payment_date', get_string('payment_date', 'local_ticketmanagement'),['optional'=>true]);
        
         //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                                    INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                                    WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
        $token=$token->token;

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token',PARAM_TEXT);   

        
        

        
    }

    protected function isSelected($current, $expected) {
        return $current == $expected ? 'selected' : '';
    }

    protected function isChecked($current, $expected) {
        return $current == $expected ? 'checked' : '';
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

        if (!$this->is_submitted()) {
            return null;
        }
        $data = $this->get_data();
        
    
    
        if ($data) {
            // Check if all three fields are either empty or contain only whitespace
            $allEmpty = empty(trim($data->ticketnumber ?? '')) ;

            // If not all are empty, proceed with the insertion
            if (!$allEmpty) {
                // Insert a new car record
                $DB->insert_record('ticketmanagement_fines', [
                    'userid' => $data->userid,
                    'carid' => $data->carid,
                    'ticketid' => trim($data->ticketnumber),
                    'status' => $data->status,
                    'expiration_date' => $data->expiration_date,
                    'payment_date' => $data->payment_date,
                    
                ]);

                return $this->get_data();
            }
        return null;
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