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
 * Block definition class for the block_charts_responsetime plugin.
 *
 * @package   block_charts_responsetime
 * @copyright Year, You Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_charts_responsetime extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('charts_responsetime', 'block_charts_responsetime');
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
        if (!has_capability('blocks/charts_responsetime:view', $this->context)) {
            return null; // Return nothing if the user doesn't have access
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->page->requires->css('/blocks/charts_responsetime/styles/styles.css');
        //$this->page->requires->js_init_code('var userId = ' . $USER->id . ';');
        $this->page->requires->js_call_amd('block_charts_responsetime/init', 'init');

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                                    INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                                    WHERE username=:username LIMIT 1", ['username'=>'logisticwebservice']);
        $token=$token->token;

        // Add logic here to define your template data or any other content.
        $data = [
            'token'=>$token
        ];

        $this->content->text = $OUTPUT->render_from_template('block_charts_responsetime/content', $data);

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