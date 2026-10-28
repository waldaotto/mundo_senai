<head>
  <link rel="stylesheet" href="<?=CSS_PATH.'caixas.css'?>">
  <script src="<?= JS_PATH ?>caixas.js?v=4.0" defer></script>
</head>

<div class="caixas-container">
  <h2 class="page-title">Gerenciamento de Caixas</h2>
  <p class="page-subtitle">Acompanhe as caixas registradas, seus destinos e o status do processo.</p>

  <!-- BARRA DE PESQUISA E BOTÃO CRIAR -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
    <form method="GET" action="<?=url_to('/caixas')?>" class="search-section" style="flex: 1;">
      <input 
        type="integer" 
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

  <!-- GRID DE CARDS COM DADOS EM BASE64 -->
  <div class="caixas-grid">
    <?php if (!empty($caixas) && is_array($caixas)): ?>
      <?php foreach ($caixas as$caixa): ?>
        <?php
        $destino = $this->find_regiao($caixa['regiao_id']) ?: 'Não informado';
        $tag_uid = $this->find_tag($caixa['tag_id']) ?: 'N/A';

        $caixa['destino'] =$destino;
        $caixa['tag_uid'] =$tag_uid;

        $jsonCaixaBase64 = base64_encode(json_encode($caixa));
        ?>
        <div class="caixa-card card-item-caixa" data-caixa="<?= $jsonCaixaBase64 ?>">
          <div class="card-header">
            <span class="caixa-id"><i class="fa-solid fa-box"></i> Caixa #<?= htmlspecialchars($caixa['id']) ?></span>
            <span class="badge-processo"><?= htmlspecialchars(strtoupper($caixa['processo'])) ?? 'INDEFINIDO' ?></span>
          </div>
          <div class="card-body">
            <div class="info-row">
              <span>Destino:</span>
              <strong><?= htmlspecialchars($destino) ?></strong>
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

<!-- FORMULÁRIO OCULTO PARA EXCLUSÃO VIA POST -->
<form id="formDeletarCaixa" action="<?=url_to('/caixas/deletar')?>" method="POST" style="display: none;">
  <input type="hidden" name="id" id="deletar_caixa_id">
</form>

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

      <?php $tag_disponivel =$this->tags_disponiveis(); ?>
      <div class="form-group">
        <label for="tag_uid">TAG RFID</label>
        <?php if (empty($tag_disponivel)): ?>
          <input type="text" id="tag_uid" name="tag_uid" placeholder="Nenhuma tag disponível" required="" autocomplete="off" disabled>
        <?php else: ?>
          <select id="tag_uid" name="tag_uid">
            <?php foreach($tag_disponivel as$tag): ?>
              <option value="<?=$tag['id']?>"><?=$tag['uid']?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="processo_id">Processo Inicial</label>
        <select id="processo_id" name="processo_id">
          <option value="preparacao">Preparação</option>
          <option value="montagem">Montagem</option>
          <option value="pesagem">Pesagem</option>
          <option value="esteira">Esteira</option>
          <option value="carga">Carga</option>
          <option value="transporte">Transporte</option>
          <option value="finalizado">Finalizado</option>
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

    <!-- RODAPÉ COM AÇÕES -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #30363d;">
      <button type="button" class="search-btn" style="background-color: #da3633; border-color: #f85149; color: #fff;" onclick="executarExclusaoDirecta()">
        <i class="fa-solid fa-trash-can"></i> Excluir
      </button>

      <button type="button" class="btn-primary" style="background-color: #d29922; border-color: #bb8009; color: #fff;" onclick="executarEdicaoDirecta()">
        <i class="fa-solid fa-pen-to-square"></i> Editar
      </button>
    </div>
  </div>
</div>

<!-- MODAL 3: EDITAR CAIXA (POST) -->
<div id="modalEditarCaixa" class="modal-overlay" onclick="fecharModalOverlay(event, 'modalEditarCaixa')">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title" id="e_modal_title"><i class="fa-solid fa-pen-to-square"></i> Editar Caixa</h3>
      <button type="button" class="close-btn" onclick="fecharModal('modalEditarCaixa')">&times;</button>
    </div>
    
    <form action="<?=url_to('/caixas/editar')?>" method="POST" class="modal-form">
      <input type="hidden" name="caixa_id" id="e_caixa_id">

      <div class="form-group">
        <label for="e_destino_id">Destino</label>
        <select id="e_destino_id" name="destino_id">
          <option value="1">Norte</option>
          <option value="2">Nordeste</option>
          <option value="3">Centro-Oeste</option>
          <option value="4">Sudeste</option>
          <option value="5">Sul</option>
        </select>
      </div>

      <div class="form-group">
        <label for="e_processo_id">Processo Atual</label>
        <select id="e_processo_id" name="processo_id">
          <option value="preparacao">Preparação</option>
          <option value="montagem">Montagem</option>
          <option value="pesagem">Pesagem</option>
          <option value="esteira">Esteira</option>
          <option value="carga">Carga</option>
          <option value="transporte">Transporte</option>
          <option value="finalizado">Finalizado</option>
        </select>
      </div>

      <button type="submit" class="btn-primary" style="justify-content: center; margin-top: 8px;">
        Salvar Alterações
      </button>
    </form>
  </div>
</div>