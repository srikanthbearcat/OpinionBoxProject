<?php

/**
 * Created by IntelliJ IDEA.
 * User: S525796
 * Date: 16-01-2017
 * Time: 12:46 AM
 */
class Users
{
    private $user_name;
    private $first_name;
    private $last_name;
    private $email;
    private $password;


    function __construct($user_name, $first_name, $last_name, $email, $password) {
        $this->user_name = $user_name;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password = $password;
    }
    function getUsername() {
        return $this->user_name;
    }


    function getFirstName() {
        return $this->first_name;
    }

    function getLastName() {
        return $this->last_name;
    }

    function getEmail() {
        return $this->email;
    }

    function getPassword() {
        return $this->password;
    }



    function setUsername($user_name) {
        $this->user_name = $user_name;
    }

    function setFirstName($first_name) {
        $this->first_name = $first_name;
    }

    function setLastName($last_name) {
        $this->last_name = $last_name;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setPassword($password) {
        $this->password = $password;
    }



}
?>