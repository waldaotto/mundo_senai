<?php

namespace App\Models;
use PDO;
use App\Core\Model;

class RfidModel extends Model{
    protected string $table = 'passagem_tag';

    public function ultima_rfid(){
        $stmt = $this->cursor->query(
            "SELECT id, rfid, data_hora,peso
            FROM {$this->table}
            ORDER BY id DESC
            LIMIT 1"
        );
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;  
    }    
}
