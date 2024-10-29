<?php
namespace report_dailyattendance;

class AttendanceList {
    private $customerid;
    private $groupid;
    private $billid;
    private $attendance_list;
    private $selected_date_start;
    private $selected_date_end;
    private $attendance_status;
    private $order;
    private $orderby;
    private $pages;
    private $offset;
    private $num_records;
    
    function __construct($customerid,$groupid,$billid,$selected_date_start,$selected_date_end,$attendance_status, $offset, $order, $orderby){
        $this->customerid=$customerid;
        $this->groupid=$groupid;
        $this->billid=($billid===null || $billid==='')?null:$billid;
        $this->selected_date_start=$selected_date_start;
        $this->selected_date_end=$selected_date_end;
        $this->attendance_status=$attendance_status;
        $this->order=$order;
        $this->orderby=$orderby;
        $this->pages=[new \stdClass()];
        $this->pages[0]->page=1;
        $this->pages[0]->active=true;
        $this->offset=$offset;
        $this->num_records=0;
        $this->setDailyAttendance($this->billid);
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

    private function setDailyAttendance($billid=null){
        global $DB;
        $customershortcode=$this->get_customershortcode();
        
        
        $customerkey = $DB->get_record_sql(
            "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('shortname') . " = " . $DB->sql_compare_text(':customerkey'),
            ['customerkey' => 'customer']
        )->id;
        
        $groupkey = $DB->get_record_sql(
            "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('shortname') . " = " . $DB->sql_compare_text(':groupkey'),
            ['groupkey' => 'group']
        )->id;

        
        $billidkey = $DB->get_record_sql(
            "SELECT id FROM {user_info_field} WHERE " . $DB->sql_compare_text('shortname') . " = " . $DB->sql_compare_text(':billidkey'),
            ['billidkey' => 'billid']
        )->id;
        //$groupkey=$DB->get_field('user_info_data', 'fieldid', ['data'=>'G1']);
        //$billidkey=$DB->get_field('user_info_data', 'fieldid', ['data'=>$billid]);
        
       

        $sub_sql="(SELECT DISTINCT
            att.course AS courseid,
            c.shortname AS Nomducours,
            c.fullname AS nameCourse,
            att.name AS Module,
            attsess.id AS sessionid,
            attlog.studentid AS studentid,
            (
                CASE WHEN(info.fieldid = :customerkey) THEN info.data
            END
            ) AS Customer,
            (
                CASE WHEN(info.fieldid = :groupkey) THEN info.data
            END
            ) AS grouptrainee,
            (
                CASE WHEN(info.fieldid = :billidkey) THEN info.data
            END
            ) AS Billid,
            u.firstname AS Prenom,
            u.lastname AS Nom,
            attsess.sessdate AS DateAtt,
            attlog.id AS logid,
            attlog.statusid AS statusid,
            attsta.description AS description,
            attlog.remarks AS feedback
            FROM
                mdl_attendance att
            JOIN mdl_attendance_sessions attsess
            ON (attsess.attendanceid = att.id) JOIN mdl_attendance_log attlog
            ON (attlog.sessionid = attsess.id) JOIN mdl_attendance_statuses attsta
            ON (attsta.id = attlog.statusid) JOIN mdl_user u
            ON (u.id = attlog.studentid) JOIN mdl_course c
            ON (c.id = att.course) JOIN mdl_user_info_data info
            ON (u.id = info.userid) WHERE (u.suspended = 0)
            ORDER BY
                att.course,
                attsess.id,
                attlog.statusid) as daily_attendance";

        $sql="SELECT 
            MAX(Customer) as customerid,                                                   
            MAX(grouptrainee) as grouptrainee, 
            MAX(Billid) as billid, 
            UPPER(Prenom) as Prenom, 
            UPPER(Nom) as Nom, 
            UPPER(Nomducours) as Nomducours, 
            MAX(UPPER(nameCourse)) as nameCourse, 
            (DateAtt) as DateAtt, 
            (description) as description, 
            MAX(UPPER(feedback)) as feedback  FROM ".$sub_sql." 
            group by DateAtt, Nomducours, description, nom, Prenom
            having customerid like :customerid
            and grouptrainee like :grouptrainee 
            and UPPER(billid) like :billid
            and DateAtt>= :datestart 
            and DateAtt<= :dateend
            and (description like :st or description like :abt or description like :lt or description like :et)
            ORDER BY ".$this->orderby." ASC ";
        
        $startdate=isset($this->selected_date_start)?$this->selected_date_start:date('Y-m-d',time());
        $enddate=isset($this->selected_date_end)?$this->selected_date_end:date('Y-m-d',time());
        $enddate=$enddate+86400;
        

        $present=($this->attendance_status->present)?'Present':'off';
        $absent=($this->attendance_status->absent)?'Absent':'off';
        $late=($this->attendance_status->late)?'Late%':'off';
        $excused=($this->attendance_status->excused)?'Excused':'off';
        
        $list=[];

        $params=array(
            'customerkey'=>$customerkey,
            'groupkey'=>$groupkey,
            'billidkey'=>$billidkey,
            'customerid'=>$this->get_customershortcode(),
            'grouptrainee' => $this->get_group(), 
            'billid' => ($billid==='' || $billid===null)?'%':'%'.strtoupper($billid).'%', 
            'datestart'=>$startdate, 
            'dateend'=>$enddate, 
            'st'=>$present, 
            'abt'=>$absent, 
            'lt'=>$late, 
            'et'=>$excused
        );
        $listAttendance=$DB->get_recordset_sql($sql, $params,0,0);
        
        foreach ($listAttendance as $record) {
            $list[]=$record;
            if (count($list)%$this->offset==0 && count($list)!==0){
                $obj=new \stdClass();
                $obj->page=intdiv(count($list),$this->offset)+1;
                $obj->active=false;
                $this->pages[]=$obj;
            }
        }
        $this->num_records=count($list);
        
        $this->attendance_list=$list;
        
        $listAttendance->close();
    }

