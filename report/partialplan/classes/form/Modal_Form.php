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
 * @package    report_partialplan
 * @copyright  2024 Alberto Marín Mendoza (http://myhappycoding.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_partialplan\form;

class Modal_Form extends \core_form\dynamic_form {
    private $id;
    // Add elements to form.
    public function definition() {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        
        $mform = $this->_form; // Don't forget the underscore!
        
        $emails=$this->get_options();
        
        $mform->addElement('static', 'aboutform', '', 'You are about to send an email to: ' . count($emails) . ' trainees');
        
        $this->id=$id;
        // Required field (client-side validation test).
        $mform->addElement('text', 'tesubject', 'Subject:', 'size="50"');
        $mform->addRule('tesubject', null, 'required', null, 'client');
        $mform->setType('tesubject', PARAM_TEXT);
        $desceditoroptions = $this->get_description_text_options();
        $mform->addElement('editor', 'teeditor', 'to...','size="50"', $desceditoroptions);
        $mform->settype('teeditor',PARAM_RAW);
        
        $mform->addHelpButton('description_editor', 'description', 'core_customfield');

        //$mform->addElement('editor','teeditor','to...','size="50"');
        
        $emailvalue='';
        foreach ($emails as $key => $value) {
            if ($key===count($emails)-1)
                $emailvalue.=$value->email;
            else
                $emailvalue.=$value->email.',';
            
        }
        $text=$mform->addElement('hidden', "emailhidden", $emailvalue);
        $mform->setType("emailto", PARAM_CLEANHTML);

        
        
        
        
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
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
        $args=[];
        $args['list_trainees']=$this->_ajaxformdata['list_trainees'];
        $args['customerid']=$this->_ajaxformdata['customer'];
        $customer=$args['customerid'];
        $arr_billid=explode(",",$args['list_trainees']);
        $arr_emails=[];
        foreach ($arr_billid as $key=>$billid) {
            $arr_billid[$key]=clean_param(trim($billid),PARAM_CLEANHTML);
            $vesssel=explode("_",$arr_billid[$key])[0];
            $billid=explode("_",$arr_billid[$key])[1];
            $arr_emails[]=$this->get_email($customer,$group,$billid);
        }
        $args['emails']=$arr_emails;
        
        return $args['emails'];
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
        
        return $this->get_data();
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
        $this->set_data([
            'list_trainees' => $this->optional_param('list_trainees', false, PARAM_TEXT),
            'customer' => $this->optional_param('customer', '', PARAM_TEXT),
        ] + $this->get_options());
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
        global $USER;
        return new \moodle_url('/user/profile.php',['id'=>$USER->id]);
    }


       //The $customer is the numeric id
    //$vesel is the text shortname
    //billid is the billet number
    protected function get_email($customer, $group, $billid){
        global $DB;
        $user=new \stdClass();
        $sql="SELECT {user}.id, shortname, data
                FROM {user} 
                    INNER JOIN {user_info_data} ON {user_info_data}.userid={user}.id
                    INNER JOIN {user_info_field} ON {user_info_field}.id={user_info_data}.fieldid
                WHERE data =:billid AND shortname='billid'";

        $query= $DB->get_records_sql($sql,array('billid'=>$billid));
        foreach ($query as $row) {
            //Given a user
            $userid=$row->id;
            $user = $DB->get_record('user', array('id' => $userid));
            profile_load_custom_fields($user);
            
            $groupid=$this->getGroupCodeFromDB($customer,$group);
            
            //Check if is the user we are looking for
            if ($billid==$user->profile['billid'] && $default_customer==$customerid && $group==$groupid){
                $user->id=$userid;
                $user->billid=$billid;
                $user->customer=$customerid;
                $user->group=$groupid;
                $user->rol=$user->profile['rol'];
                $user->type=$user->profile['type'];
                $user->department=$user->profile['department'];
                $user->passport=$user->profile['passport'];
                $user->personal_email=$user->profile['personal_email'];
                $user->nie=$user->profile['nie'];
                $user->firstname=$user->firstname;
                $user->lastname=$user->lastname;
                $user->email=$user->email;
                break;
            }      
        }
        return $user;
    }

    private function getCustomerCodeFromDB($shortname){
        global $DB; 
        $dbman=$DB->get_manager();
        $result=null;
        if ($dbman->table_exists('customer')){
            $query = $DB->get_record('customer',array('shortname'=>$shortname),'id');
            if ($query)
                $result = $query->id;
        }
        return $result;
    }

    private function getGroupCodeFromDB($customerid,$groupName){
        global $DB; 
        $dbman=$DB->get_manager();
        $result=null;
        if ($dbman->table_exists('grouptrainee')){
            $query = $DB->get_record('grouptrainee',array('name'=>$groupName,'customer'=>$customerid),'id');
            if ($query)
                $result = $query->id;
        }
        return $result;
    }



}