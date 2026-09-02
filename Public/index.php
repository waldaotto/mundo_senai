<?php

use App\Routes\Router;
session_start();

define('ROOT', dirname(__DIR__));

require_once __DIR__."/../App/Core/defines.php";
require_once ROOT.'/vendor/autoload.php';
require_once ROOT.'/App/Routes/UrlHelper.php';

Router::execute();
?>




