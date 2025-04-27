<?php
namespace local_ticketmanagement\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');

class uploadFamilyformcsv extends \moodleform {
    
    public function definition() {
        $mform = $this->_form;
        
        // File picker
        $mform->addElement('filepicker', 'familycsvfile', get_string('csvfile', 'local_ticketmanagement'), 
            null, ['accepted_types' => '.csv']);
        $mform->addRule('familycsvfile', get_string('required'), 'required', null, 'client');
        
        // CSV options
        $mform->addElement('select', 'encoding', get_string('encoding', 'local_ticketmanagement'), 
            ['UTF-8' => 'UTF-8', 'ISO-8859-1' => 'ISO-8859-1']);
        $mform->setDefault('encoding', 'UTF-8');
        
        $mform->addElement('select', 'delimiter', get_string('csvdelimiter', 'local_ticketmanagement'), 
            [',' => ',', ';' => ';', 'tab' => 'tab']);
        $mform->setDefault('delimiter', ',');
        
        $mform->addElement('advcheckbox', 'hasheaders', get_string('csvhasheaders', 'local_ticketmanagement'));
        $mform->setDefault('hasheaders', 1); // Default to checked
        
        $mform->addElement('advcheckbox', 'preview', get_string('previewonly', 'local_ticketmanagement'));
        $mform->setDefault('preview', 1);
        
        $this->add_action_buttons(true, get_string('upload', 'local_ticketmanagement'));
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        $draftid = file_get_submitted_draft_itemid('familycsvfile');
        $fileinfo = file_get_draft_area_info($draftid);
        
        if ($fileinfo['filecount'] === 0) {
            $errors['familycsvfile'] = get_string('nofileuploaded', 'local_ticketmanagement');
        } elseif ($fileinfo['filesize'] === 0) {
            $errors['familycsvfile'] = get_string('emptyfile', 'local_ticketmanagement');
        }
        
        return $errors;
    }
    
    /**
     * Gets the uploaded file content
     */
    public function get_file_content($fieldname) {
        global $USER;
        
        $draftid = file_get_submitted_draft_itemid($fieldname);
        $fs = get_file_storage();
        $context = \context_user::instance($USER->id);
        
        $files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false);
        
        if (count($files)){
            $file = reset($files);
            return $file->get_content();
        }
        
        return null;
    }
}
