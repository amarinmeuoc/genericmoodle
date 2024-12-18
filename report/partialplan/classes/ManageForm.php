<?php
namespace report_partialplan;


class ManageForm {
    public $toform='';
    public $fromform='';
    public $trainee_form;
    function __construct(){
        $this->$trainee_form= new \report_partialplan\form\trainee_form();
    }

    public function manageForm(){
        global $USER;
        // Form processing and displaying is done here.
        if ($this->$trainee_form->is_cancelled()) {
            // If there is a cancel element on the form, and it was pressed,
            // then the `is_cancelled()` function will return true.
            // You can handle the cancel operation here.
        } else if ($this->$fromform = $this->$trainee_form->get_data()) {
            // When the form is submitted, and the data is successfully validated,
            // the `get_data()` function will return the data posted in the form.
            $this->$trainee_form->display();
        } else {
            // This branch is executed if the form is submitted but the data doesn't
            // validate and the form should be redisplayed or on the first display of the form.
            // Set anydefault data (if any).
            $this->$trainee_form->set_data($toform);

            // Display the form.
            $this->$trainee_form->display();

            $customerid=$this->getCustomerId($USER->profile['customercode']);

            $this->$fromform=(object)['selcustomer'=>$customerid,'assesstimefinish'=>time(),'submitbutton'=>'Submit'];
        }
    }

    private function getCustomerId($customercode){
        global $DB;
        $customerId=$DB->get_record('customer',['shortname'=>$customercode],'id');
        return isset($customerId->id)?$customerId->id:"";
    }
}