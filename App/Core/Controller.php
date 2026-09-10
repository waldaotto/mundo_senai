<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Controller base — fornece o método render() usado por todos os
 * controllers para carregar uma View dentro do layout (header/footer).
 */
abstract class Controller
{
    /**
     * Renderiza uma View dentro do layout padrão.
     *
     * @param string               $view  Nome do arquivo em App/Views (sem .php)
     * @param array<string, mixed> $dados Variáveis disponíveis dentro da View
     */
    protected function render(string $view, array $dados = []): void
    {
        // Extrai as variáveis para o escopo local (ex: $dados['produtos'] -> $produtos)
        extract($dados, EXTR_SKIP);

        $caminhoView = BASE_PATH . '/App/Views/' . $view . '.php';

        if (!is_file($caminhoView)) {
            http_response_code(500);
            echo "Erro: View '{$view}' não encontrada em App/Views/.";
            return;
        }

        require BASE_PATH . '/App/Views/header.php';
        require $caminhoView;
        require BASE_PATH . '/App/Views/footer.php';
    }

    /**
     * Renderiza uma View "solta", sem o layout (header/footer).
     * Útil para páginas com visual próprio, como o painel de telemetria.
     */
    protected function renderStandalone(string $view, array $dados = []): void
    {
        extract($dados, EXTR_SKIP);
        $caminhoView = BASE_PATH . '/App/Views/' . $view . '.php';

        if (!is_file($caminhoView)) {
            http_response_code(500);
            echo "Erro: View '{$view}' não encontrada em App/Views/.";
            return;
        }

        require $caminhoView;
    }

    /**
     * Responde em JSON e encerra a execução.
     */
    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
