<?php

include_once __DIR__.'/App/Core/database/Connection.php';
use App\Core\database\Connection;

if (session_status() == PHP_SESSION_NONE){
    session_start();
}

if(isset($_SESSION['user_id'])){
    harder("location: "+__DIR__.+"/App/Views/Home.php");
    exit();
}


