<?php
namespace report_coursereport;

class AssessmentList {
    private $customerid;
    private $groupid;
    private $billid;
    private $wbs;
    private $selected_date_start;
    private $selected_date_end;
    private $assessment_list;
    private $order;
    private $orderby;
    private $pages;
    private $offset;
    private $num_records;
    private $status;

    function __construct($customerid,$groupid,$billid,$wbs,$selected_date, $offset, $order, $orderby,$status){
        $this->customerid=$customerid;
        $this->groupid=$groupid;
        $this->billid=($billid===null || $billid==='')?null:$billid;
        $this->wbs=$wbs;
        $this->selected_date=$selected_date;
        $this->order=$order;
        $this->orderby=$orderby;
        $this->status=$status;
        $this->pages=[new \stdClass()];
        //Se inicia la página 1 y se pone activa
        $this->pages[0]->page=1;
        $this->pages[0]->active=true;

        //Se define el número de registros por páginas
        $this->offset=$offset;
        $this->num_records=0;
        $this->setAssessmentList();
    }

    function set_activePage($page){
        $this->active_page=$page;
    }

    function set_order($order){
        $this->order;
    }

    function set_orderby($orderby){
        $this->orderby;
    }

    function get_activePge(){
        return $this->active_page;
    }

    function get_offset(){
        return $this->offset;
    }

    function get_order(){
        return $this->order;
    }

    function get_orderby(){
        return $this->orderby;
    }

    function get_numRecords(){
        return $this->num_records;
    }

    function get_group(){
        global $DB;
        $group=$DB->get_record('grouptrainee', ['id'=>$this->groupid],'name');
        return $group->name;
    }

    function get_customershortcode(){
        global $DB;
        $customer_shortname='';
        if ($this->customerid!==''){
            $customer=$DB->get_record('customer', ['id'=>$this->customerid],'shortname');
            $customer_shortname=$customer->shortname;
        } else {
            $customer_list=$DB->get_records('customer', [], 'shortname ASC', 'id, shortname');
            $customer_list=array_values($customer_list);
            
            $customer_shortname=$customer_list[0]->shortname;
        
        }
        return $customer_shortname;
        
            
    }

    private function setAssessmentList(){
        global $DB;
        $selected_date=isset($this->selected_date)?$this->selected_date:time();

        $sql="SELECT id, billid,customerid, groupid, customercode, groupname,  shortname, fullname,  min(start_date) as startdate, max(end_date) as enddate, max(firstname) as firstname, max(lastname) as lastname, max(email) as email, max(attendance) as attendance, avg(assessment) as assessment 
        FROM (SELECT DISTINCT
            itp.id as id,
            itp.customerid as customerid,
            customer.shortname as customercode,
            itp.groupid as groupid,
            grouptrainee.name as groupname,
            itp.billid as billid,
            courses.shortname as shortname,
            courses.fullname as fullname,
            itp.startdate AS start_date,
            itp.enddate AS end_date,
            users.firstname as firstname,
            users.lastname as lastname,
            users.email as email,
            grades.userid as userid,
            items.courseid as courseid,
            CASE WHEN items.itemname REGEXP 'attendance' then grades.finalgrade end as attendance,
            case when items.itemname not REGEXP 'attendance' and items.itemname not like 'attitude' then grades.finalgrade end as assessment,
            case when items.itemname like 'attitude' and info.fieldid=1 then substr(grades.feedback, 1, 250) end as comments,
            items.itemname as itemname,
            items.itemtype as itemtype,
            items.itemmodule as itemmodule
            FROM
                mdl_itptrainee itp
                join mdl_user users on (itp.email=users.email)
                left join mdl_grade_grades grades on (grades.userid=users.id)
                join mdl_grade_items items on (items.id=grades.itemid)
                join mdl_user_info_data info on (info.userid=users.id)
                join mdl_course courses on (courses.id=items.courseid and itp.course=courses.shortname)
                join mdl_customer customer on (customer.id=itp.customerid)
                join mdl_grouptrainee grouptrainee on (grouptrainee.id=itp.groupid)
                left join mdl_grade_categories cat on (cat.courseid=courses.id)

            WHERE
                (items.itemtype='manual' or items.itemmodule REGEXP 'attendance') 
                and items.itemname NOT REGEXP '[Aa]ttitude'
                and items.itemname NOT REGEXP '[Pp]articipation'
                and items.itemname NOT REGEXP '[Aa]chieved [Ll]evel'
                and grades.finalgrade!='NULL'
             and cat.fullname REGEXP '[Tt]otal [Aa]s+es+ment\s*\w*') 
        AS RESULT
            GROUP BY customerid,groupid,billid,shortname,fullname
            HAVING customerid=:customerid AND
             groupid=:groupid AND 
             billid like :billid AND 
             shortname like :shortname";
             
        if ($this->status===1){ //If listing completed courses
            $sql.=" AND 
                startdate <= :startdate AND 
                enddate <= :enddate";
        } else {
            $sql.=" AND 
                startdate <= :startdate AND 
                enddate >= :enddate";
        }

        $sql.=" ORDER BY ". $this->orderby ." ";
        if ($this->order===true)
            $sql.="ASC";
        else
            $sql.="DESC";
            
        
       
        
        $list=[];
        $listAssessment=$DB->get_recordset_sql($sql,
                            array('customerid'=>$this->customerid,'groupid' => $this->groupid, 
                            'billid' => ($this->billid==='' || $this->billid===null)?'%':'%'.strtoupper($this->billid).'%', 'startdate'=>$selected_date+86400, 
                            'enddate'=>$selected_date, 'shortname'=>($this->wbs==='' || $this->wbs===null)?'%':'%'.strtoupper($this->wbs).'%'),0,0);
        
        
        //Cada vez que el número de registros supere el offset, se añade una nueva página                    
        foreach ($listAssessment as $record) {
            $list[]=$record;
            if (count($list)%$this->offset==0 && count($list)!==0){
                $obj=new \stdClass();
                $obj->page=intdiv(count($list),$this->offset)+1;
                $obj->active=false;
                $this->pages[]=$obj;
            }
        }
        $this->num_records=count($list);
        
        $this->assessment_list=$list;
        
        $listAssessment->close();
    }

