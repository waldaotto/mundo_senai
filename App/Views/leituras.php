<?php require_once 'header.php'; ?>

<style>
  .rfid-display-card {
    background: linear-gradient(145deg, #161b22, #21262d);
    border: 1px solid #30363d;
    border-radius: 16px;
    padding: 35px;
    max-width: 600px;
    margin: 20px auto 0 auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  }

  .rfid-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 25px;
    border-bottom: 1px solid #30363d;
    padding-bottom: 15px;
  }

  .rfid-radar-icon {
    font-size: 2rem;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.15); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
  }

  .rfid-data-group {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .rfid-data-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #0d1117;
    padding: 14px 20px;
    border-radius: 10px;
    border: 1px solid #30363d;
  }

  .rfid-label {
    color: #8b949e;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
  }

  .rfid-value {
    color: #58a6ff;
    font-size: 1.25rem;
    font-weight: bold;
    font-family: monospace;
  }
</style>
<script src=<?=JS_PATH."leituras.js"?>></script>

<h2 class="tab-title">Leitura RFID</h2>
<p class="tab-subtitle">Dados capturados em tempo real pelo leitor de triagem.</p>

<div class="rfid-display-card">
  <div class="rfid-header">
    <span class="rfid-radar-icon">📡</span>
    <h3 style="color: #f0f6fc; margin: 0; font-size: 1.3rem;">Última Leitura Capturada</h3>
  </div>

  <div class="rfid-data-group">
    <div class="rfid-data-row">
      <span class="rfid-label">ID do Registro</span>
      <span class="rfid-value" id="id">--</span>
    </div>

    <div class="rfid-data-row">
      <span class="rfid-label">Código RFID (UID)</span>
      <span class="rfid-value" style="color: #2f81f7;" id="rfid">--</span>
    </div>

    <div class="rfid-data-row">
      <span class="rfid-label">Data e Hora</span>
      <span class="rfid-value" style="color: #3fb950; font-size: 1.05rem;" id="data">--</span>
    </div>
  </div>
</div>

</div> <!-- fecha dashboard-container -->
</div> <!-- fecha app-viewport -->
</body>
</html>