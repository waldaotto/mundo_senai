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
}