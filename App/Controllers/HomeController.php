<?php

namespace App\Controllers;

class HomeController {

    public $router;

    public function __construct($router) {
        $this->router = $router;
    }
    public function index(){

        $router = $this->router;
        return require __DIR__."/../Views/home.php";
    }
}