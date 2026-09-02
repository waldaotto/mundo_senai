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
        
        if(!password_verify($password,password_hash($usuario['senha'],PASSWORD_DEFAULT))){
            return false;
        }

        $_SESSION["user_id"] = $usuario["id"];
            return $usuario["id"];

    }

    public function logout() {

        session_destroy();
        
    }

}
