<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Triagem 4.0 SENAI</title>
  <!-- Carregamento direto usando url_to -->
  <link rel="stylesheet" href="<?= url_to('/Assets/css/login.css') ?>">
</head>
<body>

  <div class="login-card">
    <div class="login-header">
      <svg class="login-logo" viewBox="0 0 300 65" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 8H30V13H0V8ZM0 20H30V25H0V20ZM0 32H30V37H0V32ZM0 44H30V49H0V44ZM0 56H30V61H0V56Z" fill="#FFFFFF"/>
        <path d="M270 8H300V13H270V8ZM270 20H300V25H270V20ZM270 32H300V37H270V32ZM270 44H300V49H270V44ZM270 56H300V61H270V56Z" fill="#FFFFFF"/>
        <path d="M42 54C38 54 36 51 36 47C36 41 42 38 49 36C57 34 60 32 60 28C60 25 57 23 52 23C45 23 39 26 36 29L31 20C36 16 45 13 54 13C66 13 73 18 73 27C73 35 66 38 57 40C50 42 48 43 48 46C48 48 51 49 55 49C62 49 69 45 73 42L78 51C72 56 63 59 52 59" fill="#FFFFFF"/>
        <path d="M80 15H115V24H93V32H112V40H93V50H116V59H80V15Z" fill="#FFFFFF"/>
        <path d="M121 15H134L150 42V15H162V59H149L133 32V59H121V15Z" fill="#FFFFFF"/>
        <path d="M185 15H199L214 59H200L197 49H178L174 59H162L185 15ZM193 39L188 24L182 39H193Z" fill="#FFFFFF"/>
        <path d="M220 15H233V59H220V15Z" fill="#FFFFFF"/>
      </svg>
      <h1>Triagem 4.0</h1>
      <p>Acesse o painel do sistema para continuar</p>
    </div>

    <form method="post">
      <div class="form-group">
        <label for="usuario">Usuário / Matrícula</label>
        <input type="text" id="usuario" name="usuario" class="form-input" placeholder="Digite seu usuário" required autocomplete="off">
      </div>

      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" class="form-input" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn-login">Entrar no Sistema</button>
    </form>
  </div>

  <?php if (!empty($mensagem)): ?>
    <span class="error-msg"><?= htmlspecialchars($mensagem) ?></span>
  <?php endif; ?>

</body>
</html>