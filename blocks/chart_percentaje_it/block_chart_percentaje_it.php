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
 * Block definition class for the block_chart_percentaje_it plugin.
 *
 * @package   block_chart_percentaje_it
 * @copyright Year, You Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_chart_percentaje_it extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('chart_percentaje_it', 'block_chart_percentaje_it');
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        global $OUTPUT,$DB;

        if ($this->content !== null) {
            return $this->content;
        }

        // Check if the user has the required capability
        if (!has_capability('blocks/chart_percentaje_it:view', $this->context)) {
            return null; // Return nothing if the user doesn't have access
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->page->requires->css('/blocks/chart_percentaje_it/styles/styles.css');
        //$this->page->requires->js_init_code('var userId = ' . $USER->id . ';');
        $this->page->requires->js_call_amd('block_chart_percentaje_it/init', 'init');

        //Se obtiene el token del usuario y se guarda en un campo oculto
                $token = $DB->get_record_sql("
            SELECT t.token
            FROM {external_tokens} t
            INNER JOIN {external_services} s ON s.id = t.externalserviceid
            INNER JOIN {user} u ON u.id = t.userid
            WHERE u.username = :username
            AND s.shortname = :servicename
            LIMIT 1",
        [
            'username' => 'logisticwebservice',
            'servicename' => 'chart_percentaje_it_navantiaservices'
        ]);
        
        $token=$token->token;

        // Add logic here to define your template data or any other content.
        $data = [
            'token'=>$token
        ];

        $this->content->text = $OUTPUT->render_from_template('block_chart_percentaje_it/content', $data);

        //$this->page->requires->js('/blocks/graphical_events/js/init.js', false);

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