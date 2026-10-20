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

    public function get_media_peso(){
        $stmt = $this->cursor->query("SELECT AVG(peso) as media FROM {$this->table} WHERE peso > 0");
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    public function get_tags_mensais(){

        $stmt = $this->cursor->query("SELECT COUNT(*) as total FROM {$this->table} WHERE MONTH(data_hora) = MONTH(CURRENT_DATE()) AND YEAR(data_hora) = YEAR(CURRENT_DATE())");
        return $stmt ? ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0) : 0;
    }
}
