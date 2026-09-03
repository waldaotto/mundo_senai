<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Services\RfidService;
class RfidController extends Controller
 {

    public RfidService $service;

    public function __construct() {
        $this->service = new RfidService();
    }
    

    public function store(){
        $conteudo = file_get_contents(
            'php://input'
        );

        $dados = json_decode(
            $conteudo,
            true
        );

        if (!is_array($dados)){

            http_response_code(400);

            echo json_decode([
                'status'=>'erro',
                'mensagem'=>'JSON invalido'
            ]);

            return;
        }

        $rfid = $dados['rfid'] ?? null;

        if(!$rfid){

            http_response_code(400);

            echo json_decode([
                'status'=>'erro',
                'mensagem'=>'RFID nao informado'
            ]);

            return;
        }

        echo json_encode([
            'status'=>'OK',
            'mensagem'=>'RFID recebido!'
        ]);

        $this->service->recebe_tag($rfid);
    }
}