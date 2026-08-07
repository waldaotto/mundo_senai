<?php

include __DIR__.'/../vendor/autoload.php';

use App\Core\database\Connection;

if (session_status() == PHP_SESSION_NONE){
    session_start();
}

if(isset($_SESSION['user_id'])){
    header("Location: ",__DIR__.+"/App/Views/Home.php");
    exit();
}
