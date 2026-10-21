<?php

namespace App\Models;
use App\Core\Model;
use PDO;

class TagsModel extends Model {

    protected string $table = 'tags';

    public function get_n_tags(){

        $stmt = $this->cursor->query("SELECT COUNT(*) AS total FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    public function find_disponiveis(): array {
        $sql = "
            SELECT t.id, t.uid, t.data_cadastro
            FROM tags t
            WHERE NOT EXISTS (
                SELECT 1 FROM caixa c
                WHERE c.tag_id = t.id
                AND (c.processo IS NULL OR c.processo <> 'finalizado')
            )
        ";
        return $this->cursor->query($sql)->fetchAll();
    }
}