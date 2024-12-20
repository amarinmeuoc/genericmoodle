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

class ActionsFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB,$USER;

        $mform = $this->_form;
        

        $ticketid=$this->_ajaxformdata['num_ticket'];
        $role=$this->_ajaxformdata['role'];

        $mform->addElement('static', 'ticketid', get_string('ticketid', 'local_ticketmanagement'), $ticketid);

        $mform->addElement('hidden',  'hiddenticketid',  $ticketid);

        $ticket = $DB->get_record('ticket',['id'=>$ticketid],'state,assigned');
        
        $actions = $DB->get_records('ticket_action', ['ticketid' => $ticketid], 'dateaction DESC', '*', 0, 1);
        $action=reset($actions);
        $updated=time();

        $mform->addElement('hidden','updated',$updated);
        $mform->addElement('hidden',  'userid',  ($role==='student')?$USER->id:$ticket->assigned);
        $mform->addElement('hidden','state',$ticket->state);
        
        // Agregar cada acción en un contenedor HTML con los detalles correspondientes
        
        $mform->addElement('text','description','Add a new action:');

        

        profile_load_custom_fields($USER);
        if (preg_match('/^(logistic|manager)$/i', $USER->profile['role'])) {
            $mform->addElement('text','internal','Internal message:');
            $mform->addElement('button', 'boExcel', 'Export to Excel');
        }
   
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
        
        global $DB;

        // Obtener los datos enviados del formulario
        $data = $this->get_data();
    
        // Verificar que los datos estén disponibles y obtener el ID del usuario y el ID del ticket
        if ($data && !empty(trim($data->description))) {
            $DB->execute("INSERT INTO {ticket_action} (action, internal, dateaction, userid, ticketid)
                VALUES (?,?,?,?,?)",
                array($data->description,$data->internal,$data->updated,$data->userid,$data->hiddenticketid));
        }

        return $data;
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
        global $DB, $USER;
        $ticketid=$this->_ajaxformdata['num_ticket'];
        $role=$this->_ajaxformdata['role'];


        $actions=$DB->get_records('ticket_action',['ticketid'=>$ticketid],'dateaction ASC','*');

        $mform = $this->_form;

        
        $mform->addElement('html', '<div class="qheader">');
        $mform->addElement('html','<div class="action">');
        $mform->addElement('html', '<div class="date"><strong> Date </strong></div>');
        $mform->addElement('html', '<div class="description"><strong> Description </strong></div>');
        $mform->addElement('html', '<div class="addby"><strong> Assigned to: </strong></div>');
        $mform->addElement('html','</div>');
    foreach ($actions as $action) {
        $user=$DB->get_record('user', ['id'=>$action->userid], 'firstname,lastname', IGNORE_MISSING);
        $formatted_date = userdate($action->dateaction, '%d-%m-%Y %H:%M');
        // Agregar cada acción en un contenedor HTML con los detalles correspondientes
        $mform->addElement('html', '<div id="' . $action->id . '" class="action">');
        $mform->addElement('html', '<div class="date"><strong>' . $formatted_date . '</strong></div>');
        // Crea el campo de descripción
$description = '<div class="description">';
$description .= $action->action;  // Esto es el texto de la descripción

// Si no es un estudiante ni un observador, agrega el valor de 'internal' en un tooltip
if (!preg_match('/^(student|observer)$/i', $USER->profile['role'])) {
    if ($action->internal){
            $description .= '<span class="hiddenmessage" data-tooltip="' . $action->internal . '">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
        </span>';
    }
    
}

$description .= '</div>';

// Añadir el campo de descripción al formulario
$mform->addElement('html', $description);
        
        if (preg_match('/webservice/i',$user->firstname)){
            $user->firstname='Waiting for a controller';
        }

        $mform->addElement('html', '<div class="addedby">' .$user->firstname . '</div>');
        $mform->addElement('html', '</div>');
    }
    $mform->addElement('html', '</div>');



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
        global $USER;
        return new \moodle_url('/user/profile.php',['id'=>$USER->id]);
    }


}