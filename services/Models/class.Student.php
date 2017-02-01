<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Student{
    private  $first_name;
    private  $last_name;
    private  $email_id;

    
    function __construct($first_name, $last_name, $email_id) {

        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email_id = $email_id;

    }



    function getFirstName() {
        return $this->first_name;
    }

    function getLastName() {
        return $this->last_name;
    }

    function getEmailId() {
        return $this->email_id;
    }




    function setFirstName($first_name) {
        $this->first_name = $first_name;
    }

    function setLastName($last_name) {
        $this->last_name = $last_name;
    }

    function setEmailId($email_id) {
        $this->email_id = $email_id;
    }



}

?>