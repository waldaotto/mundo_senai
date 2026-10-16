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

    public function get_tags(mixed $filtros): array
    {
        try {
            // 1. Sem filtro: busca todos
            if (empty($filtros)) {
                $resultado = $this->model->find_all();
                return is_array($resultado) ? $resultado : [];
            }

            // 2. Numérico: tenta por ID primeiro, depois por UID
            if (is_numeric($filtros)) {
                $item = $this->model->find((int) $filtros);

                if (!empty($item)) {
                    return $this->normalizar($item);
                }
            }

            // 3. Texto (ou numérico que não achou por ID): busca por UID
            $item = $this->model->find_by_field("uid", (string) $filtros);

            return $this->normalizar($item);

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 2);
        }
    }

    private function normalizar(mixed $item): array
    {
        if (empty($item) || !is_array($item)) {
            return [];
        }

        // Registro simples (tem 'id') vira lista com um item; senão já é lista
        return isset($item['id']) ? [$item] : $item;
    }

    public function create_tag(string $uid) {
        try {
            $this->model->create(["id" => null, "uid" => $uid, "data_cadastro" => date("Y-m-d H:i:s")]);
        } catch (Exception $e) {
            echo "Um erro ocorreu: " . $e->getMessage();
        }
    }
}