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

        if (empty($fitlros['campo']) || empty($filtros['valor'])){
            try{
                return $this->model->find_all();
            }
            catch(Exception $e){
                throw new Exception("$e", 1);
            }
        }

        try{
            return $this->model->find_by_field($filtros['campo'],$filtros['valor']);
        }
        catch(Exception $e){
            throw new Exception($e,2);
        }
    }


}