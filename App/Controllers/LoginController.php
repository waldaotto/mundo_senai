<?php

namespace App\Controllers;

use App\Services\UserServices;
use App\Views\Login;

/**
 * Classe responsavel por fazer validaçòes de entrada e saída de dados para Login.
 */
class LoginController {

    private string $usuario;
    private string $senha;
    private UserServices $service;

    public function __construct()
    {
        $this->service = new UserServices();
    }

    public function index(){
        Login::view($this);

    }

    public function validate(){

        $this->set_post();

        $user_id = $this->service->login($this->usuario,$this->senha);

        if (!$user_id)
            return false;

        return true;
        
    }

    public function set_post(){

        if ($_SERVER["REQUEST_METHOD"] === "POST"){
            $this->usuario = $_POST['usuario'] ?? '';
            $this->senha = $_POST['senha'] ?? '';
        }

        return;
    }
}


