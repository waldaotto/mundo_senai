<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CaixasServices;

class CaixasController extends Controller {

    public CaixasServices $service;

    public function __construct()
    {
        $this->service = new CaixasServices();
    }

    public function index() {
        $caixas = $this->caixas_loader();

        $this->view('header', ['title' => 'Caixas']);
        $this->view('caixas', ['caixas' => $caixas]);
    }

    public function cadastro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ((int)($_SESSION['role'] ?? 0) < 5) {
                echo "Sem acesso";
                $this->redirect('/home');
                return;
            }

            $destino_id  = (int)($_POST['destino_id'] ?? 0);
            $tag_uid     = $_POST['tag_uid'] ?? '';
            $processo_id = $_POST['processo_id'] ?? '';

            if (!$destino_id || !$tag_uid || !$processo_id) {
                $this->redirect("/caixas");
                return;
            }

            $this->service->cadastrar_caixa($destino_id, $tag_uid, $processo_id);
            $this->redirect("/caixas");
        }
    }

    /**
     * Rota POST: /caixas/editar
     */
    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ((int)($_SESSION['role'] ?? 0) < 5) {
                echo "Sem acesso";
                $this->redirect('/home');
                return;
            }

            $caixa_id    = (int)($_POST['caixa_id'] ?? 0);
            $destino_id  = (int)($_POST['destino_id'] ?? 0);
            $processo_id = $_POST['processo_id'] ?? '';

            if ($caixa_id > 0 && $destino_id > 0 && !empty($processo_id)) {
                $this->service->editar_caixa($caixa_id, $destino_id, $processo_id);
            }

            $this->redirect("/caixas");
        }
    }

    /**
     * Rota POST: /caixas/deletar
     */
    public function deletar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ((int)($_SESSION['role'] ?? 0) < 5) {
                echo "Sem acesso";
                $this->redirect('/home');
                return;
            }

            $id = (int)($_POST['id'] ?? 0);

            if ($id > 0) {
                $this->service->deletar_caixa($id);
            }

            $this->redirect("/caixas");
        }
    }

    private function caixas_loader() {
        if (empty($_GET['search']) || !isset($_GET['search'])) {
            return $this->service->find_all();
        }

        $search = $_GET['search'];
        return $this->service->loader($search);
    }

    public function find_regiao(int $id) {
        $regiao = $this->service->get_regiao($id);
        return $regiao['regiao'] ?? 'Não informado';
    }

    public function find_processo(int $id) {
        $i = $this->service->get_processo($id);
        return $i['descricao'] ?? "Que";
    }

    public function find_tag(int $id) {
        $tag = $this->service->get_tag($id);
        return $tag['uid'] ?? 'N/A';
    }

    public function tags_disponiveis() {
        $tags = $this->service->get_disponiveis();
        return empty($tags) ? [] : $tags;
    }
}