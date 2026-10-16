<?php

namespace App\Controllers;

use App\Core\Controller;

class CaixasController extends Controller {

    public function cadastro() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $destino = $_POST['destino'] ?? '';
        $tag_uid = $_POST['tag_uid'] ?? '';
        $peso = $_POST['peso'] ?? 0;
        $processo = $_POST['processo_atual'] ?? 'Em Triagem';

        // Lógica de inserção no banco de dados aqui...

        header('Location: ' . url_to('/caixas'));
        exit;
    }
}

    public function index() {
        // Exemplo fictício de dados (substitua pela consulta ao banco de dados)
        $caixas = [
            [
                'id' => 101,
                'destino' => 'Setor A - Linha 1',
                'processo_atual' => 'Pesagem',
                'tag_uid' => 'E200001A99001',
                'peso' => '12.4',
                'data_hora' => '16/10/2026 10:15'
            ],
            [
                'id' => 102,
                'destino' => 'Depósito Central',
                'processo_atual' => 'Aguardando Envio',
                'tag_uid' => 'E200001A99002',
                'peso' => '8.1',
                'data_hora' => '16/10/2026 09:40'
            ]
        ];

        // Se houver busca por ID na URL
        if (!empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $caixas = array_filter($caixas, function($c) use ($search) {
                return strpos((string)$c['id'], $search) !== false;
            });
        }

        $this->view('header',['title'=>'Caixas']);
        $this->view('caixas', ['caixas' => $caixas]);
    }
}