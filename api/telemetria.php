<?php
/**
 * api/telemetria.php
 * API de telemetria da Estação de Controle — Mundo SENAI.
 *
 * POST -> recebe payload JSON do ESP-01 ou ESP-02 e grava em data/status_esteira.json
 * GET  -> retorna a leitura atual em JSON para o painel (leituras.php)
 *
 * Hardware: 2 ESP32.
 *   ESP-01 = recepção (RFID) + pesagem (HX711) + braço robótico
 *   ESP-02 = esteira + sensor IR + servo desviador (destinos RJ ou SC)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('STATUS_FILE', __DIR__ . '/../data/status_esteira.json');

function estadoPadrao(): array
{
    return [
        'timestamp'     => date('c'),
        'id_caixa'      => null,
        'peso_g'        => 0.0,
        'destino'       => 'AGUARDANDO',
        'status'        => 'aguardando',
        'estacao_ativa' => true,
        'esp_origem'    => null,
        'contadores'    => [
            'total' => 0,
            'rj'    => 0,
            'sc'    => 0,
        ],
        'historico'     => [],
    ];
}

function lerEstado(): array
{
    if (!file_exists(STATUS_FILE)) {
        return estadoPadrao();
    }
    $conteudo = @file_get_contents(STATUS_FILE);
    if ($conteudo === false || $conteudo === '') {
        return estadoPadrao();
    }
    $estado = json_decode($conteudo, true);
    if (!is_array($estado)) {
        return estadoPadrao();
    }
    return array_replace_recursive(estadoPadrao(), $estado);
}

/**
 * Grava o estado de forma atômica (arquivo temporário + rename),
 * usando um lock exclusivo para evitar corrupção quando os 2 ESPs
 * escrevem quase ao mesmo tempo.
 */
function gravarEstado(array $estado): bool
{
    $estado['timestamp'] = date('c');
    $json = json_encode($estado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }

    $lockFile = STATUS_FILE . '.lock';
    $handle = fopen($lockFile, 'c');
    if ($handle === false) {
        return false;
    }

    flock($handle, LOCK_EX);
    $tmp = STATUS_FILE . '.tmp';
    $ok = @file_put_contents($tmp, $json) !== false && @rename($tmp, STATUS_FILE);
    flock($handle, LOCK_UN);
    fclose($handle);

    return $ok;
}

function validarDestino(?string $destino): string
{
    $destino = strtoupper(trim((string)$destino));
    return in_array($destino, ['RJ', 'SC', 'AGUARDANDO'], true) ? $destino : 'AGUARDANDO';
}

function validarStatus(?string $status): string
{
    $status = strtolower(trim((string)$status));
    $validos = [
        'aguardando', 'recepcao', 'identificacao_id', 'pesagem',
        'decisao_destino', 'esteira', 'desviada', 'entregue', 'erro',
    ];
    return in_array($status, $validos, true) ? $status : 'aguardando';
}

function responder(int $codigo, array $payload): void
{
    http_response_code($codigo);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// =====================================================
//  ROTEAMENTO
// =====================================================
$metodo = $_SERVER['REQUEST_METHOD'];

// ----------------------- GET -----------------------
if ($metodo === 'GET') {
    responder(200, [
        'sucesso' => true,
        'dados'   => lerEstado(),
    ]);
}

// ----------------------- POST -----------------------
if ($metodo === 'POST') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);

    if (!is_array($payload)) {
        responder(400, ['sucesso' => false, 'erro' => 'Payload JSON inválido ou ausente.']);
    }

    $espOrigem = trim((string)($payload['esp_origem'] ?? ''));
    if (!in_array($espOrigem, ['ESP-01', 'ESP-02'], true)) {
        responder(400, ['sucesso' => false, 'erro' => 'esp_origem inválido. Use ESP-01 ou ESP-02.']);
    }

    $estado = lerEstado();

    $idCaixa = isset($payload['id_caixa']) && $payload['id_caixa'] !== ''
        ? substr(trim((string)$payload['id_caixa']), 0, 32)
        : $estado['id_caixa'];

    $pesoG = isset($payload['peso_g']) ? round((float)$payload['peso_g'], 1) : $estado['peso_g'];
    $destino = validarDestino($payload['destino'] ?? null);
    $status  = validarStatus($payload['status'] ?? null);

    // ---- ESP-01: recepção (RFID) + pesagem ----
    if ($espOrigem === 'ESP-01') {
        $estado['id_caixa']   = $idCaixa;
        $estado['peso_g']     = $pesoG;
        $estado['destino']    = $destino;
        $estado['status']     = $status;
        $estado['esp_origem'] = $espOrigem;

        // Após pesar com destino já definido, sinaliza decisão para o ESP-02 agir
        if ($status === 'pesagem' && ($destino === 'RJ' || $destino === 'SC')) {
            $estado['status'] = 'decisao_destino';
        }
    }

    // ---- ESP-02: esteira + triagem (desviador) ----
    if ($espOrigem === 'ESP-02') {
        $estado['status']     = $status;
        $estado['esp_origem'] = $espOrigem;

        if ($status === 'desviada' || $status === 'entregue') {
            $estado['contadores']['total']++;
            if ($destino === 'RJ') {
                $estado['contadores']['rj']++;
            } elseif ($destino === 'SC') {
                $estado['contadores']['sc']++;
            }
            // Libera a estação para a próxima caixa
            $estado['id_caixa'] = null;
            $estado['peso_g']   = 0.0;
            $estado['destino']  = 'AGUARDANDO';
            $estado['status']   = 'aguardando';
        }
    }

    // ---- Histórico (últimos 50 eventos) ----
    array_unshift($estado['historico'], [
        'timestamp'  => date('c'),
        'esp_origem' => $espOrigem,
        'id_caixa'   => $idCaixa,
        'peso_g'     => $pesoG,
        'destino'    => $destino,
        'status'     => $status,
    ]);
    $estado['historico'] = array_slice($estado['historico'], 0, 50);

    if (isset($payload['estacao_ativa']) && is_bool($payload['estacao_ativa'])) {
        $estado['estacao_ativa'] = $payload['estacao_ativa'];
    }

    if (!gravarEstado($estado)) {
        responder(500, ['sucesso' => false, 'erro' => 'Falha ao gravar o arquivo de estado.']);
    }

    responder(200, ['sucesso' => true, 'dados' => $estado]);
}

responder(405, ['sucesso' => false, 'erro' => 'Método não permitido. Use GET ou POST.']);
