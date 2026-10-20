<?php

namespace App\Services;

use App\Models\TagsModel;
use App\Models\CaixasModel;

class CaixasServices {

    protected TagsModel $tags;
    protected CaixasModel $model;

    public function __construct()
    {
        $this->tags = new TagsModel();
        $this->model = new CaixasModel();

    }

    public function cadastrar_caixa(int $destino_id, string $tag_uid, int $processo_id){

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
            'processo'
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


}