    public function getAssessment($selected_page=null){
        //Si se solicita el resultado de una pagina se activa la pagina seleccionada.
        if ($selected_page){
            array_map(function($item) use ($selected_page){
                $item->active=false;
                if($selected_page==$item->page)
                    $item->active=true;
            }, $this->pages);
        } 
        //Si no hay pagina seleccionada se selecciona la primera.
        if ($selected_page===null)
            $selected_page=0;
            
        //Si la pagina seleccionada es la primera    
        if ($selected_page===0){
            $pagina=array_slice($this->assessment_list,($selected_page)*$this->offset,count($this->assessment_list));
            $this->pages=[new \stdClass()];
            $this->pages[0]->page=1;
            $this->pages[0]->active=true;
        } elseif ($selected_page>0) {
            $selected_page=$selected_page-1;
            usort($this->assessment_list,array($this,'comparator'));
            
            $pagina=array_slice($this->assessment_list,($selected_page)*$this->offset,$this->offset);
        }
        
        $result=[$pagina,$this->pages];
        return $result;
    }

    private function comparator($obj1,$obj2){
        switch ($this->orderby) {
            case 'startdate':
                if ($this->order=='ASC')
                    return $obj1->startdate <=> $obj2->startdate;
                else
                    return $obj2->startdate <=> $obj1->startdate;
                break;
            case 'enddate':
                if ($this->order=='ASC')
                    return $obj1->enddate <=> $obj2->enddate;
                else
                    return $obj2->enddate <=> $obj1->enddate;
                break;
            case 'billid':
                if ($this->order=='ASC')
                    return $obj1->billid <=> $obj2->billid;
                else
                    return $obj2->billid <=> $obj1->billid;
                break;
            case 'groupname':
                if ($this->order=='ASC')
                    return $obj1->groupname <=> $obj2->groupname;
                else
                    return $obj2->groupname <=> $obj1->groupname;
                break;
            case 'customercode':
                if ($this->order=='ASC')
                    return $obj1->customerid <=> $obj2->customerid;
                else
                    return $obj2->customerid <=> $obj1->customerid;
                break;
            case 'firstname':
                if ($this->order=='ASC')
                    return $obj1->firstname <=> $obj2->firstname;
                else
                    return $obj2->firstname <=> $obj1->firstname;
                break;
            case 'lastname':
                if ($this->order=='ASC')
                    return $obj1->lastname <=> $obj2->lastname;
                else
                    return $obj2->lastname <=> $obj1->lastname;
                break;
            case 'attendance':
                if ($this->order=='ASC')
                    return $obj1->attendance <=> $obj2->attendance;
                else
                    return $obj2->attendance <=> $obj1->attendance;
                break;
            case 'assessment':
                if ($this->order=='ASC')
                    return $obj1->assessment <=> $obj2->assessment;
                else
                    return $obj2->assessment <=> $obj1->assessment;
                break;
            case 'wbs':
                if ($this->order=='ASC')
                    return $obj1->shortname <=> $obj2->shortname;
                else
                    return $obj2->shortname <=> $obj1->shortname;
                break;
            
            default:
                # code...
                break;
        }
    }

   

    public function getTotalAssessment(){
        return count($this->assessment_list);
    }
}