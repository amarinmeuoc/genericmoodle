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
        

        $ticketid=$this->_ajaxformdata['num_ticket'] ?? '';
        $role=$this->_ajaxformdata['role'] ?? '';

        $mform->addElement('static', 'ticketid', get_string('ticketid', 'local_ticketmanagement'), $ticketid,['data-name' => $ticketid]);
        
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
        $userrole = $USER->profile['role'] ?? '';
        if (preg_match('/^(logistic|manager)$/i', $userrole)) {
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
    global $USER;
    

    try {
        // Load custom profile fields
        profile_load_custom_fields($USER);
        
        $requiredroles = ['logistic', 'manager', 'student'];
        $userrole = strtolower($USER->profile['role'] ?? '');
        
        if (!in_array($userrole, $requiredroles)) {
            throw new \moodle_exception('nopermission', 'local_ticketmanagement');
        }
        
        $ticketid=$this->_ajaxformdata['num_ticket']??$this->_ajaxformdata['hiddenticketid'];

        if (empty($ticketid)) {
            throw new \moodle_exception('missingticketid', 'local_ticketmanagement');
        }
        
    } catch (\Exception $e) {
        error_log("Form access check failed: " . $e->getMessage());
        throw $e; // Re-throw after logging
    }
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
    // Start output buffering to catch any stray output
    ob_start();
    
    try {
        global $DB, $USER;

        $data = $this->get_data();
        
        // Debugging: Log received data
        error_log("Form submission data: " . print_r($data, true));

        if (!$data || !is_object($data)) {
            throw new \Exception('Invalid form data received');
        }

        // Validate required fields
        $description = trim($data->description ?? '');
        if (empty($description)) {
            throw new \Exception('Description is required');
        }

        $user= $DB->get_record('user', ['id' => $data->userid], 'id, firstname, lastname', IGNORE_MISSING);

        $description .= ' - ' . $user->firstname . ', ' . $user->lastname;

        $ticket= $DB->get_record('ticket', ['id' => $data->hiddenticketid], 'id, state, assigned', IGNORE_MISSING);
        if (!$ticket) {
            throw new \Exception('Ticket not found');
        }

        // Prepare the record to be inserted

        $record = new \stdClass();
        $record->action = $description;
        $record->internal = $data->internal ?? '';
        $record->dateaction = $data->updated ?? time();
        $record->userid = $ticket->assigned ?? $USER->id;
        $record->ticketid = $data->hiddenticketid ?? null;

        if (empty($record->userid) || empty($record->ticketid)) {
            throw new \Exception('Missing required identifiers');
        }

        // Debugging: Log record being inserted
        error_log("Preparing to insert record: " . print_r($record, true));

        $transaction = $DB->start_delegated_transaction();
        
        try {
            $id = $DB->insert_record('ticket_action', $record);
            $transaction->allow_commit();
            
            // Debugging: Log success
            error_log("Record inserted successfully with ID: $id");

            //Notify the user about the successful action
            $this->notify_user();
            
            return [
                'status' => 'success',
                'message' => 'Action added successfully',
                'ticketid' => $record->ticketid ?? '',
                'newid' => $id ?? ''
            ];
            
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }

    } catch (\Exception $e) {
        // Log the full error
        error_log("Form submission error: " . $e->getMessage());
        
        // Clean any output buffers
        ob_end_clean();
        
        return [
            'status' => 'error',
            'message' => 'Error processing form: ' . $e->getMessage()
        ];
    } finally {
        // Ensure no output remains in buffer
        ob_end_clean();
    }
}

