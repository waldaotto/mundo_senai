<?php

namespace App\Services;
use App\Models\RfidModel;

class RfidService{

    public RfidModel $model;

    public function __construct() {
        $this->model = new RfidModel();
    }

    public function recebe_tag(string $rfid){
        
        $this->model->create(
            [
            "id"=>null,
            "rfid"=>$rfid,
            "data_hora"=>date("Y-m-d H:i:s")
            ]
        );
    }
}