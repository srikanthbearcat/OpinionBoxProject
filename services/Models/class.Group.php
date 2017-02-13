<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Group{
    private $group_no;
    private $group_topic;

    
    function __construct($group_no, $group_topic) {
        $this->group_no = $group_no;
        $this->group_topic = $group_topic;
    }

    function getGroupNo() {
        return $this->group_no;
    }

    function getGroupTopic() {
        return $this->group_topic;
    }

    function setGroupNo($group_no) {
        $this->group_no = $group_no;
    }

    function setGroupTopic($group_topic) {
        $this->group_topic = $group_topic;
    }


}
?>

