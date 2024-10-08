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

class TicketFormPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        $mform = $this->_form;

        $ticketid=$this->_ajaxformdata['num_ticket'];

        $mform->addElement('static', 'ticketid', get_string('ticketid', 'local_ticketmanagement'), $ticketid);

        // Priority dropdown to allow the user to change the priority
        $priorityoptions = [
            'Low' => get_string('low', 'local_ticketmanagement'),
            'Medium' => get_string('medium', 'local_ticketmanagement'),
            'High' => get_string('high', 'local_ticketmanagement'),
        ];
        $mform->addElement('select', 'priority', get_string('priority', 'local_ticketmanagement'), $priorityoptions);
        $mform->setDefault('priority', 'Medium');  // Default priority value

        // Add any other fields as necessary...

        
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
        
        return $this->get_data();
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
        global $USER;
        return new \moodle_url('/user/profile.php',['id'=>$USER->id]);
    }


   

    

}