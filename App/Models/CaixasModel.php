<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

class CaixasModel extends Model {

    protected string $table = "caixa";


    function get_caixas_enviadas(){

        $stmt = $this->cursor->query("SELECT COUNT(*) as total FROM {$this->table} WHERE processo = 'finalizado'");
        return $stmt ? ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0) : 0;
    }

    function get_caixas_processo(){

        $stmt = $this->cursor->query("SELECT COUNT(*) as total FROM {$this->table} WHERE (processo != 'Finalizado' AND processo IS NOT NULL)");
        return $stmt ? ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0) : 0;
    }

    function get_frequente_regiao(){
        $stmt = $this->cursor->query("SELECT regiao_id, COUNT(*) as qtd FROM {$this->table} WHERE regiao_id IS NOT NULL AND regiao_id != '' GROUP BY regiao_id ORDER BY qtd DESC LIMIT 1");
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    function get_distribuicao_regiao(){
        $stmt = $this->cursor->query("SELECT regiao_id, COUNT(*) as total FROM {$this->table} WHERE regiao_id IS NOT NULL AND regiao_id != '' GROUP BY regiao_id ORDER BY total DESC LIMIT 5");
        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    function get_ultimas_mov(){
        $stmt = $this->cursor->query("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 6");
        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    public function atualizar_peso_por_tag(int $tag_id, float $peso): int {
        $sql = "
            UPDATE {$this->table}
            SET peso = :peso
            WHERE tag_id = :tag_id
            AND (processo IS NULL OR processo <> 'finalizado')
        ";

        $stmt = $this->cursor->prepare($sql);
        $stmt->execute([
            ':peso'   => $peso,
            ':tag_id' => $tag_id,
        ]);

        return $stmt->rowCount();
    }

    public function contar_ativas_por_tag(int $tag_id): int {
    $sql = "
        SELECT COUNT(*)
        FROM {$this->table}
        WHERE tag_id = :tag_id
          AND (processo IS NULL OR processo <> 'finalizado')
    ";
    $stmt = $this->cursor->prepare($sql);
    $stmt->execute([':tag_id' => $tag_id]);

    return (int) $stmt->fetchColumn();
}
}