<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Services\RfidService;

class RfidController extends Controller
 {

    public RfidService $service;
    public mixed $last_insert;
    public int $last_id;

    public function __construct() {
        $this->service = new RfidService();
        $last = $this->service->model->ultima_rfid();
    }

    public function index(){
        // if (isset($_SESSION["user_id"])){
        //     $this->redirect("/mundo_senai/");
        // }        
        $this->view('header',['title'=>'Leituras']);
        $this->view('leituras');
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
        $peso = $dados['peso'] ?? null;

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
        
        $this->last_id = $this->service->recebe_tag($rfid,$peso);

        }

    public function pooling(){
        header('Content-Type: application/json');

        $ultimo = $this->service->model->ultima_rfid()[0] ?? null;

        if (!$ultimo) {
            echo json_encode(['novo' => false]);
            return;
        }

        $depois = isset($_GET['depois']) ? (int)$_GET['depois'] : null;

        if ($depois !== null && (int)$ultimo['id'] <= $depois) {
            echo json_encode(['novo' => false]);
            return;
        }

        echo json_encode([
            'novo'=> true,
            'id'=> (int)$ultimo['id'],
            'rfid'=> $ultimo['rfid'],
            'peso'=> $ultimo['peso'],
            'data_hora' => $ultimo['data_hora'],
        ]);
    }
 }