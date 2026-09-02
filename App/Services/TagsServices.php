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
            
            return [($this->model->find_by_field('tag_uid',$filtros))];
        }
        catch(Exception $e){
            throw new Exception($e,2);
        }
    }


}