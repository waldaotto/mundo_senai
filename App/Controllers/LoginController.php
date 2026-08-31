<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\UserServices;
use App\Views\Login;

/**
 * Classe responsavel por fazer validaçòes de entrada e saída de dados para Login.
 */
class LoginController extends Controller {


    private string $usuario;
    private string $senha;
    private UserServices $service;

    public function __construct()
    {
        $this->service = new UserServices();
    }

    public function index($data = ""){

        if (isset($_SESSION["user_id"])){
            $this->redirect("/");
        }
        
        $this->view('login',['mensagem'=>$data]);
    }

    /**
     * Realiza a validação de login.
     */
    public function validate(){

        $this->set_post();
        $mensagem = "";

        if ((empty($this->usuario)) || (empty($this->senha))) {
            $mensagem = "Preencha todos os campos!";
        }

        else
            {

            $user_id = $this->service->login($this->usuario,$this->senha);

            if (!($user_id)){
                $mensagem = "Nome de usuario ou senha incorretos";         
            }
            else {
                $this->redirect('/');
            }
        }
        $this->index($mensagem);
    }

    public function set_post(){

        if ($_SERVER["REQUEST_METHOD"] === "POST"){
            $this->usuario = $_POST['usuario'] ?? '';
            $this->senha = $_POST['senha'] ?? '';
        }

        return;
    }
}


