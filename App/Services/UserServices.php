<?php

namespace App\Services;

use App\Models\UserModel;

class UserServices {

    private UserModel $user_model;

    public function __construct()
    {
        $this->user_model = new UserModel();
    }

    public function login(string $user, string $password){

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $usuario = $this->user_model->find_by_field("nome",$user);

        if (!$usuario)
            return false;
        
        if(!password_verify($password,password_hash($usuario['senha'],PASSWORD_DEFAULT))){
            return false;
        }

        $_SESSION["usuario_id"] = $usuario["id"];
            return $usuario["id"];

    }

    public function logout() {

        session_abort();
        header("Location: ../Views/login.php");
        exit();
    }

}
