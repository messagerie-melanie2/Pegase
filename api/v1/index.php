<?php

use Program\Lib\Api\Main as m;

include '../../program/include/includes.php';

header("Content-Type:application/json");

$uri = \Program\Lib\Request\Request::getFilteredURL();
$uri = explode('/', $uri);

$countUri = 0;
$index = 0;

foreach ($uri as $value) {
    $countUri++;
    if ($value == "index.php") {
       $index = $countUri-1; 
    }
}

//Retourne la méthode (GET / POST ...)
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        $function = $uri[$index+1];
        m::$function($uri[$index+2]);
    break;
    
    case 'POST':
        $function = $uri[$index+1];
        m::$function();
    break;
}




