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

    public function get_tags(string $filtros){

        if (empty($filtros)){
            try{
                return $this->model->find_all();
            }
            catch(Exception $e){
                throw new Exception("$e", 1);
            }
        }

        try{
            
            return [($this->model->find_by_field('rfid',$filtros))];
        }
        catch(Exception $e){
            throw new Exception($e,2);
        }
    }


}