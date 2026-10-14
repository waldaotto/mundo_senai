<?php

namespace App\Controllers;
use App\Core\Controller;
use App\Models\TagsModel;
use App\Models\RfidModel;


class HomeController extends Controller {

    
    public function index(){

        if (!isset($_SESSION["user_id"])){
            $this->redirect("login");
        }

        $this->view('header',['title'=>'Home']);
        $this->view('home',$this->get_data());

    }

    public function get_data(){

        $md = new TagsModel();
        $data = [];
        $data['total_tags'] = $md->get_n_tags();
        return $data;
    }
}