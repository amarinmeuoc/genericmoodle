<?php
namespace report_partialplan;


class TrainingPlan {

    private $trainingplan;
    public $date;
    public $customerid;
    public $group;
    public $order;
    public $orderby;
    
    function __construct($order,$orderby,$customershortcode=null,$date=null){
        global $USER;
        $customerid=$this->getFirstCustomerIdFromDB(); //Por defecto se elige el primer cliente de la base de datos
        
        /*
         * Si no se pasa un cliente al constructor se comprueba el codigo cliente del usuario activo
         * Si el usuario activo no tiene asignado un código de cliente, entonces se elige el primero de la base de datos
         * Sino se obtiene el id cliente relativo al codigo cliente del cliente acitvo
        */
        if (is_null($customershortcode)){ 
            if ($USER->profile['customer']!==''){
                $customerid=$this->getCustomerId($USER->profile['customer']);
            } else {
                $customerid=$this->getFirstCustomerIdFromDB();
            }
            
        } 
        $this->order=$order;
        $this->orderby=$orderby;
        $this->date=isset($date)?$date:time();
        $this->customerid=$customerid;
        $this->setTrainingPlan($USER->profile['customer'],$this->date);
        $this->group=$this->getGroupFromDB($customerid);
        
        
        
    }

    private function getCustomerId($customershortcode){
        // Implementación para obtener el customer ID basado en el shortcode
        // Ejemplo:
        global $DB;
        $record = $DB->get_record('customer', ['shortname' => $customershortcode]);
        return $record ? $record->id : null;
    }

    private function getFirstCustomerIdFromDB(){
        global $DB;
        $record = $DB->get_record_sql('SELECT id FROM {customer} ORDER BY id ASC LIMIT 1');
        return $record ? $record->id : null;
    }

    public function setTrainingPlan($customercode,$date){
        global $DB;
        
        //If no customer sent via form, we get the active one.
        if ($customercode!==''){
            $this->customerid=$this->getCustomerId($customercode);
        }
        //La fecha está en formato unixtime
        $this->date=$date;
        
        //Formateamos la fecha para hacer la consulta d-m-Y
        $formatedDate=date('d-m-Y',$date);
        
        
        $sql= "SELECT * FROM {trainingplan}
                    WHERE DATE(FROM_UNIXTIME(startdate))<=STR_TO_DATE(:startdate,'%d-%m-%Y')  
                    AND DATE(FROM_UNIXTIME(enddate))>=STR_TO_DATE(:enddate,'%d-%m-%Y')  
                    AND customerid=:customer";
        $courses=$DB->get_records_sql($sql,['customer'=>$this->customerid,'startdate'=>$formatedDate, 'enddate'=>$formatedDate]);
        $courses=array_values($courses);
        $this->trainingplan= $courses;
        $this->trainingplan=array_map(function($course){
            $course->terminado=$course->enddate<time();
            return $course;
        },$this->trainingplan);
        
        $this->group=$this->getGroupFromDB($this->customerid);
        
        $this->orderTrainingPlan($this->orderby,$this->order);
    }

    public function getTrainingPlanFiltered($groupid,$billid){
        return array_filter($this->trainingplan,function($obj) use ($groupid,$billid){
            if ($groupid==0) 
                return preg_match("/".$billid."/i",$obj->trainees); 
            else
                return $obj->groupid==$groupid && preg_match("/".$billid."/i",$obj->trainees);
        });
    }

    public function getTrainingPlan(){
        return $this->trainingplan;
    }

    private function getGroupFromDB($customerid){
        global $DB;
        $group=$DB->get_records('grouptrainee',['customer'=>$customerid]);
        $group=array_values($group);
        return $group;
    }

    public function orderTrainingPlan($orderby,$order){
        $this->orderby=$orderby;
        $this->order=$order;
        
        if (!is_array($this->trainingplan) || empty($this->trainingplan)) {
            return; // Do nothing if $this->trainingplan is not an array or empty
        }
        usort($this->trainingplan,array($this,'comparator'));
    }

    private function comparator($obj1,$obj2){
        switch ($this->orderby) {
            case 'startdate':
                if ($this->order=='1')
                    return $obj1->startdate <=> $obj2->startdate;
                else
                    return $obj2->startdate <=> $obj1->startdate;
                break;
            case 'enddate':
                if ($this->order=='1')
                    return $obj1->enddate <=> $obj2->enddate;
                else
                    return $obj2->enddate <=> $obj1->enddate;
                break;
            case 'coursename':
                if ($this->order=='1')
                    return $obj1->course <=> $obj2->course;
                else
                    return $obj2->course <=> $obj1->course;
                break;
            case 'provider':
                if ($this->order=='1')
                    return $obj1->provider <=> $obj2->provider;
                else
                    return $obj2->provider <=> $obj1->provider;
                break;
            
            default:
                # code...
                break;
        }

    }
}