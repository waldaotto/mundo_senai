<?php

namespace App\Models;
use App\Core\Model;

class RfidModel extends Model{
    protected string $table = 'passagem_tag';

    public function ultima_rfid()
{
    $stmt = $this->cursor->query(
        "SELECT id,rfid, data_hora
         FROM {$this->table}
         ORDER BY ABS(TIMESTAMPDIFF(SECOND, data_hora, NOW()))
         LIMIT 1"
    );
    return $stmt->fetchAll();
}    
}