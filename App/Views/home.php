<head>
  <link rel="stylesheet" href="<?=CSS_PATH.'home.css'?>">
</head>

<div class="stats-grid">
  <!-- TOTAL DE TAGS -->
  <div class="stat-card">
    <h3>TOTAL DE TAGS CADASTRADAS</h3>
    <div class="val-wrapper">
      <span class="number" style="color: #58a6ff;">
        <?= sprintf("%02d", $total_tags ?? 0) ?>
      </span>
      <i class="fa-solid fa-tag card-icon" style="color: #58a6ff;"></i>
    </div>
  </div>

  <!-- LEITURAS HOJE -->
  <div class="stat-card">
    <h3>LEITURAS HOJE</h3>
    <div class="val-wrapper">
      <span class="number" style="color: #3fb950;">
        <?= sprintf("%02d", $leituras_hoje ?? 0) ?>
      </span>
      <i class="fa-solid fa-wifi card-icon" style="color: #3fb950;"></i>
    </div>
  </div>

  <!-- SENSORES CONECTADOS -->
  <div class="stat-card">
    <h3>SENSORES CONECTADOS</h3>
    <div class="val-wrapper">
      <span class="number" style="color: #d29922;">
        <?= sprintf("%03d", $sensores_conectados ?? 1) ?>
      </span>
      <i class="fa-solid fa-plug card-icon" style="color: #d29922;"></i>
    </div>
  </div>
</div>

<div class="tables-grid">
  <!-- ÚLTIMAS LEITURAS RFID -->
  <div class="panel-box">
    <h2><i class="fa-solid fa-wifi"></i> Últimas Leituras RFID</h2>
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>RFID (UID)</th>
          <th>Data / Hora</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($ultimas_leituras) && is_array($ultimas_leituras)) { ?>
          <?php foreach ($ultimas_leituras as $leitura) { ?>
          <tr>
            <td>#<?= $leitura['id'] ?></td>
            <td><strong style="color: #58a6ff;"><?= htmlspecialchars($leitura['tag_uid'] ?? $leitura['rfid'] ?? 'N/A') ?></strong></td>
            <td style="color: #8b949e;"><?= date('d/m/Y H:i', strtotime($leitura['data_hora'])) ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="3" style="text-align: center; color: #8b949e; padding: 20px;">Nenhuma leitura registrada hoje.</td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <!-- TAGS CADASTRADAS -->
  <div class="panel-box">
    <h2><i class="fa-solid fa-tags"></i> TAGs Cadastradas</h2>
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tag UID</th>
          <th>Status</th>
          <th>Data</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($tags) && is_array($tags)) { ?>
          <?php foreach (array_slice($tags, 0, 5) as $tag) { ?>
          <tr>
            <td>#<?= $tag['id'] ?></td>
            <td><code><?= htmlspecialchars($tag['tag_uid'] ?? 'Não definido') ?></code></td>
            <td><span class="badge-status"><i class="fa-solid fa-check"></i> Ativo</span></td>
            <td style="color: #8b949e;"><?= isset($tag['data_hora']) ? date('d/m/Y', strtotime($tag['data_hora'])) : '-' ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="4" style="text-align: center; color: #8b949e; padding: 20px;">Nenhuma TAG cadastrada.</td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>