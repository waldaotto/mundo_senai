<?php

include_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\LoginController;

if (session_status() !== PHP_SESSION_ACTIVE){
    session_start();
}

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [LoginController::class, 'index']);

$router->post('/login', [LoginController::class, 'login']);

$basePath = '/mundo_senai';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = str_replace($basePath, '', $uri);

if ($uri === '') {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);
