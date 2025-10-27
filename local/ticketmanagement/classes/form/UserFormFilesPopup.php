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

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php"); 


class UserFormFilesPopup extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $userid = $this->_ajaxformdata['userid'];
        
        // Hidden para que userid llegue en process_dynamic_submission
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        // Add a file manager element for uploading files
        $mform->addElement('filemanager', 'userfiles', get_string('attachedfiles', 'local_ticketmanagement'), null, $this->get_options());
        $mform->setType('userfiles', PARAM_RAW); // File manager uses PARAM_RAW
        $mform->addHelpButton('userfiles', 'attachedfiles', 'local_ticketmanagement');


        // Add action buttons (submit and cancel)
       // $this->add_action_buttons(true, get_string('savechanges')); 
        
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
        return [
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => -1,
            'accepted_types' => '*',
        ];

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
        $data = $this->get_data();

        // Acceder como objeto
        $userid = $data->userid;

        // Contexto de usuario válido
        $context = \context_user::instance($userid);

        // Guardar archivos en el filearea
        file_save_draft_area_files(
            $data->userfiles,       // draft itemid
            $context->id,           // contextid
            'local_ticketmanagement', // componente
            'userfiles',            // filearea
            $userid,                // itemid (lo usas como identificador)
            $this->get_options()
        );

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
        $userid = $this->_ajaxformdata['userid'];
        $context = \context_user::instance($userid);

        // Preparamos un draft area para el filemanager
        $draftitemid = file_get_submitted_draft_itemid('userfiles');
        file_prepare_draft_area(
            $draftitemid,                 // draft itemid
            $context->id,                 // contexto
            'local_ticketmanagement',     // componente
            'userfiles',                  // filearea
            $userid,                      // itemid
            $this->get_options()          // opciones de subida
        );

        // Pasamos los valores al formulario
        $data = new \stdClass();
        $data->userid = $userid;
        $data->userfiles = $draftitemid;

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
