<?php

namespace App\Services;

use App\Models\TagsModel;
use App\Models\CaixasModel;
use App\Models\RegiaoModel;
use App\Models\ProcessoModel;

class CaixasServices {

    protected TagsModel $tags;
    protected CaixasModel $model;
    protected RegiaoModel $regiao;
    protected ProcessoModel $processo;

    public function __construct()
    {
        $this->tags = new TagsModel();
        $this->model = new CaixasModel();
        $this->regiao = new RegiaoModel();
        $this->processo = new ProcessoModel();

    }

    public function cadastrar_caixa(int $destino_id, string $tag_uid,string $processo){

        $tag = $this->get_tag_id(($tag_uid));
        
        if (empty($tag)){
            return [];
        }

        try{
            $insert = $this->model->create([
            'id'=>null,
            'tag_id'=>$tag['id'],
            'regiao_id'=>$destino_id,
            'user_id'=>$_SESSION['user_id'],
            'data_criada'=>date("Y-m-d H:i:s"),
            'processo'=>strtolower(trim($processo))
            ]);
        }catch (Exception $e) {
            
            throw new Exception($e->getMessage(), 2);
        }

        return $insert;
    }   

    private function get_tag_id(string $uid){

        $tag = $this->tags->find_by_field('uid',$uid);
        return is_array($tag) ? $tag : [];

    }

    public function get_regiao(int $regiao_id){

        $regiao = $this->regiao->find($regiao_id);
        return empty($regiao) ? [] : $regiao;
    }

    public function get_processo(int $processo_id){

        $processo = $this->processo->find(trim($processo_id));
        return empty($processo) ? [] : $processo;
    }

    public function find_all(){
        return $this->model->find_all();
    }

    public function find(int $id){
        return $this->model->find($id);
    }

    public function get_tag(int $id){
        return $this->tags->find($id);
    }
    
    public function get_disponiveis(){
        return $this->tags->find_disponiveis() ?? [];
    }
}
