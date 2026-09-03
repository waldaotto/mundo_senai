<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Services\RfidService;

class RfidController extends Controller
 {

    public RfidService $service;
    public array $last_insert;

    public function __construct() {
        $this->service = new RfidService();
        $last = $this->service->model->ultima_rfid();
        $this->last_insert = $last;
    }

    public function index(){
        // if (isset($_SESSION["user_id"])){
        //     $this->redirect("/mundo_senai/");
        // }
        
        $this->view('header',['title'=>'Leituras']);
        $this->view('leituras',($this->last_insert)[0]);
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

        $this->last_insert = $this->service->recebe_tag($rfid);
    }
}