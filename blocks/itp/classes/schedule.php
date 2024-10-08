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
 * This file defines the current version of the block_itp plugin code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    block_itp
 * @copyright  2024 Alberto Marín Mendoza (http://myhappycoding.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_itp;

class schedule {

    protected $email;
    protected $totalAssessment;
    protected $totalAttendance;
    protected $itp;
    protected $orderby;
    protected $order;

    function __construct($email){
        //Getting the schedule
        $this->email=$email;
        $this->orderby='startdate';
        $this->order='ASC';
        $this->totalAttendance=0;
        $this->totalAssessment=0;
        $this->setItp();
    }

    public function showTotalAssessment(){
        return $this->totalAssessment;
    }

    public function showTotalAttendance(){
        return $this->totalAttendance;
    }

    public function getUserInformation(){
        return $this->user;
    }

    public function getItp($compacted=null){
        $itp=$this->itp;
        if ($compacted==='yes'){
            //agrupar $this->itp por curso con min(startdate) y max(enddate)
            $grouped = [];
            foreach ($this->itp as $item) {
                // Crear una clave única para cada grupo basado en los campos relevantes
                $key = $item->shortcode;
                
                // Si el grupo no existe, inicializarlo
                if (!isset($grouped[$key])) {
                    $grouped[$key] = $item;
                } else {
                    // Actualizar la fecha mínima de startdate y la fecha máxima de enddate
                    $grouped[$key]->startdate = min($grouped[$key]->startdate, $item->startdate);
                    $grouped[$key]->enddate = max($grouped[$key]->enddate, $item->enddate);
                    // Actualizar la duración total
                    $grouped[$key]->duration += $item->duration;
                }
            }

            // Paso 2: Convertir el array agrupado en un array indexado
            $this->itp = array_values($grouped);

            // Reordenar después de agrupar
            $this->orderItp($this->orderby, $this->order);
        }
        
        return $this->itp;
    }

    

    public function orderItp($orderby,$order){
        $this->orderby=$orderby;
        $this->order=$order;
        if (!is_array($this->itp) || empty($this->itp)) {
            return; // Do nothing if $this->itp is not an array or empty
        }
        usort($this->itp,array($this,'comparator'));
    }

    
    public function setItp(){
        global $DB;
        
        //Load the DDL manager and xmldb API
        $dbman=$DB->get_manager();
        $table_itp='itptrainee';
        $this->course = new \block_itp\coursedetails();
        
        $sql= "SELECT 
                i.id, 
                i.startdate, 
                i.enddate, 
                i.course AS shortcode, 
                c.id AS courseid,
                c.visible,
                i.groupid, 
                i.billid,
                i.customerid, 
                i.email, 
                i.name AS coursename, 
                i.duration, 
                i.location, 
                i.classroom,
                i.schedule, 
                i.lastupdate
            FROM 
                {itptrainee} AS i
            INNER JOIN 
                {course} AS c ON c.shortname=i.course
            WHERE email=:email";
            
        
        if ($dbman->table_exists($table_itp)){
            $query=$DB->get_records_sql($sql, array('email'=>$this->email));
                      
            $this->addCourseMetadata($query);

            $this->calculateTotalAssessment($query);

            $this->calculateTotalAttendance($query);

            //The returned array first element must be index 0
            $this->itp=array_values($query);
            
            //Avoiding dates are strings
            foreach ($this->itp as $key => $recordObj) {
                $recordObj->startdate=(int)$recordObj->startdate;
                $recordObj->enddate=(int)$recordObj->enddate;
                $recordObj->duration=(int)$recordObj->duration;
                $recordObj->visible=(int)$recordObj->visible;
                $recordObj->attendance=(float)$recordObj->attendance;
                $recordObj->assessment=(float)$recordObj->assessment;
            }
            
        } else 
            return;
            
    }

    private function addCourseMetadata($query){
        foreach ($query as $key => $ipt_record) {
            $course = new \block_itp\coursedetails($ipt_record);
            $query[$key]->courseUrl=$course->getCourseUrl();
            $query[$key]->assessment=$course->getCourseAssessment();
            $query[$key]->attendance=$course->getCourseAttendance();
            $query[$key]->courseId=$course->getCourseId();
            $query[$key]->assessmentUrl=$course->getAssessmentUrl();
            
        }
    }

    private function calculateTotalAssessment($query){
        $total=0;
        $cont=0;
        foreach ($query as $ipt_record){
            if ($ipt_record->assessment!==null){
                $total+=floatval($ipt_record->assessment);
                $cont++;
            }
        }
        if ($cont===0){
            $this->totalAssessment=0;
        }else{
            $this->totalAssessment=number_format($total/$cont,2,'.','');
        }
    }

    private function calculateTotalAttendance($query){
        $total=0;
        $cont=0;
        foreach ($query as $ipt_record){
            if ($ipt_record->attendance!==null){
                $total+=floatval($ipt_record->attendance);
                $cont++;
            }
        }
        if ($cont===0){
            $this->totalAttendance=0;
        }else{
            $this->totalAttendance=number_format($total/$cont,2,'.','');
        }
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
            case 'att':
                if ($this->order=='ASC')
                    return $obj1->attendance <=> $obj2->attendance;
                else
                    return $obj2->attendance <=> $obj1->attendance;
                break;
            case 'ass':
                if ($this->order=='ASC')
                    return $obj1->assessment <=> $obj2->assessment;
                else
                    return $obj2->assessment <=> $obj1->assessment;
                break;
            case 'course':
                if ($this->order=='ASC')
                    return $obj1->coursename <=> $obj2->coursename;
                else
                    return $obj2->coursename <=> $obj1->coursename;
                break;
            case 'duration':
                if ($this->order=='ASC')
                    return intval($obj1->duration) <=> intval($obj2->duration);
                else
                    return intval($obj2->duration) <=> intval($obj1->duration);
                break;
            
            default:
                # code...
                break;
        }

    }


}