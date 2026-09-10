<?php
/**
 * index.php
 * Front Controller — ponto de entrada único da aplicação.
 * Todas as requisições HTTP passam por aqui (via .htaccess).
 */

declare(strict_types=1);

// Exibir erros durante desenvolvimento. Em produção, mude para 0/off.
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();

define('BASE_PATH', __DIR__);

// Autoload simples (PSR-4 "na mão", sem depender do composer)
spl_autoload_register(function (string $class) {
    // Namespaces usados: App\Controllers\X, App\Core\X, App\Models\X, App\Routes\X
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/App/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use App\Routes\Router;

$router = new Router();
require BASE_PATH . '/App/Routes/web.php';

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->despachar($metodo, $uri);
