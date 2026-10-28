<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\UserServices;

class UserController extends Controller {

    protected mixed $service;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->service = new UserServices();
    }

    public function index(){

        if ((int)$_SESSION['role']<10){
            echo "Sem acesso";
            $this->redirect('/home');
        }

        if (!isset($_SESSION["user_id"])){
            $this->redirect("/login");
        }

        $users = $this->loader();
      

        $this->view('header', ['title' => 'Usuarios']);
        $this->view('user',['usuarios'=>$users]);
    }

    public function loader(){
        return $this->service->loader();
    }

    function formatar_cpf(string $cpf): string {
    $cpf = preg_replace('/\D/', '', $cpf);

    return preg_replace(
        '/(\d{3})(\d{3})(\d{3})(\d{2})/',
        '$1.$2.$3-$4',
        $cpf
    );
}
}
