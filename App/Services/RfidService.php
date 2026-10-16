<?php

namespace App\Services;
use App\Models\RfidModel;

class RfidService{

    public RfidModel $model;

    public function __construct() {
        $this->model = new RfidModel();
    }

    public function recebe_tag(string $rfid,mixed $peso){
        
        return $this->model->create(
            [
            "id"=>null,
            "rfid"=>$rfid,
            "peso"=>$peso,
            "data_hora"=>date("Y-m-d H:i:s")
            ]
        );
    }
}