<head>
  <link rel="stylesheet" href="<?=CSS_PATH.'tags.css'?>">
  <script src="<?=JS_PATH.'tagsModal.js'?>" defer></script>
</head>

<h2 class="page-title">Registro de TAGs Cadastradas</h2>
<p class="page-subtitle">Lista do histórico de etiquetas e seus respectivos status do sistema.</p>

<form method="post" class="search-form">
  <input type="text" class="searchtag" id="searchtag" name="searchtag" placeholder="Pesquisar por TAGs...">
  <input type="submit" value="Pesquisar" class="btn-searchtag">
</form>

<div class="toolbar">
  <button type="button" id="abrirModal" class="btn-searchtag">Criar Tag</button>
</div>

<div id="modalTag" class="modal" hidden>
  <div class="modal-box">
    <button type="button" id="fecharModal" class="modal-close" aria-label="Fechar">&times;</button>
    <h3 class="modal-title">Cadastrar Tag</h3>

    <form action="<?=url_to('/tags/cadastro')?>" method="post" class="modal-form">
      <label for="uid">UID</label>
      <input type="text" id="uid" name="uid" required autocomplete="off">
      <button type="submit">Cadastrar</button>
    </form>
  </div>
</div>

<div class="custom-table-card">
  <table class="custom-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>DESTINO / TAG UID</th>
        <th>DATA / HORA</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!empty($tags) && is_array($tags)){ ?>
      <?php foreach ($tags as $tag){ ?>
        <?php if (is_array($tag)){ ?>
          <tr>
            <td>#<?= $tag['id'] ?? 'N/A' ?></td>
            <td><strong style="color: #f0f6fc;"><?= !empty($tag['uid']) ? $tag['uid'] : (!empty($tag['tag_uid']) ? $tag['tag_uid'] : "Não definido") ?></strong></td>
            <td style="color: #8b949e;"><?= $tag['data_hora'] ?? $tag['data_cadastro'] ?? '-' ?></td>
          </tr>
        <?php } ?>
      <?php } ?>
    <?php } else { ?>
      <tr>
        <td colspan="3" style="text-align: center; color: #8b949e; padding: 30px;">Nenhuma TAG encontrada no banco.</td>
      </tr>
    <?php } ?>
  </tbody>
  </table>
</div>