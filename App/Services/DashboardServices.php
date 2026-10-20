<?php

namespace App\Services;

use App\Models\TagsModel;
use App\Models\RfidModel;
use App\Models\ProcessoModel;
use App\Models\CaixasModel;
use Exception;
use PDO;

class DashboardServices {

    private mixed $TagsModel;
    private mixed $RfidModel;
    private mixed $CaixasModel;

    public function __construct() {
        // Usa o TagsTagsModel existente para reaproveitar a conexão de banco do projeto
        $this->TagsModel = new TagsModel();
        $this->RfidModel = new RfidModel();
        $this->CaixasModel = new CaixasModel();
    }

    public function get_kpis(): array {

        // return [
        //     'tags_mes' => 0, 'caixas_enviadas' => 0, 'caixas_transito' => 0,
        //     'media_peso' => '0,0', 'destino_frequente' => 'N/A', 'sensores' => '4/4'
        // ];

        try {
            // 1. Tags lidas no mês
            $tags_mes = $this->RfidModel->get_tags_mensais();

            // 2. Total de caixas enviadas (Processo 7 = Finalizado)
            $caixas_enviadas = $this->CaixasModel->get_caixas_enviadas();

            // 3. Caixas em trânsito
            $caixas_transito = $this->CaixasModel->get_caixas_processo();

            // 4. Média de peso
            $media_row = $this->RfidModel->get_media_peso();
            $media_peso = number_format((float)($media_row['media'] ?? 0), 1, ',', '.');

            // 5. Destino mais frequente
            $dest_row = $this->CaixasModel->get_frequente_regiao();
            $destino_frequente = $dest_row['regiao_id'] ?? 'Sem registros';

            // 6. Sensores Conectados
             $sensores_str = "4/4";

            return [
                'tags_mes'           => $tags_mes,
                'caixas_enviadas'    => $caixas_enviadas,
                'caixas_transito'    => $caixas_transito,
                'media_peso'         => $media_peso,
                'destino_frequente' => $destino_frequente,
                'sensores'          => $sensores_str
            ];

        } catch (Exception $e) {
            return [
                'tags_mes' => 0, 'caixas_enviadas' => 0, 'caixas_transito' => 0,
                'media_peso' => '0,0', 'destino_frequente' => 'N/A', 'sensores' => '0/0'
            ];
        }
    }

    public function get_distribuicao_destinos(): array {

        try {
            return $this->CaixasModel->get_distribuicao_regiao();
        } catch (Exception $e) {
            return [];
        }
    }

    public function get_ultimas_movimentacoes(): array {
        try {
            return $this->CaixasModel->get_ultimas_mov();
        } catch (Exception $e) {
            return [];
        }
    }
}