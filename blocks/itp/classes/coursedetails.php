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

class coursedetails {

    private $courseUrl;
    private $assessment;
    private $assessment_list_url;
    private $attendance;
    private $courseId;
    private $shortcode;

    function __construct($itp=null){
        $this->shortcode=isset($itp->shortcode)?$itp->shortcode:null;
        if (isset($itp->shortcode) && ($itp->shortcode!==null))
            $this->courseId=$this->getIdFromDB($itp->shortcode);
        else
            $this->courseId=0;
        
        $this->courseUrl=new \moodle_url('/course/view.php',array('id'=>$this->courseId));
        $this->assessment_list_url=null;

        if (isset($itp) && $itp!==null){
            $this->setAssessment($itp);
            $this->setAttendance($itp);
        }

    }

    private function setAssessment($itp){
        
        global $DB;
        $startdate=isset($itp->startdate)?$itp->startdate:0;
        $enddate=isset($itp->enddate)?$itp->enddate:0;
        $currentdate=time();
        $email=isset($itp->email)?$itp->email:'';
        $group=isset($itp->groupid)?$itp->groupid:'';
        $billid=isset($itp->billid)?$itp->billid:'';
        $shortcode=isset($itp->shortcode)?$itp->shortcode:'';
        $score=0;
        $completado=false;

        //Show the assessment only of finished courses
        if ($startdate<$currentdate){
            $completado=true;
            $score=$DB->get_records_sql("SELECT ROUND(AVG(finalgrade),2) AS finalscore FROM {user} u
                                INNER JOIN mdl_grade_grades AS grades ON grades.userid=u.id
                                INNER JOIN mdl_grade_items AS items ON grades.itemid=items.id
                                INNER JOIN mdl_course AS courses ON courses.id=items.courseid
                                WHERE u.email=? AND courses.shortname=? AND items.itemtype='category'",
                                array($email,$shortcode));

            $score=array_values($score);
            
            $this->assessment=$score[0]->finalscore;
            $this->assessment_list_url=new \moodle_url('/blocks/itp/detailedassessment.php',array('courseId'=>$this->courseId));
        }
    }

    private function setAttendance($itp){
        global $DB;
        $startdate=isset($itp->startdate)?$itp->startdate:0;
        $enddate=isset($itp->enddate)?$itp->enddate:0;
        $currentdate=time();
        $email=isset($itp->email)?$itp->email:'';
        $shortcode=isset($itp->shortcode)?$itp->shortcode:'';
        $att=0;
        $completado=false;

        //Show the attendamce only of running courses
        if ($startdate<$currentdate){
            $completado=true;
            $att=$DB->get_records_sql("SELECT ROUND(AVG(finalgrade),2) AS finalscore FROM {user} u
                                INNER JOIN mdl_grade_grades AS grades ON grades.userid=u.id
                                INNER JOIN mdl_grade_items AS items ON grades.itemid=items.id
                                INNER JOIN mdl_course AS courses ON courses.id=items.courseid
                                WHERE u.email=? AND courses.shortname=? AND items.itemmodule='attendance'",
                                array($email,$shortcode));

            $att=array_values($att);
            
            $this->attendance=$att[0]->finalscore;
        }
    }

    public function getCourseId(){
        return $this->courseId;
    }

    public function getCourseUrl(){
        return $this->courseUrl;
    }

    public function getCourseAssessment(){
        return $this->assessment;
    }

    public function getAssessmentUrl(){
        return $this->assessment_list_url;
    }

    public function getCourseAttendance(){
        return $this->attendance;
    }

    public function getCourseShortcode(){
        return $this->shortcode;
    }

    private function getIdFromDB($shortname){
        global $DB;
        $query=$DB->get_record('course',array('shortname'=>$shortname),'id')->id;
        
        return $query;
    }


}