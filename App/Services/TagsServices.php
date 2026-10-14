<?php

namespace App\Services;
use App\Models\TagsModel;
use Exception;

class TagsServices {

    public TagsModel $model;

    public function __construct()
    {
        $this->model = new TagsModel;
    }

    public function get_tags(mixed $filtros){

        if (empty($filtros)){
            try{
                return $this->model->find_all();
            }
            catch(Exception $e){
                throw new Exception("$e", 1);
            }
        }

        try{
            return [($this->model->find($filtros))];
        }
        catch(Exception $e){
            throw new Exception($e,2);
        }


    }

    public function create_tag(string $uid){
        try{
            $this->model->create(["id"=>null,"uid"=>$uid,"data_cadastro"=>date("Y-m-d H:i:s")]);
        }catch(Exception $e){
            echo "Um erro ocorreu: ".$e;
        }
    }


}