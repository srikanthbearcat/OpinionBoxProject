<?php


require '_lib/Slim/Slim.php';
require '_lib/class.Config.php';
require '_lib/class.Core.php';
require '_lib/class.AutoLoader.php';

//require 'Models/class.UserAccount.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(

);

$app->response->headers->set('Content-Type', 'application/json');

require 'controllers/facultyController.php';
require 'controllers/adminController.php';
require 'controllers/studentController.php';



$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});



$app->run();
