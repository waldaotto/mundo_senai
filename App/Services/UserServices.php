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

        $usuario = $this->user_model->find_by_field("nome",$user);

        if (!$usuario)
            return false;
        
        if ($usuario["senha"] != $password)
            return false;

        session_start();
        $_SESSION["user_id"] = $usuario["id"];
        return $usuario["id"];

    }

    public function logout() {

        session_abort();
        header("Location: ../Views/login.php");
        exit();
    }

}
