<?php require_once 'header.php'; ?>

<style>
  .grid-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
  }

  .stat-card {
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
  }

  .stat-card h3 {
    font-size: 0.85rem;
    color: #8b949e;
    letter-spacing: 0.05em;
    margin-bottom: 12px;
  }

  .stat-card .number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #58a6ff;
  }
</style>

<h2 class="tab-title">Painel Principal - Triagem 4.0</h2>
<p class="tab-subtitle">Acompanhamento geral do sistema e métricas em tempo real.</p>

<div class="grid-stats">
  <div class="stat-card">
    <h3>TOTAL DE TAGS CADASTRADAS</h3>
    <div class="number"><?= is_array($tags ?? null) ? count($tags) : 0 ?></div>
  </div>
  <div class="stat-card">
    <h3>LEITURAS HOJE</h3>
    <div class="number" style="color: #3fb950;">128</div>
  </div>
  <div class="stat-card">
    <h3>SENSORES CONECTADOS</h3>
    <div class="number" style="color: #d29922;">3/3</div>
  </div>
</div>

</div> <!-- fecha dashboard-container -->
</div> <!-- fecha app-viewport -->
</body>
</html>