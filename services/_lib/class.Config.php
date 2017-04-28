<?php

// Configuration Class
class Config {

    static $confArray;

    public static function read($name) {
        return self::$confArray[$name];
    }

    public static function write($name, $value) {
        self::$confArray[$name] = $value;
    }

}

if ($_SERVER['SERVER_NAME'] === "localhost") {
    // Local data base config
    Config::write('db.host', '127.0.0.1');
    Config::write('db.port', '3306');
    Config::write('db.basename', 'example');
    Config::write('db.user', 'root');
    Config::write('db.password', '');



} else {
    Config::write('db.host', '0.0.0.0');
    Config::write('db.port', '0');
    Config::write('db.basename', 'example');
    Config::write('db.user', 'root');
    Config::write('db.password', '');




   }
