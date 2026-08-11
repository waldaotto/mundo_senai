<?php

namespace App\Controllers;

use App\Services\UserServices;

/**
 * Classe responsavel por fazer validaçòes de entrada e saída de dados para Login.
 */
class LoginController {

    private string $usuario;
    private string $senha;
    private UserServices $service;

    public function __construct(string $user, string $password)
    {
        $this->usuario = $user;
        $this->senha = $password;
        $this->service = new UserServices();
    }

    public function validate(){

        $user_id = $this->service->login($this->usuario,$this->senha);

        if (!$user_id)
            return false;

        return $user_id;
    }
}


