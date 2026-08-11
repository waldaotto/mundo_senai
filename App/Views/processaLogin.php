<?php
include __DIR__.'../../../vendor/autoload.php';
use App\Controllers\LoginController;

$usuario = $_POST["usuario"];
$senha = $_POST["senha"];

if ((strlen($usuario) == 0) || (strlen($senha) == 0)) {
?>
    <script>
        
        alert("Preencha todos os campos para efetuar o login!");
        window.location.href = "login.php";

    </script>

<?php
return;
}

$login = new LoginController($usuario,$senha);


if (!$login->validate()){
    ?>
    <script>
        
        alert("Senha ou nome incorretos!");
        window.location.href = "login.php";

    </script>
<?php
    return;
}

header("Location: Home.php");
exit();
?>