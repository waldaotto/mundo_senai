<?php

declare(strict_types=1);

namespace App\Routes;

/**
 * Router mínimo — registra rotas GET/POST e despacha para Controller@metodo.
 */
class Router
{
    /** @var array<string, array<string, callable|array>> */
    private array $rotas = [
        'GET'  => [],
        'POST' => [],
    ];

    public function get(string $caminho, $acao): void
    {
        $this->rotas['GET'][$this->normalizar($caminho)] = $acao;
    }

    public function post(string $caminho, $acao): void
    {
        $this->rotas['POST'][$this->normalizar($caminho)] = $acao;
    }

    private function normalizar(string $caminho): string
    {
        $caminho = rtrim($caminho, '/');
        return $caminho === '' ? '/' : $caminho;
    }

    public function despachar(string $metodo, string $uri): void
    {
        $uri = $this->normalizar($uri);
        $acao = $this->rotas[$metodo][$uri] ?? null;

        if ($acao === null) {
            http_response_code(404);
            $view = BASE_PATH . '/App/Views/404.php';
            if (is_file($view)) {
                require $view;
            } else {
                echo '404 - Página não encontrada.';
            }
            return;
        }

        if (is_array($acao)) {
            [$classe, $metodoAcao] = $acao;
            $controller = new $classe();
            $controller->$metodoAcao();
            return;
        }

        // Closure
        $acao();
    }
}
