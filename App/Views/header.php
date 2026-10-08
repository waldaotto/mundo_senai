<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TRIAGEM 4.0</title>
  <style>
    /* Reset Global Dark */
    html, body {
      margin: 0 !important;
      padding: 0 !important;
      width: 100% !important;
      min-height: 100vh !important;
      background-color: #0d1117 !important;
      color: #c9d1d9 !important;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    body > ul, body > button, body > a {
      display: none !important;
    }

    .app-viewport {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      width: 100%;
    }

    /* CARROSSEL 3D HEADER */
    .works-wheel-section {
      position: relative;
      width: 100%;
      height: 52vh;
      min-height: 360px;
      background-color: #0d1117;
      color: #ffffff;
      overflow: hidden;
      user-select: none;
      border-bottom: 1px solid #30363d;
    }

    .works-wheel-stage {
      position: absolute;
      inset: 0;
      cursor: grab;
      outline: none;
    }

    .works-wheel-stage:active {
      cursor: grabbing;
    }

    .works-wheel-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform-style: preserve-3d;
    }

    .works-wheel-card {
      position: absolute;
      backface-visibility: hidden;
      text-decoration: none;
      cursor: pointer;
    }

    .works-wheel-card-inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
      background: linear-gradient(145deg, #161b22, #21262d);
      border: 2px solid #30363d;
      transition: border-color 0.3s;
    }

    .works-wheel-card:hover .works-wheel-card-inner {
      border-color: #58a6ff;
    }

    .card-icon {
      font-size: 3rem;
      margin-bottom: 10px;
    }

    .card-label-text {
      color: #ffffff;
      font-weight: 700;
      font-size: 1.15rem;
      text-align: center;
    }

    .works-wheel-label {
      position: absolute;
      inset: 0;
      display: grid;
      place-items: center;
      font-weight: 800;
      pointer-events: none;
      opacity: 1;
      color: #f0f6fc;
    }

    .works-wheel-title {
      position: absolute;
      top: 50%;
      left: 8%;
      transform: translateY(-50%);
      font-weight: 700;
      pointer-events: none;
      opacity: 0;
      color: #ffffff;
    }

    .works-wheel-index {
      position: absolute;
      top: 5%;
      right: 3%;
      text-align: right;
      list-style: none;
      z-index: 10;
    }

    .works-wheel-index a {
      background: none;
      border: none;
      color: #8b949e;
      font-size: 0.95rem;
      text-decoration: none;
      display: block;
      margin-left: auto;
      padding: 6px 0;
      transition: color 0.2s;
    }

    .works-wheel-index a.active {
      color: #58a6ff;
      font-weight: bold;
    }

    .dashboard-container {
      flex: 1;
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 20px;
      box-sizing: border-box;
    }

    .tab-title {
      color: #f0f6fc;
      font-size: 1.8rem;
      margin-bottom: 5px;
    }

    .tab-subtitle {
      color: #8b949e;
      margin-bottom: 25px;
    }
  </style>
</head>
<body>

