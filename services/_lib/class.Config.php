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

    // DB Config
    Config::write('db.host', '107.180.24.241');
    Config::write('db.port', '3306');
    Config::write('db.basename', 'teammate_dev');
    Config::write('db.user', 'teammate_dev');
    Config::write('db.password', 'teammate_dev');

} else {

// Local data base config
    Config::write('db.host', '127.0.0.1');
    Config::write('db.port', '3306');
    Config::write('db.basename', 'teammate_dev');
    Config::write('db.user', 'root');
    Config::write('db.password', '');
}