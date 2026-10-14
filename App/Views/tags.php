

<style>
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
    transition: background 0.2s;
  }

  .btn-searchtag:hover {
    background-color: #2ea043;
  }

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

  .custom-table th, .custom-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #21262d;
  }

  .custom-table th {
    background-color: #21262d;
    color: #8b949e;
    font-size: 0.85rem;
    text-transform: uppercase;
  }
</style>

<h2 style="color: #f0f6fc; font-size: 1.6rem; margin-bottom: 5px;">Registro de TAGs Cadastradas</h2>
<p style="color: #8b949e; margin-bottom: 25px;">Lista do histórico de etiquetas e seus respectivos status do sistema.</p>

<form action="index.php?url=tags" method="post" class="search-form">
  <input type="text" class="searchtag" id="searchtag" name="searchtag" placeholder="Pesquisar por TAGs...">
  <input type="submit" value="Pesquisar" class="btn-searchtag">
</form>

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
        <tr>
          <td>#<?= $tag['id'] ?></td>
          <td><strong style="color: #f0f6fc;"><?= $tag['tag_uid'] == null ? "Não definido" : $tag['tag_uid']?></strong></td>
          <td style="color: #8b949e;"><?= $tag['data_hora'] ?></td>
        </tr>
        <?php } ?>
      <?php } else { ?>
        <tr>
          <td colspan="3" style="text-align: center; color: #8b949e; padding: 30px;">Nenhuma TAG encontrada no banco.</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

</main>
</body>
</html>