<div class="app-viewport">
  <section class="works-wheel-section">
    <div class="works-wheel-stage" id="worksWheelStage">
      <div class="works-wheel-container" id="worksWheelContainer">
        
        <a href="home" class="works-wheel-card" data-title="Painel Geral (Home)">
          <div class="works-wheel-card-inner">
            <div class="card-icon">🏠</div>
            <div class="card-label-text">Home</div>
          </div>
        </a>

        <a href="leituras" class="works-wheel-card" data-title="Leitura RFID em Tempo Real">
          <div class="works-wheel-card-inner">
            <div class="card-icon">📡</div>
            <div class="card-label-text">Leitura RFID</div>
          </div>
        </a>

        <a href="tags" class="works-wheel-card" data-title="Gerenciamento de TAGs">
          <div class="works-wheel-card-inner">
            <div class="card-icon">🏷️</div>
            <div class="card-label-text">Ver TAGs</div>
          </div>
        </a>

      </div>
    </div>

    <div class="works-wheel-label" id="worksWheelLabel">TRIAGEM 4.0</div>
    <div class="works-wheel-title" id="worksWheelTitle"></div>

    <ol class="works-wheel-index" id="worksWheelIndex">
      <li><a href="/mundo_senai" id="nav-home">1. Home</a></li>
      <li><a href="leituras" id="nav-leituras">2. Leitura RFID</a></li>
      <li><a href="tags" id="nav-tags">3. Ver TAGs</a></li>
    </ol>
  </section>

  <div class="dashboard-container">

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const stage = document.getElementById("worksWheelStage");
    const container = document.getElementById("worksWheelContainer");
    const labelEl = document.getElementById("worksWheelLabel");
    const titleEl = document.getElementById("worksWheelTitle");
    const cards = Array.from(document.querySelectorAll(".works-wheel-card"));
    const navLinks = document.querySelectorAll(".works-wheel-index a");

    if (!stage || !container) return;

    const count = cards.length;
    const STEP = 40, DRUM = 2.22, LENS = 2.7, RING_R = 1.14, BOW = 1.82;
    const DRAG_UNITS = 300, EASE = 0.12;

    const currentUrl = window.location.href;
    let initialTarget = 1;

    if (currentUrl.includes("leituras")) {
      initialTarget = 2;
    } else if (currentUrl.includes("tags")) {
      initialTarget = 3;
    } else {
      initialTarget = 1;
    }

    navLinks.forEach((link, idx) => {
      if (idx + 1 === initialTarget) {
        link.classList.add("active");
      }
    });

    let turn = initialTarget, target = initialTarget, active = initialTarget - 1, dragX = null;

    function clamp(v, lo, hi) { return Math.min(hi, Math.max(lo, v)); }
    function rad(deg) { return (deg * Math.PI) / 180; }
    function bowAt(drumDeg, bow) { return -bow * (1 - Math.cos(rad(drumDeg))); }

    function getMetrics() {
      const h = stage.clientHeight || window.innerHeight;
      const cardH = Math.max(h * 0.45, 220);
      return { cardW: cardH / 1.35, cardH, drumR: cardH * DRUM, ringR: cardH * RING_R, bow: cardH * BOW, depth: cardH * LENS, fontSize: Math.max(cardH * 0.16, 20) };
    }

    function update() {
      const m = getMetrics();
      stage.style.perspective = `${m.depth}px`;
      cards.forEach((card) => {
        card.style.width = `${m.cardW}px`;
        card.style.height = `${m.cardH}px`;
        card.style.marginLeft = `${-m.cardW / 2}px`;
        card.style.marginTop = `${-m.cardH / 2}px`;
      });
      if (labelEl) labelEl.style.fontSize = `${m.fontSize}px`;
      if (titleEl) titleEl.style.fontSize = `${m.fontSize}px`;
    }

    function loop() {
      const m = getMetrics();
      const gap = target - turn;
      if (Math.abs(gap) < 0.0005) turn = target;
      else turn += gap * EASE;

      const t = turn;
      const progress = clamp(t, 0, 1);
      const pos = Math.max(0, t - 1);

      container.style.transform = `translateZ(${-progress * m.drumR}px)`;

      cards.forEach((card, i) => {
        const d = i - pos;
        const drumDeg = d * STEP;
        const ringDeg = d * (360 / count);
        card.style.transform = `translateX(${progress * bowAt(drumDeg, m.bow)}px) rotateZ(${(1 - progress) * ringDeg}deg) translateY(${-(1 - progress) * m.ringR}px) rotateX(${progress * drumDeg}deg) translateZ(${progress * m.drumR}px)`;
        card.style.opacity = progress > 0.5 && Math.abs(d) > 1.8 ? "0" : "1";
        card.style.zIndex = Math.round(100 - Math.abs(d) * 2);
      });

      if (labelEl) labelEl.style.opacity = (1 - progress).toString();
      if (titleEl) titleEl.style.opacity = progress.toString();

      const near = clamp(Math.round(pos), 0, count - 1);
      if (active !== near) {
        active = near;
        if (titleEl) titleEl.innerText = cards[active]?.dataset.title || "";
      }

      requestAnimationFrame(loop);
    }

    stage.addEventListener("pointerdown", (e) => { dragX = e.clientX; });
    window.addEventListener("pointermove", (e) => {
      if (dragX === null) return;
      target = clamp(target + (dragX - e.clientX) / DRAG_UNITS, 0, count);
      dragX = e.clientX;
    });
    window.addEventListener("pointerup", () => {
      dragX = null;
      if (target > 0.5) target = Math.round(target);
    });

    window.addEventListener("resize", update);
    update();
    loop();
  });
</script>