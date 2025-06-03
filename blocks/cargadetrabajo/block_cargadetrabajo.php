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
 * Block definition class for the block_cargadetrabajo plugin.
 *
 * @package   block_cargadetrabajo
 * @copyright Year, You Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_cargadetrabajo extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('cargadetrabajo', 'block_cargadetrabajo');
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        global $OUTPUT, $USER, $DB;

        $context=context_block::instance($this->instance->id);
        if (!has_capability('block/cargadetrabajo:view',$context)){          
            return null;
        }
    
        $this->content = new stdClass();
        $this->content->footer = '';
    
        // Get all users with 'logistic' role
        $logisticUsers = $DB->get_records_sql("
            SELECT u.id, u.firstname, u.lastname
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = ?
            WHERE r.shortname = 'logistic'
        ", [CONTEXT_SYSTEM]);
    
        $userTicketData = [];
        $assignedCounts = [];
    
        foreach ($logisticUsers as $user) {
            // Count tickets by state for this user
            $ticketCounts = $DB->get_records_sql("
                SELECT state, COUNT(*) as count
                FROM {ticket}
                WHERE assigned = ?
                GROUP BY state
            ", [$user->id]);
    
            // Initialize counts
            $counts = [
                'Assigned' => 0,
                'Closed' => 0,
                'Cancelled' => 0,
            ];
    
            // Update counts based on query results
            foreach ($ticketCounts as $state => $record) {
                $counts[$state] = $record->count;
            }

            $assignedCount = $counts['Assigned'];
            $assignedCounts[] = $assignedCount;

            $user = \core_user::get_user($user->id);
            $userTicketData[] = [
                'userid' => $user->id,
                'fullname' => fullname($user),
                'assigned_count' => $counts['Assigned'],
                'closed_count' => $counts['Closed'],
                'cancelled_count' => $counts['Cancelled'],
                'total' => array_sum($counts)
            ];
        }

        // Find max and min assigned counts
        $maxAssigned = !empty($assignedCounts) ? max($assignedCounts) : 0;
        $minAssigned = !empty($assignedCounts) ? min($assignedCounts) : 0;
    
        // Prepare data for template
        $data = [
            'users' => array_map(function($user) use ($maxAssigned, $minAssigned) {
                $user['is_max'] = ($user['assigned_count'] == $maxAssigned && $maxAssigned > 0);
                $user['is_min'] = ($user['assigned_count'] == $minAssigned && $minAssigned < $maxAssigned);
                return $user;
            }, $userTicketData),
            'has_users' => !empty($userTicketData),
            'max_assigned' => $maxAssigned,
            'min_assigned' => $minAssigned
        ];
    
        $this->content->text = $OUTPUT->render_from_template('block_cargadetrabajo/content', $data);
    
        return $this->content;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => true,
            'mod' => false,
            'my' => true,
        ];
    }
}