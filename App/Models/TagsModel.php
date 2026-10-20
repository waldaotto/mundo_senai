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

    
}