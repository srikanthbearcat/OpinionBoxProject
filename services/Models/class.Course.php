<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Course{
    private $course_crn;
    private $course_name;
    private $course_trimester;
    
    function __construct($course_crn, $course_name,$course_trimester) {
        $this->course_crn = $course_crn;
        $this->course_name = $course_name;
        $this->course_trimester = $course_trimester;
    }

    function getCourseCrn() {
        return $this->course_crn;
    }

    function getCourseName() {
        return $this->course_name;
    }
    function getCourseTrimester() {
        return $this->course_trimester;
    }
    function setCourseCrn($course_crn) {
        $this->course_crn = $course_crn;
    }

    function setCourseName($course_name) {
        $this->course_name = $course_name;
    }

    function setCourseTrimester($course_trimester) {
        $this->course_trimester = $course_trimester;
    }
        
}
?>

