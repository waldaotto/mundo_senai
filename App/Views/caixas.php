<head>
  <link rel="stylesheet" href="<?=CSS_PATH.'caixas.css'?>">
  <script src="<?= JS_PATH ?>caixas.js?v=1.0" defer></script>
</head>

<div class="caixas-container">
  <h2 class="page-title">Gerenciamento de Caixas</h2>
  <p class="page-subtitle">Acompanhe as caixas registradas, seus destinos e o status do processo.</p>

  <!-- BARRA DE PESQUISA E BOTÃO CRIAR -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
    <form method="GET" action="<?=url_to('/caixas')?>" class="search-section" style="flex: 1;">
      <input 
        type="text" 
        name="search" 
        class="search-input" 
        placeholder="Pesquisar pelo ID da Caixa..." 
        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
      >
      <button type="submit" class="search-btn">
        <i class="fa-solid fa-magnifying-glass"></i> Pesquisar
      </button>
    </form>

    <button type="button" class="btn-primary" onclick="abrirModalCadastro()">
      <i class="fa-solid fa-plus"></i> Criar Caixa
    </button>
  </div>

  <!-- GRID DE CARDS -->
  <div class="caixas-grid">
    <?php if (!empty($caixas) && is_array($caixas)): ?>
      <?php foreach ($caixas as $caixa): ?>
        <div class="caixa-card" onclick='abrirModalDetalhes(<?= json_encode($caixa) ?>)'>
          <div class="card-header">
            <span class="caixa-id"><i class="fa-solid fa-box"></i> Caixa #<?= htmlspecialchars($caixa['id']) ?></span>
            <span class="badge-processo"><?= htmlspecialchars($caixa['processo_atual'] ?? $caixa['processo'] ?? 'Preparação') ?></span>
          </div>
          <div class="card-body">
            <div class="info-row">
              <span>Destino:</span>
              <strong><?= htmlspecialchars($caixa['destino'] ?? 'Não informado') ?></strong>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="color: #8b949e; text-align: center; grid-column: 1 / -1; padding: 40px;">
        Nenhuma caixa encontrada.
      </p>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL 1: CADASTRAR NOVA CAIXA (POST) -->
<div id="modalCadastroCaixa" class="modal-overlay" onclick="fecharModalOverlay(event, 'modalCadastroCaixa')">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fa-solid fa-box-archive"></i> Cadastrar Caixa</h3>
      <button type="button" class="close-btn" onclick="fecharModal('modalCadastroCaixa')">&times;</button>
    </div>
    
    <form action="<?=url_to('/caixas/cadastro')?>" method="POST" class="modal-form">
      <div class="form-group">
        <label for="destino_id">Destino</label>
        <select id="destino_id" name="destino_id">
          <option value="1">Norte</option>
          <option value="2">Nordeste</option>
          <option value="3">Centro-Oeste</option>
          <option value="4">Sudeste</option>
          <option value="5">Sul</option>
        </select>
      </div>

      <div class="form-group">
        <label for="tag_uid">TAG RFID</label>
        <input type="text" id="tag_uid" name="tag_uid" placeholder="Ex: E200001A99001" required autocomplete="off">
      </div>

      <div class="form-group">
        <label for="peso">Peso (kg)</label>
        <input type="number" step="0.01" id="peso" name="peso" placeholder="Ex: 12.5" required>
      </div>

      <!-- OPÇÕES DE PROCESSO ATUALIZADAS COM A BASE DE DADOS -->
      <div class="form-group">
        <label for="processo_id">Processo Inicial</label>
        <select id="processo_id" name="processo_id">
          <option value="1">Preparação</option>
          <option value="2">Montagem</option>
          <option value="3">Pesagem</option>
          <option value="4">Esteira</option>
          <option value="5">Carga</option>
          <option value="6">Transporte</option>
          <option value="7">Finalizado</option>
        </select>
      </div>

      <button type="submit" class="btn-primary" style="justify-content: center; margin-top: 8px;">
        Cadastrar Caixa
      </button>
    </form>
  </div>
</div>

<!-- MODAL 2: DETALHES DA CAIXA -->
<div id="modalCaixa" class="modal-overlay" onclick="fecharModalOverlay(event, 'modalCaixa')">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title" id="m_id">Caixa #--</h3>
      <button type="button" class="close-btn" onclick="fecharModal('modalCaixa')">&times;</button>
    </div>
    
    <div class="modal-details-grid">
      <div class="detail-item">
        <div class="detail-label">Destino</div>
        <div class="detail-value" id="m_destino">--</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Processo Atual</div>
        <div class="detail-value" id="m_processo">--</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">TAG RFID</div>
        <div class="detail-value" id="m_tag">--</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Peso</div>
        <div class="detail-value" id="m_peso">--</div>
      </div>

      <div class="detail-item" style="grid-column: span 2;">
        <div class="detail-label">Data / Hora de Registro</div>
        <div class="detail-value" id="m_data">--</div>
      </div>
    </div>
  </div>
</div>
