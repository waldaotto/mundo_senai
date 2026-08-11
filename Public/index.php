<?php

include __DIR__.'/../vendor/autoload.php';

use App\Models\UserModel;

?>

<?php

if (isset($_SESSION["user_id"])){



    header("Location: ../App/Views/Home.php");
    exit();
}  
else {
    
    header("Location: ../App/Views/login.php");
    exit();
}

