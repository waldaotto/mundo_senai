<?php


if (!isset($_SESSION["usuario_id"])) {

    $basePath = '/mundo_senai';

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $uri = str_replace($basePath, '', $uri);

    if ($uri === '') {
        $uri = '/login';
    }

    $method = $_SERVER['REQUEST_METHOD'];

    $router->url($uri, $method);

    var_dump($router);

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    merda
</body>
</html>