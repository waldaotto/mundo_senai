<head>
  <script src="<?=JS_PATH.'tagsModal.js'?>" defer></script>

  <style>
    /* ===== Busca ===== */
    .search-form {
      display: flex;
      gap: 12px;
      margin-bottom: 25px;
    }

    .searchtag {
      flex: 1;
      background-color: #161b22;
      border: 1px solid #30363d;
      color: #f0f6fc;
      padding: 12px 18px;
      border-radius: 8px;
      font-size: 1rem;
      outline: none;
      transition: border-color 0.2s;
    }

    .searchtag:focus {
      border-color: #58a6ff;
    }

    .btn-searchtag {
      background-color: #238636;
      color: #ffffff;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 1rem;
      transition: background 0.2s;
    }

    .btn-searchtag:hover {
      background-color: #2ea043;
    }

    /* ===== Cabeçalho da página ===== */
    .page-title {
      color: #f0f6fc;
      font-size: 1.6rem;
      margin-bottom: 5px;
    }

    .page-subtitle {
      color: #8b949e;
      margin-bottom: 25px;
    }

    .toolbar {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 15px;
    }

    /* ===== Tabela ===== */
    .custom-table-card {
      background-color: #161b22;
      border: 1px solid #30363d;
      border-radius: 12px;
      overflow: hidden;
    }

    .custom-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    .custom-table th,
    .custom-table td {
      padding: 16px 20px;
      border-bottom: 1px solid #21262d;
    }

    .custom-table th {
      background-color: #21262d;
      color: #8b949e;
      font-size: 0.85rem;
      text-transform: uppercase;
    }

    .tag-uid { color: #f0f6fc; }
    .tag-date { color: #8b949e; }
    .empty-row { text-align: center; color: #8b949e; padding: 30px !important; }

    /* ===== Modal ===== */
    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    .modal[hidden] {
      display: none;
    }

    .modal-box {
      position: relative;
      background: #161b22;
      border: 1px solid #30363d;
      color: #f0f6fc;
      padding: 24px;
      border-radius: 12px;
      width: 100%;
      max-width: 360px;
    }

    .modal-title {
      margin: 0 0 16px;
      font-size: 1.2rem;
    }

    .modal-close {
      position: absolute;
      top: 8px;
      right: 12px;
      border: 0;
      background: none;
      color: #8b949e;
      font-size: 24px;
      cursor: pointer;
    }

    .modal-close:hover {
      color: #f0f6fc;
    }

    .modal-form {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .modal-form label {
      color: #8b949e;
      font-size: 0.9rem;
    }

    .modal-form input[type="text"] {
      background: #0d1117;
      border: 1px solid #30363d;
      color: #f0f6fc;
      padding: 10px 12px;
      border-radius: 6px;
      font-size: 1rem;
      outline: none;
    }

    .modal-form input[type="text"]:focus {
      border-color: #58a6ff;
    }

    .modal-form button[type="submit"] {
      margin-top: 8px;
      background: #238636;
      color: #fff;
      border: 0;
      padding: 10px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 1rem;
    }

    .modal-form button[type="submit"]:hover {
      background: #2ea043;
    }
  </style>
</head>

<h2 class="page-title">Registro de TAGs Cadastradas</h2>
<p class="page-subtitle">Lista do histórico de etiquetas e seus respectivos status do sistema.</p>

<!-- Busca -->
<form method="post" class="search-form">
 
  <input type="text" class="searchtag" id="searchtag" name="searchtag" placeholder="Pesquisar por TAGs...">
  <input type="submit" value="Pesquisar" class="btn-searchtag">
</form>

<!-- Botão que abre o modal -->
<div class="toolbar">
  <button type="button" id="abrirModal" class="btn-searchtag">Criar Tag</button>
</div>

<!-- Modal -->
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

<!-- Tabela -->
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
      <?php if (!empty($tags) && is_array($tags)): ?>
        <?php foreach ($tags as $tag): ?>
          <tr>
            <td>#<?=$tag['id']?></td>
            <td><strong class="tag-uid"><?= $tag['uid']?></strong></td>
            <td class="tag-date"><?= $tag['data_cadastro'] ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="3" class="empty-row">Nenhuma TAG encontrada no banco.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</main>
</body>
</html>