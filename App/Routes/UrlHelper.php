<?php

declare(strict_types=1);

namespace App\Routes;

/**
 * UrlHelper — monta URLs absolutas do site a partir da raiz,
 * para que os links funcionem independente da pasta onde o projeto
 * for hospedado (ex: /mundo_senai/ ou raiz do domínio).
 */
class UrlHelper
{
    public static function base(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base = rtrim(str_replace('index.php', '', $script), '/');
        return $base;
    }

    public static function url(string $caminho = ''): string
    {
        $caminho = ltrim($caminho, '/');
        $base = self::base();
        return ($base === '' ? '' : $base) . '/' . $caminho;
    }

    public static function asset(string $caminho): string
    {
        return self::url('Public/Assets/' . ltrim($caminho, '/'));
    }
}
