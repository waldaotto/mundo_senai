<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Views\Tags;
use App\Services\TagsServices;
use Exception;

class TagsController extends Controller {

    public array $tags;
    public TagsServices $service;

    public function __construct(){
        $this->service = new TagsServices;
    }

    public function index(){

        if (!isset($_SESSION["user_id"])){
            $this->redirect("login");
        }

        $this->render_tags();
        $data = ['tags'=>$this->tags];

        $this->view('header',['title'=>'Tags']);
        $this->view('tags',$data);
    }

    public function render_tags(){

        $filtros = $this->filtro_setter();

        try{
            $this->tags_setter($this->service->get_tags($filtros));
        }
        catch(Exception $e){
            echo "Ocorreu um erro durante a conexão: $e";
        }
    }

    public function tags_setter(array $value){
        $this->tags = $value;
    }

    public function filtro_setter(){

        if ($_SERVER["REQUEST_METHOD"] === "POST"){
            $filtro = $_POST['searchtag'] ?? '';
            return $filtro;
        }
        
        return null;
    }

    public function search(){
        $this->index();
    }

    public function create_tag(){

        if ($_SERVER["REQUEST_METHOD"] !== "POST"){
            $this->redirect("/tags");
        }

        if(empty($_POST['uid'])){
            $this->redirect("/tags");
        }

        $uid = $_POST['uid'];

        $this->service->create_tag($uid);
        $this->redirect("/tags");

    }

    // MÉTODO PARA EDITAR A TAG
    public function editar(){

        if ($_SERVER["REQUEST_METHOD"] !== "POST"){
            $this->redirect("/tags");
        }

        $id = $_POST['id'] ?? null;
        $uid = $_POST['uid'] ?? null;

        if ($id && $uid) {
            // Tenta executar no service (se o método existir no seu TagsServices)
            if (method_exists($this->service, 'update_tag')) {
                $this->service->update_tag($id, $uid);
            } elseif (method_exists($this->service, 'editar_tag')) {
                $this->service->editar_tag($id, $uid);
            }
        }

        $this->redirect("/tags");
    }

    // MÉTODO PARA EXCLUIR A TAG
    public function excluir(){

        if ($_SERVER["REQUEST_METHOD"] !== "POST"){
            $this->redirect("/tags");
        }

        $id = $_POST['id'] ?? null;

        if ($id) {
            // Tenta executar no service (se o método existir no seu TagsServices)
            if (method_exists($this->service, 'delete_tag')) {
                $this->service->delete_tag($id);
            } elseif (method_exists($this->service, 'excluir_tag')) {
                $this->service->excluir_tag($id);
            }
        }

        $this->redirect("/tags");
    }
}