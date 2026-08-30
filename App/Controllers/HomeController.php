<?php

namespace App\Controllers;
use App\Controllers\LoginController;
use App\Views\Home;

class HomeController {

    
    public function index(){

        // if (!isset($_SESSION["user_id"])){
        //     $login = new LoginController();
        //     $login->index();
        //     return;
        // }

        // Home::view();
        var_dump($_SERVER);
    }
}