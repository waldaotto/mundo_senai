<?php

namespace App\Core;
use App\Controllers\HomeController;
use App\Controllers\LoginController;

class Router
{
    private array $routes = [
        'GET'=>[],
        'POST'=>[]
    ];

    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch(string $uri, string $method): mixed
    {
        if (!isset($this->routes[$method])) {
            http_response_code(405);
            return 'Método não permitido';
        }

        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            return 'Página não encontrada';
        }

        [$controller, $action] = $this->routes[$method][$uri];

        $controller = new $controller();

        return $controller->$action();
    }
}