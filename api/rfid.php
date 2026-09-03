<?php

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$temperatura = $data['temperatura'] ?? null;
$umidade = $data['umidade'] ?? null;

if ($temperatura === null || $umidade === null) {
    http_response_code(400);

    echo json_encode([
        "erro" => "Dados inválidos"
    ]);

    exit;
}

echo json_encode([
    "status" => "ok",
    "temperatura" => $temperatura,
    "umidade" => $umidade
]);