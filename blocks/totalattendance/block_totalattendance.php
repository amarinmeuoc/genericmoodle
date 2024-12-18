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
 * Block definition class for the block_totalattendance plugin.
 *
 * @package   block_totalattendance
 * @copyright 2023, Alberto Marín <desarrollador@myhappycoding.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once(__DIR__.'/../../config.php');
 


class block_totalattendance extends block_base {
    
    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_totalattendance');
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        global $OUTPUT;
        require_login();
        $context=context_block::instance($this->instance->id);
        if (!has_capability('block/totalattendance:view',$context)){          
            $this->content->text= "<h1>Error: Access forbiden!!.</h1> <p>Contact with the admin for more information.</p>";          
            return;
        }
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->footer = '';
        $this->page->requires->js('/blocks/totalattendance/assets/progressbar.min.js',true);
        $this->page->requires->js('/blocks/totalattendance/assets/calculateAtt.js',true);
        $this->page->requires->css(new moodle_url('/blocks/totalattendance/assets/styles.css'));

        $totalAtt=0;
        $data = ['totalatt'=>$totalAtt]; 
        $this->content->text = $OUTPUT->render_from_template('block_totalattendance/content', $data);
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

    //It doesnt display header in the block
    public function hide_header() {
        return false;
    }
}
