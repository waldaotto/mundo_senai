<?php

use App\Routes\Router;
session_start();

define('ROOT', dirname(__DIR__));

include_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__.'/../App/Routes/UrlHelper.php';

Router::execute();
?>




