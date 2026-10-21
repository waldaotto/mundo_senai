<?php

namespace App\Controllers;

use App\Core\Controller;

class UserController extends Controller {

    public function index(){

        if (!isset($_SESSION["user_id"])){
            $this->redirect("/login");
        }

        $this->view('header', ['title' => 'Usuarios']);
        $this->view('user');
    }
}
