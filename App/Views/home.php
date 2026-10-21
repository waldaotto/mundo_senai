<head>
  <link rel="stylesheet" href="<?= CSS_PATH ?>home.css?v=1.3">

  <!-- Ordem importa: scripts com defer executam na ordem em que aparecem.
       O Chart.js precisa vir antes do home.js. -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4" defer></script>
  <script src="<?= JS_PATH ?>home.js?v=1.0" defer></script>
</head>

<main class="dashboard-container">

  <!-- CABEÇALHO DA PÁGINA -->
  <div class="dash-header">
    <div>
      <span class="sub-title-red">PAINEL DE CONTROLE</span>
      <h1 class="main-title">Triagem e rastreamento de carga</h1>
    </div>
    <div class="period-badge">
      <i class="fa-regular fa-calendar"></i> Período: Período Atual
    </div>
  </div>

  <!-- CARDS DE MÉTRICAS (KPIs) -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-top">
        <span class="kpi-label">TAGS LIDAS NO MÊS</span>
        <div class="kpi-icon blue"><i class="fa-solid fa-tags"></i></div>
      </div>
      <div class="kpi-value"><?= number_format($kpis['tags_mes'] ?? 0, 0, ',', '.') ?></div>
      <div class="kpi-sub green"><i class="fa-solid fa-arrow-up"></i> Dados em tempo real</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-top">
        <span class="kpi-label">TOTAL DE CAIXAS ENVIADAS</span>
        <div class="kpi-icon purple"><i class="fa-solid fa-box-archive"></i></div>
      </div>
      <div class="kpi-value"><?= number_format($kpis['caixas_enviadas'] ?? 0, 0, ',', '.') ?></div>
      <div class="kpi-sub">acumulado no mês</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-top">
        <span class="kpi-label">CAIXAS EM TRÂNSITO</span>
        <div class="kpi-icon light-blue"><i class="fa-solid fa-truck-fast"></i></div>
      </div>
      <div class="kpi-value"><?= number_format($kpis['caixas_transito'] ?? 0, 0, ',', '.') ?></div>
      <div class="kpi-sub green"><i class="fa-solid fa-circle icon-dot"></i> ativas no fluxo agora</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-top">
        <span class="kpi-label">MÉDIA DE PESO ENVIADO</span>
        <div class="kpi-icon green"><i class="fa-solid fa-weight-hanging"></i></div>
      </div>
      <div class="kpi-value"><?= ($kpis['media_peso']) ?? '0,0' ?> <span class="unit">g</span></div>
      <div class="kpi-sub">por caixa</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-top">
        <span class="kpi-label">DESTINO MAIS FREQUENTE</span>
        <div class="kpi-icon orange"><i class="fa-solid fa-location-dot"></i></div>
      </div>
      <div class="kpi-value kpi-value--text">
        <?= htmlspecialchars($this->regiao_id($kpis['destino_frequente']) ?? 'N/A') ?>
      </div>
      <div class="kpi-sub">maior volume do mês</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-top">
        <span class="kpi-label">SENSORES CONECTADOS</span>
        <div class="kpi-icon green-bright"><i class="fa-solid fa-network-wired"></i></div>
      </div>
      <div class="kpi-value"><?= $kpis['sensores'] ?? '0/0' ?></div>
      <div class="kpi-sub green"><i class="fa-solid fa-circle icon-dot"></i> todos online</div>
    </div>
  </div>

  <!-- ÁREA DE GRÁFICOS -->
  <div class="charts-grid">
    <div class="chart-card">
      <h3>Distribuição por destino</h3>
      <p class="chart-subtitle">Participação das caixas enviadas</p>
      <div class="donut-wrapper">
        <!-- Os dados do PHP chegam ao JS por data-attribute (sem script inline) -->

        <?php
          $dadosGrafico = [];
          foreach (($destinos ?? []) as $dt) {
              $dadosGrafico[] = [
                  'destino' => $this->regiao_id($dt['regiao_id']) ?: 'Não informado',
                  'total'   => (int) ($dt['total'] ?? 0),
              ];
          }
        ?>
        <canvas
          id="chartDestinos"
          data-destinos="<?= htmlspecialchars(json_encode($dadosGrafico, JSON_UNESCAPED_UNICODE) ?: '[]', ENT_QUOTES, 'UTF-8') ?>"
        ></canvas>
      </div>
    </div>

    <div class="chart-card">
      <h3>Volume de leituras e caixas</h3>
      <p class="chart-subtitle">Fluxo diário nos últimos 7 dias</p>
      <div class="line-wrapper">
        <canvas id="chartVolume"></canvas>
      </div>
    </div>
  </div>

  <!-- TABELA DE ÚLTIMAS MOVIMENTAÇÕES -->
  <div class="table-card">
    <div class="table-card-header">
      <div>
        <h3>Últimas movimentações capturadas</h3>
        <p class="chart-subtitle">Leituras recebidas dos sensores RFID nas últimas horas</p>
      </div>
      <span class="time-badge"><i class="fa-regular fa-clock"></i> atualizado em tempo real</span>
    </div>

    <table class="custom-table">
      <thead>
        <tr>
          <th>CAIXA</th>
          <th>UID DA TAG</th>
          <th>DESTINO</th>
          <th>PROCESSO</th>
          <th>PESO</th>
          <th>SENSOR</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($ultimas_movimentacoes) && is_array($ultimas_movimentacoes)): ?>
          <?php foreach ($ultimas_movimentacoes as $mov): ?>
            <tr>
              <td><strong class="cell-strong">CX-<?= str_pad((string) $mov['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
              <td class="code-font"><?= htmlspecialchars($mov['tag_uid'] ?? $mov['tag'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($mov['destino'] ?? 'Não informado') ?></td>
              <td><span class="badge-processo"><?= htmlspecialchars($mov['processo_atual'] ?? $mov['processo'] ?? 'Preparação') ?></span></td>
              <td><?= htmlspecialchars($mov['peso'] ?? '0') ?> kg</td>
              <td><span class="sensor-online"><i class="fa-solid fa-circle"></i> Online</span></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="table-empty">Nenhuma movimentação registrada recentemente.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>