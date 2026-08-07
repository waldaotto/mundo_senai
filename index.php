<?php

include_once __DIR__.'/App/Core/database/Connection.php';
use App\Core\database\Connection;

$con = new Connection()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <pre>
        <h2>Instancia</h2>
        <?php
        var_dump($con)
        ?>
    </pre>
    <pre>
        <h2>Conexao</h2>
        <?php
        var_dump($con->connect())
        ?>
    </pre>
    <pre>
        <h2>deconexao</h2>
        <?php
        var_dump($con->disconnect())
        ?>
    </pre>
</body>
</html>