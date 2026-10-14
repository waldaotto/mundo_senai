<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Triagem 4.0 SENAI</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background-color: #0d1117;
      background-image: 
        radial-gradient(at 20% 20%, rgba(35, 134, 54, 0.15) 0px, transparent 50%),
        radial-gradient(at 80% 80%, rgba(88, 166, 255, 0.12) 0px, transparent 50%);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-card {
      background: rgba(22, 27, 34, 0.85);
      backdrop-filter: blur(12px);
      border: 1px solid #30363d;
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
      animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .login-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .login-logo {
      height: 45px;
      margin-bottom: 15px;
    }

    .login-header h1 {
      color: #f0f6fc;
      font-size: 1.4rem;
      font-weight: 700;
    }

    .login-header p {
      color: #8b949e;
      font-size: 0.85rem;
      margin-top: 5px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      color: #c9d1d9;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .form-input {
      width: 100%;
      background-color: #0d1117;
      border: 1px solid #30363d;
      color: #ffffff;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 0.95rem;
      outline: none;
      transition: all 0.3s ease;
    }

    .form-input:focus {
      border-color: #58a6ff;
      box-shadow: 0 0 12px rgba(88, 166, 255, 0.25);
    }

    .btn-login {
      width: 100%;
      background-color: #238636;
      color: #ffffff;
      border: none;
      padding: 14px;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
      box-shadow: 0 4px 15px rgba(35, 134, 54, 0.3);
    }

    .btn-login:hover {
      background-color: #2ea043;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(35, 134, 54, 0.4);
    }

    /* ESTILIZAÇÃO DO SPAN DE MENSAGEM */
    body > span,
    form span {
      display: <?= !empty($mensagem) ? 'block' : 'none' ?>;
      margin-top: 18px;
      padding: 12px 16px;
      background-color: rgba(248, 81, 73, 0.15);
      border: 1px solid rgba(248, 81, 73, 0.4);
      color: #f85149;
      border-radius: 10px;
      font-size: 0.88rem;
      font-weight: 600;
      text-align: center;
      max-width: 420px;
      width: 100%;
      box-shadow: 0 4px 15px rgba(248, 81, 73, 0.15);
      animation: fadeIn 0.3s ease-out;
    }

    @keyframes popIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
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
    <span><?=$mensagem?></span>
</body>
</html>