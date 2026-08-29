<?php
namespace App\Views;
use App\Controllers\LoginController;
use Exception;

class Login {

    public static function view(LoginController $loginctrl){
        $mensagem = "";
        $id = null;

        if ($_SERVER["REQUEST_METHOD"] === "POST"){
            $usuario = $_POST['usuario'] ?? '';
            $senha = $_POST['senha'] ?? '';
        }

        if ((empty($usuario)) || (empty($senha))) {
            $mensagem = "Preencha todos os campos!";
        }
        else{

            try{
                $id = $loginctrl->validate();
            }
            catch(Exception $e){

                $mensagem = "Erro interno ao efetuar o login! $e";
            }

            if (!($id)){

                $mensagem = "Nome de usuario ou senha incorretos";
                    
            } 
            else{
                Home::view();
                return;
            }
        }
    ?>

    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
    </head>
    <body>
        
        <pre>
            <p><?=$mensagem?></p>
        <form action="" method="post">

            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" placeholder="Seu nome de usuario...">

            <label for="senha">Senha</label>
            <input type="password" name="senha" id="senha" placeholder="Sua senha...">

            <input type="submit">

        </form>
        </pre>

    </body>
    </html>
<?php
    }
}