<?php
namespace block_gantt_diagram\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class form_filter_controller extends \moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;
        $mform->_attributes['id']="gantt_customer_select";
        // Selector de proyecto
        $projects = $DB->get_records('customer', null, 'name', 'id, name');
        $projectoptions = array();
        foreach ($projects as $project) {
            $projectoptions[$project->id] = $project->name;
        }
        $mform->addElement('select', 'project', get_string('project', 'block_gantt_diagram'), $projectoptions);
        $mform->addRule('project', get_string('required'), 'required', null, 'client');

        // Selector de grupo (dependiente del proyecto seleccionado)
        // Seleccionar el primer elemento de $projectoptions
        $firstProjectId = array_key_first($projectoptions); // Obtiene la clave del primer elemento
        
        $groups = $DB->get_records('grouptrainee', ['customer'=>$firstProjectId, 'hidden'=>0], 'name', 'id, name');
        $groupoptions = array();
        foreach ($groups as $group) {
            $groupoptions[$group->id] = $group->name;
        }
        array_unshift($groupoptions,'All Groups');
        $mform->addElement('select', 'group', get_string('group', 'block_gantt_diagram'), $groupoptions);
        $mform->addRule('group', get_string('required'), 'required', null, 'client');

        $mform->addElement('button','reload',get_string('reload'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Aquí puedes añadir validaciones adicionales si es necesario
        return $errors;
    }
}