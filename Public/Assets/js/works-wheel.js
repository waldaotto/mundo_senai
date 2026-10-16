document.addEventListener("DOMContentLoaded", () => {
  const stage = document.getElementById("worksWheelStage");
  const container = document.getElementById("worksWheelContainer");
  const labelEl = document.getElementById("worksWheelLabel");
  const titleEl = document.getElementById("worksWheelTitle");
  const cards = Array.from(document.querySelectorAll(".works-wheel-card"));

  if (!stage || !container) return;

  const count = cards.length;
  const STEP = 40;
  const DRUM = 2.22;
  const LENS = 2.7;
  const RING_R = 1.14;
  const BOW = 1.82;
  const WHEEL_UNITS = 900;
  const DRAG_UNITS = 420;
  const EASE = 0.12;

  let turn = 0;
  let target = 0;
  let active = 0;
  let dragY = null;

  function clamp(v, lo, hi) {
    return Math.min(hi, Math.max(lo, v));
  }
  function rad(deg) {
    return (deg * Math.PI) / 180;
  }
  function bowAt(drumDeg, bow) {
    return -bow * (1 - Math.cos(rad(drumDeg)));
  }

  function getMetrics() {
    const w = stage.clientWidth || window.innerWidth;
    const h = stage.clientHeight || window.innerHeight;

    // Tamanho fixo relativo garantindo tamanho mínimo visível
    const cardH = Math.max(h * 0.45, 260); 
    const cardW = cardH / 1.45;

    return {
      cardW,
      cardH,
      drumR: cardH * DRUM,
      ringR: cardH * RING_R,
      bow: cardH * BOW,
      depth: cardH * LENS,
      fontSize: Math.max(cardH * 0.18, 24),
    };
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

      const transform =
        `translateX(${progress * bowAt(drumDeg, m.bow)}px) ` +
        `rotateZ(${(1 - progress) * ringDeg}deg) translateY(${-(1 - progress) * m.ringR}px) ` +
        `rotateX(${progress * drumDeg}deg) translateZ(${progress * m.drumR}px)`;

      card.style.transform = transform;
      card.style.opacity = progress > 0.5 && Math.abs(d) > 1.8 ? "0" : "1";
      card.style.zIndex = Math.round(100 - Math.abs(d) * 2);
    });

    if (labelEl) labelEl.style.opacity = (1 - progress).toString();
    if (titleEl) titleEl.style.opacity = progress.toString();

    const near = clamp(Math.round(pos), 0, count - 1);
    if (active !== near) {
      active = near;
      if (titleEl) titleEl.innerText = cards[active]?.dataset.title || "";
      document.querySelectorAll(".works-wheel-index button").forEach((btn, idx) => {
        btn.classList.toggle("active", idx === active);
      });
    }

    requestAnimationFrame(loop);
  }

  // Eventos de rolagem e clique
  stage.addEventListener(
    "wheel",
    (e) => {
      e.preventDefault();
      target = clamp(target + e.deltaY / WHEEL_UNITS, 0, count);
    },
    { passive: false }
  );

  stage.addEventListener("pointerdown", (e) => {
    dragY = e.clientY;
  });

  window.addEventListener("pointermove", (e) => {
    if (dragY === null) return;
    target = clamp(target + (dragY - e.clientY) / DRAG_UNITS, 0, count);
    dragY = e.clientY;
  });

  window.addEventListener("pointerup", () => {
    dragY = null;
    if (target > 0.5) target = Math.round(target);
  });

  // Cliques nos itens do menu lateral
  document.querySelectorAll(".works-wheel-index button").forEach((btn, idx) => {
    btn.addEventListener("click", () => {
      target = idx + 1;
    });
  });

  window.addEventListener("resize", update);
  update();
  loop();
});