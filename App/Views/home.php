<?php require_once 'header.php'; ?>

<style>
  /* CARDS DE METRICAS */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: linear-gradient(145deg, #161b22, #21262d);
    border: 1px solid #30363d;
    border-radius: 14px;
    padding: 22px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .stat-card:hover {
    transform: translateY(-4px);
    border-color: #58a6ff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
  }

  .stat-card h3 {
    font-size: 0.8rem;
    color: #8b949e;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 12px;
  }

  .stat-card .val-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .stat-card .number {
    font-size: 2.8rem;
    font-weight: 800;
    line-height: 1;
  }

  .stat-card .card-icon {
    font-size: 2.2rem;
    opacity: 0.8;
  }

  /* ESTRUTURA DE TABELAS */
  .tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 25px;
  }

  .panel-box {
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  }

  .panel-box h2 {
    font-size: 1.15rem;
    color: #f0f6fc;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
  }

  .data-table th {
    background-color: #21262d;
    color: #8b949e;
    font-size: 0.8rem;
    text-transform: uppercase;
    padding: 12px 16px;
    border-bottom: 1px solid #30363d;
  }

  .data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #21262d;
    font-size: 0.9rem;
  }

  .data-table tr:hover {
    background-color: rgba(88, 166, 255, 0.05);
  }

  .badge-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    background: rgba(63, 185, 80, 0.15);
    color: #3fb950;
  }
</style>

<!-- CARDS COM DADOS REAIS DO BANCO -->
<div class="stats-grid">
  <div class="stat-card">
    <h3>TOTAL DE TAGS CADASTRADAS</h3>
    <div class="val-wrapper">
      <span class="number" style="color: #58a6ff; text-shadow: 0 0 15px rgba(88, 166, 255, 0.3);">
        <?= sprintf("%02d", $total_tags ?? count($tags ?? [])) ?>
      </span>
      <span class="card-icon">🏷️</span>
    </div>
  </div>

  <div class="stat-card">
    <h3>LEITURAS HOJE</h3>
    <div class="val-wrapper">
      <span class="number" style="color: #3fb950; text-shadow: 0 0 15px rgba(63, 185, 80, 0.3);">
        <?= sprintf("%02d", $leituras_hoje ?? 0) ?>
      </span>
      <span class="card-icon">📡</span>
    </div>
  </div>

  <div class="stat-card">
    <h3>SENSORES CONECTADOS</h3>
    <div class="val-wrapper">
      <span class="number" style="color: #d29922; text-shadow: 0 0 15px rgba(210, 153, 34, 0.3);">
        <?= sprintf("%03d", $sensores_conectados ?? 1) ?>
      </span>
      <span class="card-icon">🔌</span>
    </div>
  </div>
</div>

<div class="tables-grid">
  <!-- ÚLTIMAS LEITURAS RFID -->
  <div class="panel-box">
    <h2><span>📡</span> Últimas Leituras RFID</h2>
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
    <h2><span>🏷️</span> TAGs Cadastradas</h2>
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
            <td><span class="badge-status">✓ Ativo</span></td>
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

</main>
</body>
</html>