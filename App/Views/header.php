<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?=CSS_PATH.'header.css'?>">
  <title>SENAI - <?=$title ?? 'Triagem'?></title>
  <link rel="icon" type="image/png" href="<?= IMG_PATH ?>logo_app.png">

</head>
<body>

  <header class="app-header">
    <!-- 1. ESQUERDA: LOGO SENAI -->
    <div class="brand-container">
      <svg class="brand-logo-svg" viewBox="0 0 380 90" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M50 72C42 72 37 67 39 57C41 45 54 39 69 35C84 31 89 28 90 22C91 16 86 13 77 13C64 13 53 18 47 23L39 7C48 2 64 0 80 0C102 0 114 9 111 27C108 43 95 48 79 52C66 55 62 58 61 63C60 67 65 69 73 69C85 69 98 63 105 57L112 73C103 80 87 85 68 85C60 85 53 82 50 72Z" fill="#2B429D"/>
        <path d="M115 3H177V20H138V35H171V51H138V67H180V84H115V3Z" fill="#2B429D"/>
        <path d="M187 3H209L239 55V3H260V84H238L208 31V84H187V3Z" fill="#2B429D"/>
        <path d="M298 3H323L350 84H325L319 65H285L279 84H257L298 3ZM314 47L304 18L292 47H314Z" fill="#2B429D"/>
        <path d="M353 3H377V84H353V3Z" fill="#2B429D"/>
        <path d="M353 3H377L385 24H361L353 3Z" fill="#E35205"/>
      </svg>
    </div>

    <!-- 2. CENTRO: MENUS DE NAVEGAÇÃO -->
    <nav class="header-nav">
      <a href="<?=url_to('/')?>" class="nav-btn">
        <i class="fa-solid fa-chart-line nav-icon"></i> Painel
      </a>
      <a href="<?=url_to('/caixas')?>" class="nav-btn">
        <i class="fa-solid fa-box nav-icon"></i> Caixas
      </a>
      <a href="<?=url_to('/leituras')?>" class="nav-btn">
        <i class="fa-solid fa-wifi nav-icon"></i> Leituras RFID
      </a>
      <a href="<?=url_to('/tags')?>" class="nav-btn">
        <i class="fa-solid fa-tag nav-icon"></i> TAGs
      </a>
      <!-- NOVO ITEM: USUÁRIOS -->
      <?php if ($_SESSION['role'] >=10):?>
      <a href="<?=url_to('/users')?>" class="nav-btn">
        <i class="fa-solid fa-users nav-icon"></i> Usuários
      </a>
      <?php endif; ?>
    </nav>

    <!-- 3. DIREITA: USUÁRIO LOGADO + SAIR -->
    <div class="user-profile">
      <div class="user-info">
        <i class="fa-solid fa-user user-icon"></i>
        <span><?= !empty($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : (!empty($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Kauã Garcia') ?></span>
      </div>
      <a href="<?=url_to('/logout?id=' . ($_SESSION['user_id'] ?? ''))?>" class="logout-btn">Sair</a>
    </div>
  </header>

  <main class="main-container">