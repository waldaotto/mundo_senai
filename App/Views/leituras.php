<?php use App\Routes\UrlHelper; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel de Monitoramento — Estação de Controle</title>
  <link rel="stylesheet" href="<?= UrlHelper::asset('css/base.css') ?>">
  <link rel="stylesheet" href="<?= UrlHelper::asset('css/painel.css') ?>">
</head>
<body class="painel" data-api-url="<?= UrlHelper::url('/api/telemetria.php') ?>">
<div class="painel__wrapper">

  <div class="painel__topo">
    <div>
      <div class="painel__titulo">PAINEL DE MONITORAMENTO</div>
      <div class="painel__subtitulo">TELEMETRIA EM TEMPO REAL DA ESTAÇÃO DE CONTROLE</div>
    </div>
    <div class="painel__topo-direita">
      <a class="botao-voltar" href="<?= UrlHelper::url('/') ?>">← VOLTAR À LOJA</a>
      <span>VERSÃO 1.0.0</span>
      <span class="status-online"><span class="ponto-pulsante"></span> ONLINE</span>
    </div>
  </div>

  <div class="metricas">
    <div class="metrica">
      <div class="metrica__rotulo">Peso da carga</div>
      <div class="metrica__valor" id="m-peso">0.0 g</div>
    </div>
    <div class="metrica">
      <div class="metrica__rotulo">ID da caixa</div>
      <div class="metrica__valor" id="m-id">—</div>
    </div>
    <div class="metrica">
      <div class="metrica__rotulo">Tempo de operação</div>
      <div class="metrica__valor" id="m-tempo">00:00:00</div>
    </div>
    <div class="metrica">
      <div class="metrica__rotulo">Estado da estação</div>
      <div class="metrica__valor" id="m-estado">ATIVO</div>
    </div>
    <div class="metrica">
      <div class="metrica__rotulo">Destino</div>
      <div class="metrica__valor" id="m-destino">AGUARD.</div>
    </div>
  </div>

  <div class="painel__corpo">

    <div class="painel-box">
      <div class="painel-box__titulo">
        <span>STATUS DA ESTAÇÃO</span>
        <span class="selo-operacional"><span class="bolinha"></span> OPERACIONAL</span>
      </div>

      <div class="diagrama">
        <div class="diagrama__linha"></div>
        <div class="diagrama__linha-ativa" id="linha-ativa" style="width:0"></div>

        <div class="no-estacao" id="no-braco">
          <div class="no-estacao__caixa">🦾</div>
          <div class="no-estacao__rotulo">BRAÇO + RFID</div>
          <div class="no-estacao__sub">ESP-01</div>
        </div>

        <div class="no-estacao" id="no-balanca">
          <div class="no-estacao__caixa">⚖️</div>
          <div class="no-estacao__rotulo">BALANÇA</div>
          <div class="no-estacao__sub">ESP-01</div>
        </div>

        <div class="no-estacao" id="no-esteira">
          <div class="no-estacao__caixa">📦</div>
          <div class="no-estacao__rotulo">ESTEIRA</div>
          <div class="no-estacao__sub">ESP-02</div>
        </div>

        <div class="no-estacao no-destino" id="no-rj">
          <div class="no-estacao__caixa">📍</div>
          <div class="no-estacao__rotulo">RJ</div>
          <div class="no-estacao__sub">destino</div>
        </div>

        <div class="no-estacao no-destino" id="no-sc">
          <div class="no-estacao__caixa">📍</div>
          <div class="no-estacao__rotulo">SC</div>
          <div class="no-estacao__sub">destino</div>
        </div>
      </div>

      <div class="legenda-diagrama">
        <span><span class="bolinha bolinha--operacional"></span> Operacional</span>
        <span><span class="bolinha bolinha--alerta"></span> Alerta</span>
        <span><span class="bolinha bolinha--falha"></span> Falha</span>
      </div>
    </div>

    <div class="lateral">

      <div class="painel-box">
        <div class="painel-box__titulo"><span>ESTADO DOS ESP's</span></div>

        <div class="esp-item">
          <div>
            <div class="esp-item__nome">ESP-01</div>
            <div class="esp-item__sub">Braço · RFID · Balança</div>
          </div>
          <div class="esp-item__status esp-item__status--ok" id="esp1-status">REPOUSO</div>
        </div>

        <div class="esp-item">
          <div>
            <div class="esp-item__nome">ESP-02</div>
            <div class="esp-item__sub">Esteira · Desviador</div>
          </div>
          <div class="esp-item__status esp-item__status--ok" id="esp2-status">REPOUSO</div>
        </div>
      </div>

      <div class="painel-box">
        <div class="painel-box__titulo"><span>SEQUÊNCIA</span></div>
        <div id="lista-sequencia">
          <div class="sequencia-item" data-status="recepcao">
            <span class="bolinha"></span> RECEPÇÃO <span class="sequencia-item__esp">ESP-01</span>
          </div>
          <div class="sequencia-item" data-status="identificacao_id">
            <span class="bolinha"></span> LEITURA ID <span class="sequencia-item__esp">ESP-01</span>
          </div>
          <div class="sequencia-item" data-status="pesagem">
            <span class="bolinha"></span> PESAGEM <span class="sequencia-item__esp">ESP-01</span>
          </div>
          <div class="sequencia-item" data-status="decisao_destino">
            <span class="bolinha"></span> DECISÃO <span class="sequencia-item__esp">ESP-01</span>
          </div>
          <div class="sequencia-item" data-status="esteira">
            <span class="bolinha"></span> ESTEIRA <span class="sequencia-item__esp">ESP-02</span>
          </div>
          <div class="sequencia-item" data-status="desviada">
            <span class="bolinha"></span> DESVIO <span class="sequencia-item__esp">ESP-02</span>
          </div>
        </div>
      </div>

      <div class="painel-box">
        <div class="painel-box__titulo"><span>CONTADORES</span></div>
        <div class="contadores">
          <div class="contador-item">
            <div class="contador-item__valor" id="c-total">0</div>
            <div class="contador-item__rotulo">TOTAL</div>
          </div>
          <div class="contador-item">
            <div class="contador-item__valor contador-item__valor--rj" id="c-rj">0</div>
            <div class="contador-item__rotulo">RJ</div>
          </div>
          <div class="contador-item">
            <div class="contador-item__valor contador-item__valor--sc" id="c-sc">0</div>
            <div class="contador-item__rotulo">SC</div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="painel-box">
    <div class="painel-box__titulo"><span>LOG DE EVENTOS</span></div>
    <div class="log-eventos" id="log-eventos">
      <div class="log-linha">
        <span class="log-hora">—</span>
        <span class="log-esp">—</span>
        <span class="log-status">—</span>
        <span class="log-msg">Aguardando primeira leitura da estação...</span>
      </div>
    </div>
  </div>

</div>

<button class="botao-abortar" id="btn-abortar">🛑<br>ABORTAR</button>

<script src="<?= UrlHelper::asset('js/leituras.js') ?>"></script>
</body>
</html>
