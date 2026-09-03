<?php
namespace App\Routes;
use App\Core\Helper;

class Router {

    const CONTROLLER_NAMESPACE = 'App\\Controllers';

    public static function load(string $controller, string $method){

        try {
            $controllerNamespace = self::CONTROLLER_NAMESPACE.'\\'.$controller;
            if(!class_exists($controllerNamespace)){
                throw new \Exception("Controller {$controller} não existe.");
            }

            $controllerInstance = new $controllerNamespace;

            if(!method_exists($controllerInstance,$method)){
                throw new \Exception("O {$method} nao existe em {$controller}", 1);
            }

            return $controllerInstance->$method();

        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public static function routes():array {
        return [
            'get' => [
                '/mundo_senai/'=> fn()=> self::load('HomeController','index'),
                '/mundo_senai/login'=> fn()=> self::load('LoginController','index'),
                '/mundo_senai/tags'=> fn()=> self::load('TagsController','index')
            ],

            'post' => [
                '/mundo_senai/login'=> fn()=> self::load('LoginController','validate'),
                '/mundo_senai/tags'=> fn()=> self::load('TagsController','search'),
                '/mundo_senai/logout'=> fn()=> self::load('LoginController','logout'),
                '/mundo_senai/api/rfid'=> fn()=> self::load('RfidController','store')
            
            ]
        ];
    }

    public static function execute(){
        try {
            $routes = self::routes();
            $request = Helper::request();
            $uri = Helper::uri('path');

            if (!isset($routes[$request])){
                throw new \Exception("Requst Metodo não existe.");
            }

            if (!array_key_exists($uri,$routes[$request])){
                throw new \Exception("A rota {$uri} não existe.");
            }

            $router = $routes[$request][$uri];

            if (!is_callable($router)){
                throw new \Exception("Route not callable");
                
            }

            return $router();

        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }
}