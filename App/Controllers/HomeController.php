<?php

namespace App\Controllers;

class HomeController {

    public function index(){
        return require __DIR__."/../Views/home.php";
    }
}