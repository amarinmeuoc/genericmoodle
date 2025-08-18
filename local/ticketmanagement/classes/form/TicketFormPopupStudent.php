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
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the local_createcustomer plugin code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    local_ticketmanagement
 * @copyright  2024 Alberto Marín Mendoza (http://myhappycoding.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ticketmanagement\form;


class TicketFormPopupStudent extends \core_form\dynamic_form {
    // Define the form structure
    public function definition() {
        global $DB;
        $mform = $this->_form;

        $ticketid=$this->_ajaxformdata['num_ticket'];
   
        $mform->addElement('static', 'ticketidtitle', get_string('ticketid', 'local_ticketmanagement'), $ticketid);
        $mform->addElement('hidden', 'ticketid', $ticketid);

        //Se obtiene el id de usuario a partir del ticket
        $ticket=$DB->get_record('ticket', ['id'=>$ticketid], '*');
       
        $userid=$ticket->userid;
        $subcategory=$ticket->subcategoryid;

         //Se lee de la base de datos
        $user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname, email');
        $username=$user->firstname . ", ". $user->lastname;
        $mform->addElement('static',  'user',  get_string('user', 'local_ticketmanagement'), $username);

        //Se obtiene la fecha de la tabla de tickets
        $date=date('d-m-Y H:i',$ticket->dateticket);
        $mform->addElement('static',  'date',  get_string('date', 'local_ticketmanagement'), $date);

        
        $ticket_category_options=$DB->get_records('ticket_category',[],'id ASC','*');
        $categoryoption=[];
        foreach ($ticket_category_options as $value => $option) {
            $categoryoption[$value]=$option->category;
        }

        $selCategory=$mform->addElement('select', 'category', get_string('category', 'local_ticketmanagement'), $categoryoption,['disabled'=>true]);
        $category=$DB->get_field('ticket_subcategory', 'categoryid', ['id'=>$subcategory]);
        $selCategory->setSelected($category);
        

        $ticket_subcategory_options=$DB->get_records('ticket_subcategory',['categoryid'=>$category],'id ASC','*');
        $subcategoryoption=[];
        foreach ($ticket_subcategory_options as $value => $option) {
            $subcategoryoption[$value]=$option->subcategory;
        }

        $selSubcategory=$mform->addElement('select', 'subcategory', get_string('subcategory', 'local_ticketmanagement'), $subcategoryoption,['disabled'=>true]);
        $selSubcategory->setSelected($subcategory);

        // Priority dropdown to allow the user to change the priority
        $priorityoptions = [
            'Low' => get_string('low', 'local_ticketmanagement'),
            'Medium' => get_string('medium', 'local_ticketmanagement'),
            'High' => get_string('high', 'local_ticketmanagement'),
        ];
        $selPriority=$mform->addElement('select', 'priority', get_string('priority', 'local_ticketmanagement'), $priorityoptions,['disabled'=>true]);
        $mform->setDefault('priority', 'Medium');  // Default priority value
        $selPriority->setSelected($ticket->priority);
        

        $description=$ticket->description;

        // Add any other fields as necessary...
        $mform->addElement('static',  'description',  get_string('description', 'local_ticketmanagement'), $description);

        $familyid=$ticket->familiarid;

        //Get familiar name
        $familiar=$DB->get_record('family',['id'=>$familyid],'*');
        if ($familiar)
            $familiarString="$familiar->relationship: $familiar->name, $familiar->lastname";
        else
            $familiarString="No family issue";
        // Add any other fields as necessary...
        $mform->addElement('static',  'familyissue',  get_string('familyissue', 'local_ticketmanagement'), $familiarString);

        $mform->addElement('hidden',  'userid',  $userid);

        // --- Configuración de área de archivos ---
        $context = \context_system::instance();
        $filearea = 'sharedfiles';
        $component = 'local_ticketmanagement';
        $itemid = $ticket->dateticket; // constante por ticket
        $maxbytes = 10485760; // Límite de 10MB

        // Configurar opciones del filemanager

        $fileoptions=[
                'subdirs' => 0,
                'maxbytes' => $maxbytes,
                'areamaxbytes' => 10485760,
                'maxfiles' => 50,
                'accepted_types' => ['document','.jpg', '.jpeg', '.png', '.gif'],
                'return_types' => 1 | 2,
                'filearea'=>'sharedfiles'
        ];

         // --- Preparar draft para filemanager ---
        $draftitemid = file_get_submitted_draft_itemid('attachments');
        file_prepare_draft_area(
            $draftitemid,
            $context->id,
            $component,
            $filearea,
            $itemid,
            $fileoptions
        );

        $mform->addElement(
            'filemanager',
            'attachments',
            get_string('attachment', 'local_ticketmanagement'),
            null,
            $fileoptions
        );

        $mform->addElement('hidden',  'hiddenstate',  $ticket->state);
        
    }

    // This method processes the submitted data
    public function process_data($data) {
        
    }

    // Custom validation if needed
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Add any custom validation if necessary
        return $errors;
    }

    // Return any additional data after form submission (optional)
    public function get_return_data() {
        // Return data after form submission if necessary
        return ['success' => true];
    }

     /**
     * Check if current user has access to this form, otherwise throw exception
     *
     * Sometimes permission check may depend on the action and/or id of the entity.
     * If necessary, form data is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     */
    protected function check_access_for_dynamic_submission(): void {
        return;
    }

    /**
     * Returns form context
     *
     * If context depends on the form data, it is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     *
     * @return \context
     */
    protected function get_context_for_dynamic_submission(): \context {
      
        return \context_system::instance();
    }

    /**
     * File upload options
     *
     * @return array
     * @throws \coding_exception
     */
    protected function get_options(): array {
        return [];
    }

 
    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * This method can return scalar values or arrays that can be json-encoded, they will be passed to the caller JS.
     *
     * Submission data can be accessed as: $this->get_data()
     *
     * @return mixed
     */
    public function process_dynamic_submission() {
        global $DB, $USER;

        $data = $this->get_data();
        if (!$data) {
                throw new \moodle_exception('nodata', 'local_ticketmanagement');
            }

        $ticketid = $data->ticketid;
        $originalticket = $DB->get_record('ticket', ['id' => $ticketid], '*', MUST_EXIST);

        $haschanges = false;
        $changes = [];
        $updateticket = new \stdClass();
        $updateticket->id = $ticketid;

        
        // Archivos adjuntos
        $context = \context_system::instance();
        $filearea = 'sharedfiles';
        $component = 'local_ticketmanagement';
        $itemid = $originalticket->dateticket; // constante por ticket

        $fs = get_file_storage();

        // Obtener archivos previos (excluyendo el directorio)
        $pre_savedfiles = array_filter($fs->get_area_files($context->id, $component, $filearea, $itemid), function($file) {
            return $file->get_filename() != '.';
        });

        file_save_draft_area_files(
            $data->attachments,
            $context->id,
            $component,
            $filearea,
            $itemid,
            ['subdirs' => 0, 'maxbytes' => 10485760, 'maxfiles' => 50]
        );

        // Obtener archivos posteriores (excluyendo el directorio)
        $post_savedfiles = array_filter($fs->get_area_files($context->id, $component, $filearea, $itemid), function($file) {
            return $file->get_filename() != '.';
        });

        // Calcular diferencia correctamente
        $savedfiles = count($post_savedfiles) - count($pre_savedfiles);

        if ($savedfiles != 0) {
            $haschanges = true;
            $changes['attachments'] = true;
        }

        return [
            'success' => true,
            'haschanges' => $haschanges,
            'changes' => $changes,
            'ticketid' => $ticketid,
            'cancelled' => $data->cancelled,
            'hiddenstate' => $data->hiddenstate,
            'close' => $data->close,
            'subcategory' => $data->subcategory,
            'priority' => $data->priority,
            'saved_files_count' => $savedfiles, // Opcional: también puedes incluir el nuevo itemid
            'userid' => $USER->id, // ID del usuario que está haciendo la actualización
        ];
}

    

    /**
     * Load in existing data as form defaults
     *
     * Can be overridden to retrieve existing values from db by entity id and also
     * to preprocess editor and filemanager elements
     *
     * Example:
     *     $this->set_data(get_entity($this->_ajaxformdata['id']));
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;
        $mform = $this->_form;
        // Si el formulario ya fue enviado, no ejecutamos esta lógica.
        // Si el formulario ya fue enviado, obtener el ticketid desde los datos del formulario
        if ($this->is_submitted()) {
            $data = $this->get_data();
            $ticketid = $data->ticketid ?? null;
        } else {
            // Si el formulario se está cargando por primera vez, obtener el ticketid desde _ajaxformdata
            $ticketid = $this->_ajaxformdata['num_ticket'] ?? null;
        }

        // Verificar que el ticketid no esté vacío.
        if (empty($ticketid)) {
            throw new \moodle_exception('Ticket ID is missing or invalid');
        }

        $ticket = $DB->get_record('ticket', ['id' => $ticketid], '*');
        if (!$ticket) {
            throw new \moodle_exception('Ticket not found');
        }
        $userid = $ticket->userid;

        $context = \context_system::instance();
    $filearea = 'sharedfiles';
    $component = 'local_ticketmanagement';
    $itemid = $ticket->dateticket; // constante por ticket

    $fileoptions = [
        'subdirs' => 0,
        'maxbytes' => 10485760,
        'maxfiles' => 50,
    ];

    $draftitemid = file_get_submitted_draft_itemid('attachments');
    file_prepare_draft_area(
        $draftitemid,
        $context->id,
        $component,
        $filearea,
        $itemid,
        $fileoptions
    );

    $this->set_data([
        'attachments' => $draftitemid,
        'priority' => $ticket->priority,
        'close' => ($ticket->state === 'Closed') ? 1 : 0,
        'cancelled' => ($ticket->state === 'Cancelled') ? 1 : 0,
        'state' => $ticket->state,
    ]);
        

    }

    public function get_description_text_options() : array {
        global $CFG;
        require_once($CFG->libdir.'/formslib.php');
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'context' => \context_system::instance()
        ];
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * This is used in the form elements sensitive to the page url, such as Atto autosave in 'editor'
     *
     * If the form has arguments (such as 'id' of the element being edited), the URL should
     * also have respective argument.
     *
     * @return \moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): \moodle_url {
                
        return new \moodle_url('/local_ticketmanagement/index.php');
    }

}