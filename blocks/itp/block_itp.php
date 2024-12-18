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
 * Block definition class for the block_itp plugin.
 *
 * @package   block_itp
 * @copyright 2024, Alberto Marín <albertomarinmendoza@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_itp extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_itp');
        
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        global $OUTPUT, $USER;
        require_login();

        if ($this->content !== null) {
            return $this->content;
        }

        $context=context_block::instance($this->instance->id);
        if (!has_capability('block/itp:view',$context)){          
            $this->content->text= "<h1>Error: Access forbidden!!.</h1> <p>Contact with the admin for more information.</p>";          
            return;
        }

        //Cargar los campos personalizados
        profile_load_custom_fields($USER);

        // Acceder al valor del campo personalizado role
        $role = $USER->profile['role'];

        //Por defecto se carga el formulario de estudiante
        $mform=new \block_itp\form\filteritpform();
        
        if (preg_match('/(controller|manager|logistic)/i', $role)) {
            $mform=new \block_itp\form\filteritpform_controller();
            $this->page->requires->js_call_amd('block_itp/init_con', 'loadITP');
        } elseif (preg_match('/observer/i', $role)) {
            $mform=new \block_itp\form\filteritpform_observer();
            $this->page->requires->js_call_amd('block_itp/init_obv', 'loadITP');
        } else {
            $mform=new \block_itp\form\filteritpform();
            $this->page->requires->js_call_amd('block_itp/init', 'loadITP');
        }

        $toform=null;
        
        // Set anydefault data (if any).
        $mform->set_data($toform);

        // Display the form.
        $form_html = $mform->render();
        
        
        
        
        
        $this->page->requires->css(new moodle_url('/blocks/itp/css/styles.css'));

        $this->content = new stdClass();
        $this->content->footer = '';

        $data = [ 
            'form' => $form_html,
        ];


        $this->content->text = $OUTPUT->render_from_template('block_itp/itp', $data);

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

    public function hide_header(){
        return false;
    }

    public function has_config(){
        return true;
    }
}
