<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Services\RfidService;
use App\Services\CaixasServices;
use App\Models\RfidModel;
use PDO;



class RfidController extends Controller
 {

    public RfidService $service;
    public mixed $caixasService;
    public mixed $last_insert;
    public int $last_id;
    public mixed $model;

    public function __construct() {
        $this->service = new RfidService();
        $last = $this->service->model->ultima_rfid();
        $this->caixasService = new CaixasServices();
        $this->model = new RfidModel();
    }

    public function index(){
        // if (isset($_SESSION["user_id"])){
        //     $this->redirect("/mundo_senai/");
        // }        
        $this->view('header',['title'=>'Leituras']);
        $this->view('leituras');
    }

    public function store(){
        header('Content-Type: application/json; charset=utf-8');

        $conteudo = file_get_contents('php://input');
        $dados = json_decode($conteudo, true);

        if (!is_array($dados)) {
            http_response_code(400);
            echo json_encode([
                'status'   => 'erro',
                'mensagem' => 'JSON invalido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $rfid = trim($dados['rfid']) ?? null;
        $peso = $dados['peso'] ?? null;

        if (!$rfid) {
            http_response_code(400);
            echo json_encode([
                'status'   => 'erro',
                'mensagem' => 'RFID nao informado'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!is_numeric($peso) || (float) $peso <= 0) {
            http_response_code(400);
            echo json_encode([
                'status'   => 'erro',
                'mensagem' => 'Peso invalido'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        try {
            $tag = $this->caixasService->get_tag_id($rfid);
            $tag_id = (int) ($tag['id'] ?? 0);          // pega o ID de dentro do array

            if ($tag_id === 0) {
                http_response_code(404);
                echo json_encode([
                    'status'   => 'erro',
                    'mensagem' => 'TAG NAO CADASTRADA'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $qtd = $this->caixasService->contar_caixas_ativas($tag_id);

            if ($qtd === 0) {
                http_response_code(404);
                echo json_encode([
                    'status'   => 'erro',
                    'mensagem' => 'TAG NAO ESTA NA CAIXA'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($qtd > 1) {
                http_response_code(409);
                echo json_encode([
                    'status'   => 'erro',
                    'mensagem' => 'TAG EM MAIS DE UMA CAIXA ATIVA'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->caixasService->set_peso_por_tag($tag_id, (float) $peso);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status'   => 'erro',
                'mensagem' => 'ERRO AO DEFINIR PESO'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

         http_response_code(200);
        echo json_encode([
            
                'status'=>'OK',
                'mensagem'=>'Rfid recebido com sucesso'
            ], JSON_UNESCAPED_UNICODE);

            $this->service->recebe_tag($rfid,$peso);
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

    public function get_regiao_id(){
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['erro' => 'metodo nao permitido']);
            exit;
        }

        // PREENCHER: use a conexão que o seu Core/Model já fornece
        $pdo = $this->model->cursor;

        try {
            // 1) última tag que passou
            $passagem = $pdo->query(
                "SELECT rfid
                FROM passagem_tag
                ORDER BY data_hora DESC, id DESC
                LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);

            if (!$passagem) {
                http_response_code(404);
                echo json_encode(['erro' => 'nenhuma passagem de tag registrada']);
                exit;
            }

            // 2) caixa não finalizada com essa tag
            $stmt = $pdo->prepare(
                "SELECT c.regiao_id
                FROM caixa c
                JOIN tags t ON t.id = c.tag_id
                WHERE t.uid = :rfid
                    AND c.processo <> 'finalizado'
                ORDER BY c.id DESC
                LIMIT 1"
            );
            $stmt->execute([':rfid' => $passagem['rfid']]);
            $caixa = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$caixa) {
                http_response_code(404);
                echo json_encode(['erro' => 'tag nao esta em caixa ativa']);
                exit;
            }

            if ($caixa['regiao_id'] === null) {
                http_response_code(422);
                echo json_encode(['erro' => 'caixa sem regiao']);
                exit;
            }

            echo json_encode(['regiao_id' => (int) $caixa['regiao_id']]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'erro no banco']);
        }
    }
 }