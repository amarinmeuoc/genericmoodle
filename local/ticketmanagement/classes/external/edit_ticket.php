<?php
namespace local_ticketmanagement\external;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use moodle_exception;
use stdClass;

require_once($CFG->libdir . '/filelib.php');

class edit_ticket extends \core_external\external_api {
    
    public static function execute_parameters() {
        return new external_function_parameters([
            'params' => new external_multiple_structure(
                new external_single_structure([
                    'ticketid' => new external_value(PARAM_TEXT, 'Ticket ID'),
                    'cancelled' => new external_value(PARAM_INT, 'Cancelled status (0 or 1)'),
                    'state' => new external_value(PARAM_TEXT, 'Current state of the ticket'),
                    'priority' => new external_value(PARAM_TEXT, 'Priority level of the ticket'),
                    'closed' => new external_value(PARAM_INT, 'Closed status (0 or 1)'),
                    'subcategory' => new external_value(PARAM_INT, 'Category ID'),
                    'saved_files_count' => new external_value(PARAM_INT, 'If negative files have been removed, if positive files have been added'),
                    'eventoCat' => new external_value(PARAM_TEXT, 'Event Category', VALUE_OPTIONAL, ''),
                    'eventoSubCat' => new external_value(PARAM_TEXT, 'Event SubCategory', VALUE_OPTIONAL, ''),
                    'eventoPriority' => new external_value(PARAM_TEXT, 'Event Priority', VALUE_OPTIONAL, ''),
                    'close' => new external_value(PARAM_INT, 'Close status (0 or 1)', VALUE_OPTIONAL, 0),
                    'userid' => new external_value(PARAM_INT, 'User ID of the ticket owner', VALUE_OPTIONAL, 0),
                    'haschanges' => new external_value(PARAM_INT, 'Indicates if there are changes to the ticket', VALUE_OPTIONAL, 0)
                ])
            ) 
        ]);
    }

