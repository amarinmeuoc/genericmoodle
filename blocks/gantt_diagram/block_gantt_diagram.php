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
 * Block definition class for the block_gantt_diagram plugin.
 *
 * @package   block_gantt_diagram
 * @copyright 2025, Alberto Marín <albertomarinmendoza@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_gantt_diagram extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_gantt_diagram');
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        global $OUTPUT,$DB,$USER;

        if ($this->content !== null) {
            return $this->content;
        }

        //Cargar los campos personalizados
        profile_load_custom_fields($USER);

        // Acceder al valor del campo personalizado role
        $role = $USER->profile['role'];

       
        //Carga de formulario 
        if (preg_match('/(controller|manager|logistic)/i', $role)) {
            $mform=new \block_gantt_diagram\form\form_filter_controller();
        } else {
            $mform=new \block_gantt_diagram\form\form_filter_observer();
        } 

        $toform=null;
        
        // Set anydefault data (if any).
        $mform->set_data($toform);

        // Display the form.
        $form_html = $mform->render();
        

        $this->content = new stdClass();
        $this->content->footer = '';

        //Se obtiene el token del usuario y se guarda en un campo oculto
        $token=$DB->get_record_sql("SELECT token FROM mdl_external_tokens 
                                    INNER JOIN mdl_user ON mdl_user.id=mdl_external_tokens.userid
                                    WHERE username=:username LIMIT 1", ['username'=>'webserviceuser']);
        $token=$token->token;

        $this->page->requires->css('/blocks/gantt_diagram/styles/frappe-gantt.min.css');
        $this->page->requires->js_call_amd('block_gantt_diagram/init', 'init',[$role,$token]);
        //$this->page->requires->js_call_amd('block_graphical_events/frappe_gantt_module', 'init', [$jsondata]);

        

        // Add logic here to define your template data or any other content.
        $data = [
            'form'=>$form_html,
        ];

        $this->content->text = $OUTPUT->render_from_template('block_gantt_diagram/content', $data);

        

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