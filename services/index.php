<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

//require 'amenadiel/slim-phpconsole/src/PHPConsoleWriter.php';
//$logwriter = new \Amenadiel\SlimPHPConsole\PHPConsoleWriter(true);

require '_lib/Slim/Slim.php';
require '_lib/class.Config.php';
require '_lib/class.Core.php';
require '_lib/class.AutoLoader.php';

//require 'Models/class.UserAccount.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(
//    array(
//    'log.enabled' => true,
//    'log.level' => \Slim\Log::DEBUG,
//    'log.writer' => $logwriter)
);

$app->response->headers->set('Content-Type', 'application/json');

require 'Controllers/facultyController.php';
require 'Controllers/adminController.php';
require 'Controllers/studentController.php';

//$app->log->debug('Debug called!');
//$app->log->info('This is just info');
//$app->log->warning('Heads Up! This is a warning');

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});



$app->run();
