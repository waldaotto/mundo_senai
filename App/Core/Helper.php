<?php
namespace App\Core;

class Helper {
    public static function uri(string $type):string{
        return parse_url($_SERVER['REQUEST_URI'])[$type];
    }
    
    public static function request():string{
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}
