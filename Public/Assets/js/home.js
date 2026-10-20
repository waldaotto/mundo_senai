/**
 * home.js — Gráficos do dashboard "Triagem e rastreamento de carga"
 *
 * Carregado com `defer` depois do Chart.js, então o DOM já está pronto
 * e `Chart` já existe quando este arquivo executa.
 */
(function () {
  'use strict';

  // ---------- Constantes de tema ----------
  const COLORS = {
    blue:   '#2f81f7',
    green:  '#3fb950',
    purple: '#a371f7',
    orange: '#f0883e',
    muted:  '#8b949e',
    grid:   '#21262d',
  };

  const DONUT_PALETTE = [COLORS.blue, COLORS.green, COLORS.purple, COLORS.orange, COLORS.muted];

  // ---------- Utilitários ----------
  /** Lê um JSON de um data-attribute. Retorna `fallback` se ausente ou inválido. */
  function readJsonData(element, attribute, fallback) {
    try {
      const raw = element.dataset[attribute];
      return raw ? JSON.parse(raw) : fallback;
    } catch (error) {
      console.error('[home.js] JSON inválido em data-' + attribute, error);
      return fallback;
    }
  }

  // ---------- Gráfico: distribuição por destino (rosca) ----------
  function initDestinosChart() {
    const canvas = document.getElementById('chartDestinos');
    if (!canvas) return;

    const destinos = readJsonData(canvas, 'destinos', []);
    const labels   = destinos.map(function (item) { return item.destino || 'Outro'; });
    const values   = destinos.map(function (item) { return Number(item.total) || 0; });

    new Chart(canvas, {
      type: 'doughnut',
      data: {
        labels: labels.length ? labels : ['Sem Dados'],
        datasets: [{
          data: values.length ? values : [1],
          backgroundColor: DONUT_PALETTE,
          borderWidth: 0,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
          legend: { position: 'right', labels: { color: COLORS.muted } },
        },
      },
    });
  }

  // ---------- Gráfico: volume de leituras e caixas (linha) ----------
  function initVolumeChart() {
    const canvas = document.getElementById('chartVolume');
    if (!canvas) return;

    // TODO: dados ainda são fixos (mock), iguais aos do código original.
    // Quando houver endpoint/consulta, passar via data-attribute como em chartDestinos.
    new Chart(canvas, {
      type: 'line',
      data: {
        labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        datasets: [
          { label: 'Leituras', data: [12, 19, 15, 25, 22, 10, 5], borderColor: COLORS.blue,  tension: 0.4 },
          { label: 'Caixas',   data: [8, 12, 10, 18, 15, 6, 2],   borderColor: COLORS.green, tension: 0.4 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { grid: { display: false },           ticks: { color: COLORS.muted } },
          y: { grid: { color: COLORS.grid },       ticks: { color: COLORS.muted } },
        },
        plugins: {
          legend: { labels: { color: COLORS.muted } },
        },
      },
    });
  }

  // ---------- Inicialização ----------
  if (typeof Chart === 'undefined') {
    console.error('[home.js] Chart.js não foi carregado (verifique o CDN e a ordem dos scripts).');
    return;
  }

  initDestinosChart();
  initVolumeChart();
})();