    public function getDailyAttendance($selected_page=null){
        if ($selected_page){
            array_map(function($item) use ($selected_page){
                $item->active=false;
                if($selected_page==$item->page)
                    $item->active=true;
            }, $this->pages);
        } 

        if ($selected_page===null)
            $selected_page=0;
        
            
        if ($selected_page===0){
            $pagina=array_slice($this->attendance_list,($selected_page)*$this->offset,count($this->attendance_list));
            $this->pages=[new \stdClass()];
            $this->pages[0]->page=1;
            $this->pages[0]->active=true;
        } elseif ($selected_page>0) {
            $selected_page=$selected_page-1;
            usort($this->attendance_list,array($this,'comparator'));
            $pagina=array_slice($this->attendance_list,($selected_page)*$this->offset,$this->offset);
        }
        
        $result=[$pagina,$this->pages];
        return $result;
    }

    private function comparator($obj1,$obj2){
        
        switch ($this->orderby) {
            case 'DateAtt':
                if ($this->order=='ASC')
                    return $obj1->dateatt <=> $obj2->dateatt;
                else
                    return $obj2->dateatt <=> $obj1->dateatt;
                break;
            case 'billid':
                if ($this->order=='ASC')
                    return $obj1->billid <=> $obj2->billid;
                else
                    return $obj2->billid <=> $obj1->billid;
                break;
            case 'group':
                if ($this->order=='ASC')
                    return $obj1->group <=> $obj2->group;
                else
                    return $obj2->group <=> $obj1->group;
                break;
            case 'Prenom':
                if ($this->order=='ASC')
                    return $obj1->prenom <=> $obj2->prenom;
                else
                    return $obj2->prenom <=> $obj1->prenom;
                break;
            case 'Nom':
                if ($this->order=='ASC')
                    return $obj1->nom <=> $obj2->nom;
                else
                    return $obj2->nom <=> $obj1->nom;
                break;
            case 'Nomducours':
                if ($this->order=='ASC')
                    return $obj1->nomducours <=> $obj2->nomducours;
                else
                    return $obj2->nomducours <=> $obj1->nomducours;
                break;
            case 'description':
                if ($this->order=='ASC')
                    return $obj1->description <=> $obj2->description;
                else
                    return $obj2->description <=> $obj1->description;
                break;
            default:
                # code...
                break;
        }
    }

   

    public function getTotalDailyAttendance(){
        return count($this->attendance_list);
    }
}