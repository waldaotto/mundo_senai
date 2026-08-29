<?php

include_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\LoginController;

if (session_status() !== PHP_SESSION_ACTIVE){
    session_start();
}
