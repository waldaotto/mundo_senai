<?php
namespace App\Controllers;
use App\Views\Tags;
use App\Services\TagsServices;
use Exception;

class TagsController {

    public array $tags;
    public TagsServices $service;

    public function __construct(){
        $this->service = new TagsServices;
    }

    public function index(){
        Tags::view($this);
    }

    public function render_tags(){

        $filtros = $this->filtro_setter();

        try{
            $this->tags_setter($this->service->get_tags($filtros));
        }
        catch(Exception $e){
            return "Ocorreu um erro durante a conexão: $e";
        }
    }

    public function tags_setter(array $value){
        $this->tags = $value;
    }

    public function filtro_setter(){

         if ($_SERVER["REQUEST_METHOD"] === "POST"){

            $filtro = [
                'campo'=> $_POST['campo'] ?? '',
                'valor'=> $_POST['valor'] ?? ''
            ]; 

            return $filtro;
        }
        
        return null;
    }
}