    public static function execute($params) {
        global $DB, $USER;
        
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        $newTicket = $request['params'][0];
        $ticketid = $newTicket['ticketid'] ?? null;
        $userid = $newTicket['userid'] ?? $USER->id;

        // Check if the ticket exists
        if (!$DB->record_exists('ticket', ['id' => $ticketid])) {
            throw new moodle_exception('Invalid ticket ID');
        }

        $oldTicket = $DB->get_record('ticket', ['id' => $ticketid], '*', MUST_EXIST);

        $selectedSubcategory = $newTicket['subcategory'] ?? null;

        // Check if there are changes to the ticket
        $haschanges = $newTicket['haschanges'] ?? false;
        
        if ($haschanges === 0) {

            //if selectedSubcategory is different from the old one, then has changes is true
            if ($selectedSubcategory != $oldTicket->subcategoryid) {
                $haschanges = 1;
            } else {
                $haschanges = 0;
            }
            if ($haschanges === 0) {
                return [
                    'ticket' => [
                        'ticketid' => $newTicket['ticketid'],
                        'state' => $newTicket['state'],
                        'priority' => $newTicket['priority']
                    ],
                    'success' => false
                ];
            }
        }

        // Prepare the update record
        $record = new stdClass();
        $record->id = $ticketid;
        $record->subcategoryid = $newTicket['subcategory'];
        $record->priority = $newTicket['priority'];
        
        // Handle ticket state
        if (!empty($newTicket['closed'])) {
            $record->state = 'Closed';
        } elseif (!empty($newTicket['cancelled'])) {
            $record->state = 'Cancelled';
        } else {
            $record->state = $newTicket['state'];
        }

        try {
            $DB->update_record('ticket', $record);
            
            // Get all involved users
            $editor = $DB->get_record('user', ['id' => $userid]);
            $assigned_user = $DB->get_record('user', ['id' => $oldTicket->assigned]);
            $ticket_owner = $DB->get_record('user', ['id' => $oldTicket->userid]);
            
            // Prepare action message
            $messages = [];
            $saved_files_count = $newTicket['saved_files_count'] ?? 0;
            
            if ($userid != $oldTicket->assigned) {
                $messages[] = "Updated by: " . fullname($editor);
            } else {
                $messages[] = "Ticket updated";
            }

            if (!empty($newTicket['eventoCat'])) {
                $messages[] = "New Category: " . $newTicket['eventoCat'];
            }
            if (!empty($newTicket['eventoSubCat'])) {
                $messages[] = "New SubCategory: " . $newTicket['eventoSubCat'];
            }
            if (!empty($newTicket['eventoPriority'])) {
                $messages[] = "New Priority: " . $newTicket['eventoPriority'];
            }
            if ($saved_files_count > 0) {
                $messages[] = "Files added: " . $saved_files_count;
            } elseif ($saved_files_count < 0) {
                $messages[] = "Files removed: " . abs($saved_files_count);
            }

            if ($record->state === 'Cancelled') {
                $messages = ["Ticket Cancelled"];
            } elseif ($record->state === 'Closed') {
                $messages = ["Ticket Closed"];
            }

            $finalMessage = count($messages) > 1 ? implode(', ', $messages) : $messages[0];
            
            // Log the action
            $DB->insert_record('ticket_action', [
                'action' => $finalMessage,
                'dateaction' => time(),
                'userid' => $assigned_user->id,
                'ticketid' => $ticketid
            ]);

            // Prepare notification data
            $ticket_url = new \moodle_url('/local/ticketmanagement');
            $ticket_url->set_anchor($ticketid);
            $ticket_link = \html_writer::link($ticket_url, get_string('viewticket', 'local_ticketmanagement'));
            
            // 1. Notify ticket owner (if editor is not the owner) and is not the webservice user
            if ($userid != $oldTicket->userid && $oldTicket->userid != $USER->id) {
                $message = get_string('owner_notification', 'local_ticketmanagement', [
                    'ticketid' => $ticketid,
                    'editor' => fullname($editor),
                    'changes' => $finalMessage,
                    'link' => $ticket_link
                ]);
                
                                
                self::send_notification(
                    $oldTicket->userid,
                    $userid,
                    get_string('ticket_updated', 'local_ticketmanagement', $ticketid),
                    $message
                );
            }
            
            // 2. Notify assigned user (if editor is not the assigned user)
            if ($userid != $oldTicket->assigned) {
                $message = get_string('assigned_notification', 'local_ticketmanagement', [
                    'ticketid' => $ticketid,
                    'editor' => fullname($editor),
                    'changes' => $finalMessage,
                    'link' => $ticket_link
                ]);
                
                self::send_notification(
                    $oldTicket->assigned,
                    $userid,
                    get_string('ticket_updated', 'local_ticketmanagement', $ticketid),
                    $message
                );
            }
            
            // 3. Special case: If assigned to webservice user, notify logistics team
                        
            if ($oldTicket->assigned == $USER->id) {
                $logistics_users = $DB->get_records_sql("
                    SELECT u.id 
                    FROM {user} u
                    JOIN {role_assignments} ra ON ra.userid = u.id
                    JOIN {role} r ON r.id = ra.roleid
                    WHERE r.shortname = 'logistic' 
                    AND u.deleted = 0 
                    AND u.suspended = 0
                    AND u.id NOT IN (?, ?)
                ", [$userid, $oldTicket->userid]);
                
                if ($logistics_users) {
                    $message = get_string('logistics_notification', 'local_ticketmanagement', [
                        'ticketid' => $ticketid,
                        'editor' => fullname($editor),
                        'changes' => $finalMessage,
                        'link' => $ticket_link
                    ]);
                    
                    foreach ($logistics_users as $user) {
                        self::send_notification(
                            $user->id,
                            $userid,
                            get_string('ticket_needs_reassignment', 'local_ticketmanagement'),
                            $message
                        );
                    }
                }
            }

            return [
                'ticket' => [
                    'ticketid' => $ticketid,
                    'state' => $record->state,
                    'priority' => $record->priority
                ],
                'success' => true
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send both email and notification to a user
     */
    private static function send_notification($to, $from, $subject, $message) {    
        // Notification task
        $notification_task = new \local_ticketmanagement\task\add_notification_task();
        $notification_task->set_custom_data([
            'to' => $to,
            'subject' => $subject,
            'message_plain' => strip_tags($message),
            'message_html' => $message,
            'from' => $from
        ]);
        
        \core\task\manager::queue_adhoc_task($notification_task);

        // Email task
        $email_task = new \local_ticketmanagement\task\send_email_task();
        $email_task->set_custom_data([
            'to' => $to,
            'subject' => $subject,
            'message_plain' => strip_tags($message),
            'message_html' => $message,
            'from' => $from
        ]);
        \core\task\manager::queue_adhoc_task($email_task);
    }

    public static function execute_returns() {
        return new external_single_structure([
            'ticket' => new external_single_structure([
                'ticketid' => new external_value(PARAM_TEXT, 'Ticket ID'),
                'state' => new external_value(PARAM_TEXT, 'Current state of the ticket'),
                'priority' => new external_value(PARAM_TEXT, 'Priority level of the ticket'),
            ]),
            'success' => new external_value(PARAM_BOOL, 'Status of the ticket update'),
            'message' => new external_value(PARAM_TEXT, 'Error message if update fails', VALUE_OPTIONAL),
        ]);
    }
}