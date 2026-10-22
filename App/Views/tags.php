<head>
  <link rel="stylesheet" href="<?=CSS_PATH.'tags.css?v=1.1'?>">
  <script src="<?=JS_PATH.'tagsModal.js'?>" defer></script>
</head>

<h2 class="page-title">Registro de TAGs Cadastradas</h2>
<p class="page-subtitle">Lista do histórico de etiquetas e seus respectivos status do sistema.</p>

<!-- Form de Pesquisa -->
<form method="post" class="search-form">
  <input type="text" class="searchtag" id="searchtag" name="searchtag" placeholder="Pesquisar por TAGs...">
  <input type="submit" value="Pesquisar" class="btn-searchtag">
</form>

<!-- Toolbar de Criação -->
 <?php if ($_SESSION['role']>=5):?>
<div class="toolbar">
  <button type="button" id="abrirModal" class="btn-searchtag">
    <i class="fa-solid fa-plus"></i> Criar Tag
  </button>
</div>
<?php endif; ?>

<!-- Modal 1: Cadastrar Tag -->
<div id="modalTag" class="modal" hidden>
  <div class="modal-box">
    <button type="button" id="fecharModal" class="modal-close" aria-label="Fechar">&times;</button>
    <h3 class="modal-title">Cadastrar Tag</h3>

    <form action="<?=url_to('/tags/cadastro')?>" method="post" class="modal-form">
      <label for="uid">UID</label>
      <input type="text" id="uid" name="uid" required autocomplete="off" placeholder="Digite o UID da TAG">
      <button type="submit">Cadastrar</button>
    </form>
  </div>
</div>

<!-- Modal 2: Editar Tag -->
<div id="modalEditarTag" class="modal" hidden>
  <div class="modal-box">
    <button type="button" onclick="fecharModalEdicao()" class="modal-close" aria-label="Fechar">&times;</button>
    <h3 class="modal-title">Editar Tag</h3>

    <form action="<?=url_to('/tags/editar')?>" method="post" class="modal-form">
      <input type="hidden" id="edit_id" name="id">

      <label for="edit_uid">UID da TAG</label>
      <input type="text" id="edit_uid" name="uid" required autocomplete="off">

      <button type="submit">Salvar Alterações</button>
    </form>
  </div>
</div>

<!-- Modal 3: Confirmar Exclusão -->
<div id="modalExcluirTag" class="modal" hidden>
  <div class="modal-box">
    <button type="button" onclick="fecharModalExclusao()" class="modal-close" aria-label="Fechar">&times;</button>
    <h3 class="modal-title">Excluir Tag</h3>
    <p style="color: #8b949e; font-size: 0.9rem; margin-bottom: 16px;">
      Tem certeza que deseja excluir a TAG <strong id="delete_tag_label" style="color: #f0f6fc;"></strong>? Esta ação não poderá ser desfeita.
    </p>

    <form action="<?=url_to('/tags/excluir')?>" method="post" class="modal-form">
      <input type="hidden" id="delete_id" name="id">
      <button type="submit" class="btn-danger">Confirmar Exclusão</button>
    </form>
  </div>
</div>

<!-- Tabela de TAGs -->
<div class="custom-table-card">
  <table class="custom-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>DESTINO / TAG UID</th>
        <th>DATA / HORA</th>
        <th style="width: 160px; text-align: center;">AÇÕES</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!empty($tags) && is_array($tags)){ ?>
      <?php foreach ($tags as $tag){ ?>
        <?php if (is_array($tag)){ 
          $tagId = $tag['id'] ?? 'N/A';
          $tagUid = !empty($tag['uid']) ? $tag['uid'] : (!empty($tag['tag_uid']) ? $tag['tag_uid'] : "Não definido");
        ?>
          <tr>
            <td>#<?= htmlspecialchars($tagId) ?></td>
            <td><strong class="tag-uid"><?= htmlspecialchars($tagUid) ?></strong></td>
            <td class="tag-date"><?= htmlspecialchars($tag['data_hora'] ?? $tag['data_cadastro'] ?? '-') ?></td>
            <td>
              <div class="actions-cell" style="justify-content: center;">
                <button 
                  type="button" 
                  class="btn-action btn-edit" 
                  onclick="abrirModalEdicao('<?= htmlspecialchars($tagId) ?>', '<?= htmlspecialchars($tagUid) ?>')"
                  title="Editar TAG"
                >
                  <i class="fa-solid fa-pen-to-square"></i> Editar
                </button>

                <button 
                  type="button" 
                  class="btn-action btn-delete" 
                  onclick="abrirModalExclusao('<?= htmlspecialchars($tagId) ?>', '<?= htmlspecialchars($tagUid) ?>')"
                  title="Excluir TAG"
                >
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php } ?>
      <?php } ?>
    <?php } else { ?>
      <tr>
        <td colspan="4" class="empty-row">Nenhuma TAG encontrada no banco.</td>
      </tr>
    <?php } ?>
  </tbody>
  </table>
</div>

<script>
  function abrirModalEdicao(id, uid) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_uid').value = uid === 'Não definido' ? '' : uid;
    document.getElementById('modalEditarTag').removeAttribute('hidden');
  }

  function fecharModalEdicao() {
    document.getElementById('modalEditarTag').setAttribute('hidden', 'true');
  }

  function abrirModalExclusao(id, uid) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_tag_label').innerText = uid && uid !== 'Não definido' ? '#' + id + ' (' + uid + ')' : '#' + id;
    document.getElementById('modalExcluirTag').removeAttribute('hidden');
  }

  function fecharModalExclusao() {
    document.getElementById('modalExcluirTag').setAttribute('hidden', 'true');
  }
</script>