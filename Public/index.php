<?php

include_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;



if (session_status() !== PHP_SESSION_ACTIVE){
    session_start();
}

$home = new HomeController();
$home->index();



