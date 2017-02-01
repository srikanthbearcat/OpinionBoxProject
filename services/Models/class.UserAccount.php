<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class UserAccount {


    private $user_name;
    private $password;


    function __construct($user_name, $password) {
        $this->user_name = $user_name;
        $this->password = $password;
    }
    function getUserName() {
        return $this->user_name;
    }

    function getPassword() {
        return $this->password;
    }


    function setUserName($user_name) {
        $this->user_name = $user_name;
    }

    function setPassword($password) {
        $this->password = $password;
    }

}

?>