//Notify the user about the successful action
private function notify_user() {
    global $DB, $USER;

    $ticketid = $this->_ajaxformdata['num_ticket'] ?? $this->_ajaxformdata['hiddenticketid'];
    if (!$ticketid) {
        throw new \moodle_exception('missingticketid', 'local_ticketmanagement');
    }

    // Get the ticket details
    $ticket = $DB->get_record('ticket', ['id' => $ticketid], '*', IGNORE_MISSING);
    if (!$ticket) {
        throw new \moodle_exception('ticketnotfound', 'local_ticketmanagement');
    }

    // Notify the user about the action
    // This could be an email, a message, or any other notification method
    // For simplicity, we will just log it here
    error_log("User {$USER->id} added an action to ticket {$ticket->id}");

    $ticket_url = new \moodle_url('/local/ticketmanagement');
        $ticket_url->set_anchor($ticketid);
        $ticket_link = \html_writer::link($ticket_url, 'Ticket management');
        $ticket_link_user = \html_writer::link($ticket_url, 'Tickets Status');
        
        
        //Send email ticket created
        $to=$DB->get_record('user',['id'=>$ticket->userid]);
        $to=$to->id;
        
        $message = "<p>You've got a new Notification. Your ticket has been updated.</p>";
        $messageHTML = "
                <h1 style='background-color:#0f6cbf; color: white; padding: .3em;'>Ticket updated</h1>
                <p style='font-size:large;'>Please, go to the {$ticket_link_user} and check the ticket with ID: <strong>{$ticket->id}</strong>.</p>
                <p style='font-size:large;'>Your ticket has been updated with a new action.</p>
                <p style='font-size:large;'>Thank you for using our service.</p>
                <p style='font-size:large;'><strong>Support Team</strong></p>
            ";
        $subject="Ticket {$ticket->id} requires your attention";

        //Send email and notification to the person in charge of the ticket
        $task = new \local_ticketmanagement\task\send_email_task();
        $task->set_custom_data([
            'to' => $to,
            'subject' => $subject,
            'message_plain' => $message,
            'message_html' => $messageHTML,
            'from' => $ticket->assigned,
        ]);
        \core\task\manager::queue_adhoc_task($task);

        $task = new \local_ticketmanagement\task\add_notification_task();
        $task->set_custom_data([
            'to' => $to,
            'subject' => $subject,
            'message_plain' => $message,
            'message_html' => $messageHTML,
            'from' => $USER->id,
        ]);
        \core\task\manager::queue_adhoc_task($task);

        // Notify the assigned user about the action in case the user is not a logistic or manager
        if (!preg_match('/^(logistic|manager)$/i', $USER->profile['role'] ?? '')) {
            $message = "<p>You've got a new Notification. A new action has been added to the ticket ID: {$ticket->id}.</p>";
            $messageHTML = "
                <h1 style='background-color:#0f6cbf; color: white; padding: .3em;'>Ticket updated with a new action</h1>
                <p style='font-size:large;'>Please, go to the {$ticket_link} and check the ticket with ID: <strong>{$ticket->id}</strong>.</p>
                <p style='font-size:large;'>A new action has been added to the ticket.</p>
                <p style='font-size:large;'>Thank you for using our service.</p>
                <p style='font-size:large;'><strong>Support Team</strong></p>
            ";
            $subject = "New action added in Ticket {$ticket->id}.";

            //Send email and notification to the assigned user
            $task = new \local_ticketmanagement\task\send_email_task();
            $task->set_custom_data([
                'to' => $ticket->assigned,
                'subject' => $subject,
                'message_plain' => $message,
                'message_html' => $messageHTML,
                'from' => $USER->id,
            ]);
            \core\task\manager::queue_adhoc_task($task);

            $task = new \local_ticketmanagement\task\add_notification_task();
            $task->set_custom_data([
                'to' => $ticket->assigned,
                'subject' => $subject,
                'message_plain' => $message,
                'message_html' => $messageHTML,
                'from' => $USER->id,
            ]);
            \core\task\manager::queue_adhoc_task($task);
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
    global $DB, $USER;
    
    try {
        // Load custom profile fields
        profile_load_custom_fields($USER);
        $userrole = $USER->profile['role'] ?? '';
        
        $ticketid = $this->_ajaxformdata['num_ticket'] ?? null;
        if (!$ticketid) {
            throw new \moodle_exception('missingticketid', 'local_ticketmanagement');
        }

        $mform = $this->_form;
        
        // Start actions container
        $html = '<div class="ticket-actions-container">';
        $html .= '<div class="actions-header">';
        $html .= '<div class="date"><strong>Date</strong></div>';
        $html .= '<div class="description"><strong>Description</strong></div>';
        $html .= '<div class="addedby"><strong>Assigned to</strong></div>';
        $html .= '</div>';
        
        $actions = $DB->get_records('ticket_action', ['ticketid' => $ticketid], 'dateaction ASC');
        
        foreach ($actions as $action) {
            $user = $DB->get_record('user', ['id' => $action->userid], 'firstname, lastname', IGNORE_MISSING);
            $formatted_date = userdate($action->dateaction, '%d-%m-%Y %H:%M');
            
            $html .= '<div class="action-item">';
            $html .= '<div class="date">' . $formatted_date . '</div>';
            
            $description = '<div class="description">' . s($action->action);
            
            if (!preg_match('/^(student|observer)$/i', $userrole) && !empty($action->internal)) {
                $description .= '<span class="hiddenmessage" data-tooltip="' . s($action->internal) . '">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </span>';
            }
            
            $description .= '</div>';
            $html .= $description;
            
            $username = preg_match('/webservice/i', $user->firstname) 
                ? 'Waiting for a controller' 
                : fullname($user);
                
            $html .= '<div class="addedby">' . $username . '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $mform->addElement('html', $html);
        
    } catch (\Exception $e) {
        $mform = $this->_form;
        // Log error and show user-friendly message
        error_log("Error loading action form data: " . $e->getMessage());
        $mform->addElement('html', '<div class="alert alert-danger">Error loading actions</div>');
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
        global $USER;
        return new \moodle_url('/user/profile.php',['id'=>$USER->id]);
    }


}