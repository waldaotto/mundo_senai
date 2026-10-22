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

    public function cadastro() {
        

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if ((int)$_SESSION['role']<5){
            echo "Sem acesso";
            $this->redirect('/home');
        }

        $destino_id = (int)$_POST['destino_id'] ?? '';
        $tag_uid = $_POST['tag_uid'] ?? '';
        $processo_id = $_POST['processo_id'] ?? '';

        if (!$destino_id || !$tag_uid || !$processo_id){
            return;
        }
       
        $i = $this->service->cadastrar_caixa($destino_id,$tag_uid,$processo_id);

        $this->redirect("/caixas");
    }
}

    public function index() {
        // Exemplo fictício de dados (substitua pela consulta ao banco de dados)
        $caixas = $this->caixas_loader();

        $this->view('header',['title'=>'Caixas']);
        $this->view('caixas', ['caixas' => $caixas]);
    }

    private function caixas_loader(){

        if(empty($_GET['search']) or !isset($_GET['search'])){
            return $this->service->find_all();

        }

        $search = $_GET['search'];


        $caixas = $this->service->loader($search);
        return $caixas;
  
    }

    public function find_regiao(int $id){
        return $this->service->get_regiao($id)['regiao'];
    }

    public function find_processo(int $id){
        $i = ($this->service->get_processo($id));
        return $i['descricao'] ?? "Que";
    }

    public function find_tag(int $id){
        return $this->service->get_tag($id)['uid'];
    }

    public function tags_disponiveis(){
        $tags = $this->service->get_disponiveis();

      return empty($tags) ? [] : $tags;
    }

}