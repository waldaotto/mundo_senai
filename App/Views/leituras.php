<head>
  <link rel="stylesheet" href="<?=CSS_PATH.'leituras.css'?>">
  <script src="<?=JS_PATH.'leituras.js'?>" defer></script>
</head>

<body>
  <main>
    <h2 style="color: #f0f6fc; font-size: 1.6rem; margin-bottom: 5px;">Leitura RFID em Tempo Real</h2>
    <p style="color: #8b949e; margin-bottom: 25px;">Dados capturados instantaneamente pelo leitor de triagem.</p>

    <div class="rfid-display-card">
      <div class="rfid-header">
        <i class="fa-solid fa-wifi rfid-radar-icon"></i>
        <h3 style="color: #f0f6fc; margin: 0; font-size: 1.3rem;">Última Leitura Capturada</h3>
      </div>

      <div class="rfid-data-group">
        <div class="rfid-data-row">
          <span class="rfid-label">ID do Registro</span>
          <span class="rfid-value" id="id">--</span>
        </div>

        <div class="rfid-data-row">
          <span class="rfid-label">Código RFID (UID)</span>
          <span class="rfid-value" id="rfid" style="color: #2f81f7;">Aguardando Tag...</span>
        </div>

        <div class="rfid-data-row">
          <span class="rfid-label">Data e Hora</span>
          <span class="rfid-value" id="data" style="color: #3fb950; font-size: 1.05rem;">--/--/----</span>
        </div>

        <div class="rfid-data-row">
          <span class="rfid-label">Peso</span>
          <span class="rfid-value" id="peso" style="color: #3fb950; font-size: 1.05rem;">--/--/----</span>
        </div>
      </div>

      <div class="status-badge">
        <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> Monitorização em tempo real ativa
      </div>
    </div>
  </main>
</body>
</html>
<ElicitationsGroup>

</ElicitationsGroup>