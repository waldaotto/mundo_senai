<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Model Produto — lê o catálogo de produtos da vitrine a partir de
 * data/produtos.json. Sem banco de dados, para manter o projeto simples.
 */
class Produto
{
    private const ARQUIVO = BASE_PATH . '/data/produtos.json';

    /**
     * Retorna todos os produtos cadastrados.
     */
    public static function todos(): array
    {
        if (!is_file(self::ARQUIVO)) {
            return [];
        }
        $conteudo = file_get_contents(self::ARQUIVO);
        $dados = json_decode($conteudo, true);
        return is_array($dados) ? $dados : [];
    }

    /**
     * Retorna apenas os produtos em destaque (campo "destaque" = true).
     */
    public static function destaques(): array
    {
        return array_values(array_filter(self::todos(), function ($p) {
            return !empty($p['destaque']);
        }));
    }

    /**
     * Busca um produto pelo ID.
     */
    public static function porId(int $id): ?array
    {
        foreach (self::todos() as $produto) {
            if ((int)$produto['id'] === $id) {
                return $produto;
            }
        }
        return null;
    }

    /**
     * Lista as categorias distintas presentes no catálogo.
     */
    public static function categorias(): array
    {
        $categorias = array_unique(array_column(self::todos(), 'categoria'));
        sort($categorias);
        return $categorias;
    }
}
