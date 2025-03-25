<?php
namespace block_gantt_diagram\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class form_filter_observer extends \moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Selector de grupo
        $groups = $DB->get_records('grouptrainee', null, 'name', 'id, name');
        $groupoptions = array();
        foreach ($groups as $group) {
            $groupoptions[$group->id] = $group->name;
        }
        $mform->addElement('select', 'group', get_string('group', 'block_gantt'), $groupoptions);
        $mform->addRule('group', get_string('required'), 'required', null, 'client');

        // Campo oculto para el proyecto
        $mform->addElement('hidden', 'project');
        $mform->setType('project', PARAM_INT);

        // Añadir botón de envío
        $this->add_action_buttons(true, get_string('submit', 'block_gantt'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Aquí puedes añadir validaciones adicionales si es necesario
        return $errors;
    }
}