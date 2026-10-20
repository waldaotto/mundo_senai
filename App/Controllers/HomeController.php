<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TagsModel;
use App\Models\RfidModel;
use App\Services\DashboardServices;

class HomeController extends Controller {

    public function index(){

        if (!isset($_SESSION["user_id"])){
            $this->redirect("/login");
        }

        $this->view('header', ['title' => 'Home']);
        $this->view('home', $this->get_data());
    }

    public function get_data(){

        $md = new TagsModel();
        
        // Verifica se a classe existe antes de instanciar para evitar crashes
        if (class_exists('App\Services\DashboardServices')) {
            $dashboardService = new DashboardServices();
            $kpis = $dashboardService->get_kpis();
            $destinos = $dashboardService->get_distribuicao_destinos();
            $ultimas_movimentacoes = $dashboardService->get_ultimas_movimentacoes();
        } else {
            $kpis = [
                'tags_mes' => $md->get_n_tags(),
                'caixas_enviadas' => 0,
                'caixas_transito' => 0,
                'media_peso' => '0,0',
                'destino_frequente' => 'N/A',
                'sensores' => '8/8'
            ];
            $destinos = [];
            $ultimas_movimentacoes = [];
        }

        return [
            'total_tags'            => $md->get_n_tags(),
            'kpis'                  => $kpis,
            'destinos'              => $destinos,
            'ultimas_movimentacoes' => $ultimas_movimentacoes
        ];
    }
}