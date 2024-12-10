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

class AssignmentFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        

        global $DB;

        $mform = $this->_form;

        $ticketid = $this->_ajaxformdata['num_ticket'];
        $mform->addElement('static', 'ticketidtitle', get_string('ticketid', 'local_ticketmanagement'), $ticketid);
        $mform->addElement('hidden',  'ticketid',  $ticketid);

        // Recuperar usuarios con el rol 'logistic'
        $fieldname = 'role'; // El nombre corto del campo
        $fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => $fieldname]);

        $logisti_users = [];
        if ($fieldid) {
            $users = $DB->get_records_sql(
                "SELECT u.*
                 FROM {user} u
                 JOIN {user_info_data} d ON u.id = d.userid
                 WHERE d.fieldid = :fieldid
                 AND d.data = :role",
                ['fieldid' => $fieldid, 'role' => 'logistic']
            );

            foreach ($users as $user) {
                $logisti_users[$user->id] = "{$user->firstname} {$user->lastname}";
            }
        }

        // Añadir el select con los usuarios de logística
        $mform->addElement('select', 'selassignment', get_string('assignment', 'local_ticketmanagement'), $logisti_users);
 
         
    }

    

    // This method processes the submitted data
    public function process_data($data) {
        

        // Optionally, handle any other form processing logic here (e.g., sending emails)

        // Close or refresh the modal after processing
        $this->close();
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

        global $DB,$USER;

        // Obtener los datos enviados del formulario
        $data = $this->get_data();
    
        // Verificar que los datos estén disponibles y obtener el ID del usuario y el ID del ticket
        if ($data) {
            $ticketId = $data->ticketid;
            $selectedUserId = $data->selassignment;
    
            // Crear el objeto de registro para la actualización
            $record = new \stdClass();
            $record->id = $ticketId;  // ID del ticket
            $record->assigned = $selectedUserId;  // ID del usuario seleccionado
            $record->state = 'Assigned';

            //Nombre y apellidos del controlador
            $user=$DB->get_record('user',['id'=>$selectedUserId], 'firstname,lastname');
    
            // Actualizar la tabla 'ticket'
            try {
                $DB->update_record('ticket', $record);

                $userid=$selectedUserId;
                $dateaction=time();
                $message=$USER->firstname.", has assigned the ticket to: ". $user->firstname .", ".$user->lastname;

                $DB->execute("INSERT INTO {ticket_action} (action, dateaction, userid, ticketid)
                        VALUES (?,?,?,?)",
                        array($message,$dateaction,$userid,$ticketId));
            } catch (\Exception $e) {
                // Maneja cualquier error en la actualización
                throw new \moodle_exception('updateerror', 'local_ticketmanagement', '', null, $e->getMessage());
            }
    
            // Puedes devolver un valor de éxito o mensaje de confirmación
            return ['success' => true, 'ticket'=>['id'=>$record->id, 'state'=>'Assigned', 'user'=>"{$user->firstname}, {$user->lastname}"]];
        } else {
            return ['success' => false, 'message' => 'No se recibieron datos válidos.'];
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