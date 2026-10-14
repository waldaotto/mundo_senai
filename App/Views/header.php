<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TRIAGEM 4.0 - SENAI</title>
  <style>
    /* Reset & Base Dark Mode */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background-color: #0d1117;
      color: #c9d1d9;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    /* HEADER COMPACTO E ANIMADO */
    .app-header {
      background-color: #161b22;
      border-bottom: 1px solid #30363d;
      padding: 12px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
      animation: slideDown 0.5s ease-out;
    }

    .brand-container {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    /* LOGO SENAI SEM FUNDO */
    .brand-logo {
      height: 38px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.2));
      transition: transform 0.3s ease;
    }

    .brand-logo:hover {
      transform: scale(1.05);
    }

    .brand-title {
      font-size: 1.1rem;
      font-weight: 800;
      color: #f0f6fc;
      letter-spacing: 0.05em;
      border-left: 2px solid #30363d;
      padding-left: 15px;
    }

    /* NAV BUTTONS COM ANIMACAO */
    .header-nav {
      display: flex;
      gap: 12px;
      background: #0d1117;
      padding: 5px;
      border-radius: 12px;
      border: 1px solid #30363d;
    }

    .nav-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 18px;
      color: #8b949e;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      border-radius: 8px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }

    .nav-btn:hover {
      color: #ffffff;
      background: #21262d;
      transform: translateY(-2px);
    }

    .nav-btn.active {
      color: #ffffff;
      background: #238636;
      box-shadow: 0 0 15px rgba(35, 134, 54, 0.4);
    }

    .nav-btn .icon {
      font-size: 1.1rem;
      transition: transform 0.3s ease;
    }

    .nav-btn:hover .icon {
      transform: scale(1.2);
    }

    /* STATUS DO USUARIO/SERVIDOR */
    .user-profile {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .status-indicator {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.88rem;
      color: #f0f6fc;
      font-weight: 600;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      background-color: #3fb950;
      border-radius: 50%;
      box-shadow: 0 0 10px #3fb950;
      animation: blink 2s infinite;
    }

    .logout-btn {
      color: #f85149;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      padding: 6px 12px;
      border: 1px solid rgba(248, 81, 73, 0.3);
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .logout-btn:hover {
      background: rgba(248, 81, 73, 0.15);
      border-color: #f85149;
    }

    /* KEYFRAMES DE ANIMAÇÃO */
    @keyframes slideDown {
      from { transform: translateY(-100%); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes blink {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.85); }
    }

    .main-container {
      flex: 1;
      max-width: 1400px;
      width: 100%;
      margin: 0 auto;
      padding: 25px 20px;
      animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <header class="app-header">
    <div class="brand-container">
      <!-- LOGO SENAI TRANSPARENTE VIA SVG EMBUTIDO DE ALTA QUALIDADE -->
      <svg class="brand-logo" viewBox="0 0 300 65" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 8H30V13H0V8ZM0 20H30V25H0V20ZM0 32H30V37H0V32ZM0 44H30V49H0V44ZM0 56H30V61H0V56Z" fill="#FFFFFF"/>
        <path d="M270 8H300V13H270V8ZM270 20H300V25H270V20ZM270 32H300V37H270V32ZM270 44H300V49H270V44ZM270 56H300V61H270V56Z" fill="#FFFFFF"/>
        <path d="M42 54C38 54 36 51 36 47C36 41 42 38 49 36C57 34 60 32 60 28C60 25 57 23 52 23C45 23 39 26 36 29L31 20C36 16 45 13 54 13C66 13 73 18 73 27C73 35 66 38 57 40C50 42 48 43 48 46C48 48 51 49 55 49C62 49 69 45 73 42L78 51C72 56 63 59 52 59" fill="#FFFFFF"/>
        <path d="M80 15H115V24H93V32H112V40H93V50H116V59H80V15Z" fill="#FFFFFF"/>
        <path d="M121 15H134L150 42V15H162V59H149L133 32V59H121V15Z" fill="#FFFFFF"/>
        <path d="M185 15H199L214 59H200L197 49H178L174 59H162L185 15ZM193 39L188 24L182 39H193Z" fill="#FFFFFF"/>
        <path d="M220 15H233V59H220V15Z" fill="#FFFFFF"/>
      </svg>
      <span class="brand-title">TRIAGEM 4.0</span>
    </div>

    <!-- BOTÕES NAVEGADOR COMPACTOS -->
    <?php $current_page = basename($_SERVER['REQUEST_URI']); ?>
    <nav class="header-nav">
      <a href="<?=url_to('/')?>" class="nav-btn">
        <span class="icon">🏠</span> Home
      </a>
      <a href="<?=url_to('/leituras')?>" class="nav-btn">
        <span class="icon">📡</span> Leitura RFID
      </a>
      <a href="<?=url_to('/tags')?>" class="nav-btn">
        <span class="icon">🏷️</span> Ver TAGs
      </a>
    </nav>

    <!-- USUARIO LOGADO -->
    <div class="user-profile">
      <div class="status-indicator">
        <span class="status-dot"></span>
        <span><?= !empty($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : (!empty($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin') ?></span>
      </div>
      <a href="<?=url_to('/logout')?>" class="logout-btn">Sair ➔</a>
    </div>
  </header>

  <main class="main-container">