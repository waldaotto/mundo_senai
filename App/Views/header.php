<?php
use App\Routes\UrlHelper;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mundo SENAI — Vitrine de Projetos</title>
  <link rel="stylesheet" href="<?= UrlHelper::asset('css/base.css') ?>">
  <link rel="stylesheet" href="<?= UrlHelper::asset('css/home.css') ?>">
</head>
<body>

<header class="topbar">
  <div class="topbar__logo">senai<span class="ponto">.</span>mundo</div>

  <div class="topbar__local">
    <span>📍</span>
    <span>Enviar para<br><strong>Brasil 00000-000</strong></span>
  </div>

  <form class="topbar__busca" action="<?= UrlHelper::url('/') ?>" method="get">
    <input type="text" name="busca" placeholder="buscar projetos, protótipos, alunos...">
    <button type="submit" aria-label="Buscar">🔍</button>
  </form>

  <div class="topbar__acoes">
    <a class="topbar__rastreio" href="<?= UrlHelper::url('/rastreamento') ?>">
      📦 Acompanhe sua encomenda
    </a>
    <a class="topbar__carrinho" href="#" aria-label="Carrinho">🛒</a>
  </div>
</header>

<nav class="nav-categorias">
  <a href="#" class="ativo">Todos</a>
  <?php foreach (($categorias ?? []) as $categoria): ?>
    <a href="#"><?= htmlspecialchars($categoria) ?></a>
  <?php endforeach; ?>
</nav>
