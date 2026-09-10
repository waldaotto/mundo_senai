/**
 * leituras.js
 * Polling da API de telemetria (a cada 1000ms) e atualização do
 * Painel de Monitoramento — 2 ESPs (ESP-01 recepção/pesagem,
 * ESP-02 esteira/triagem), destinos RJ ou SC.
 */
(function () {
  'use strict';

  const API_URL = document.body.dataset.apiUrl || 'api/telemetria.php';
  const INTERVALO_MS = 1000;

  // Sequência de status na ordem em que ocorrem
  const ORDEM_SEQUENCIA = [
    'recepcao', 'identificacao_id', 'pesagem',
    'decisao_destino', 'esteira', 'desviada',
  ];

  // Referências DOM
  const el = {
    peso:      document.getElementById('m-peso'),
    id:        document.getElementById('m-id'),
    tempo:     document.getElementById('m-tempo'),
    estado:    document.getElementById('m-estado'),
    destino:   document.getElementById('m-destino'),
    linhaAtiva: document.getElementById('linha-ativa'),
    noBraco:   document.getElementById('no-braco'),
    noBalanca: document.getElementById('no-balanca'),
    noEsteira: document.getElementById('no-esteira'),
    noRJ:      document.getElementById('no-rj'),
    noSC:      document.getElementById('no-sc'),
    esp1:      document.getElementById('esp1-status'),
    esp2:      document.getElementById('esp2-status'),
    sequencia: document.getElementById('lista-sequencia'),
    total:     document.getElementById('c-total'),
    rj:        document.getElementById('c-rj'),
    sc:        document.getElementById('c-sc'),
    log:       document.getElementById('log-eventos'),
    btnAbortar: document.getElementById('btn-abortar'),
  };

  let inicioOperacao = Date.now();
  let ultimoStatus = null;

  function formatarTempo(ms) {
    const totalSeg = Math.floor(ms / 1000);
    const h = String(Math.floor(totalSeg / 3600)).padStart(2, '0');
    const m = String(Math.floor((totalSeg % 3600) / 60)).padStart(2, '0');
    const s = String(totalSeg % 60).padStart(2, '0');
    return `${h}:${m}:${s}`;
  }

  function atualizarCronometro() {
    if (el.tempo) el.tempo.textContent = formatarTempo(Date.now() - inicioOperacao);
  }

  async function buscarTelemetria() {
    try {
      const resp = await fetch(API_URL + '?t=' + Date.now(), {
        method: 'GET',
        headers: { Accept: 'application/json' },
        cache: 'no-store',
      });
      if (!resp.ok) throw new Error('HTTP ' + resp.status);

      const json = await resp.json();
      if (!json.sucesso) throw new Error(json.erro || 'Resposta inválida da API');

      atualizarPainel(json.dados);
    } catch (erro) {
      console.error('[Telemetria] Falha no polling:', erro.message);
      if (el.estado) {
        el.estado.textContent = 'OFFLINE';
        el.estado.style.color = 'var(--vermelho-erro)';
      }
    }
  }

  function atualizarPainel(dados) {
    // ---- Métricas superiores ----
    const peso = parseFloat(dados.peso_g) || 0;
    if (el.peso) el.peso.textContent = peso.toFixed(1) + ' g';

    if (el.id) el.id.textContent = dados.id_caixa || '—';

    if (el.estado) {
      const ativa = !!dados.estacao_ativa;
      el.estado.textContent = ativa ? 'ATIVO' : 'PARADO';
      el.estado.style.color = ativa ? 'var(--verde-ok)' : 'var(--vermelho-erro)';
    }

    const destino = (dados.destino || 'AGUARDANDO').toUpperCase();
    if (el.destino) {
      el.destino.textContent = destino === 'AGUARDANDO' ? 'AGUARD.' : destino;
      el.destino.style.color = destino === 'RJ' ? 'var(--amarelo-senai)'
                              : destino === 'SC' ? 'var(--azul-claro)'
                              : 'var(--texto-fraco)';
    }

    // ---- Diagrama da esteira ----
    atualizarDiagrama(dados.status, destino);

    // ---- Estado dos ESPs ----
    atualizarEstadoEsp(dados.esp_origem, dados.status);

    // ---- Sequência ----
    atualizarSequencia(dados.status);

    // ---- Contadores ----
    if (el.total) el.total.textContent = dados.contadores?.total ?? 0;
    if (el.rj)    el.rj.textContent    = dados.contadores?.rj    ?? 0;
    if (el.sc)    el.sc.textContent    = dados.contadores?.sc    ?? 0;

    // ---- Log de eventos ----
    if (el.log && Array.isArray(dados.historico) && dados.historico.length > 0) {
      el.log.innerHTML = dados.historico.slice(0, 12).map(function (ev) {
        const hora = new Date(ev.timestamp).toLocaleTimeString('pt-BR');
        return `<div class="log-linha">
          <span class="log-hora">${hora}</span>
          <span class="log-esp">${ev.esp_origem || '—'}</span>
          <span class="log-status">${ev.status || '—'}</span>
          <span class="log-msg">Caixa ${ev.id_caixa || '—'} · ${ev.peso_g ?? 0}g · destino ${ev.destino || '—'}</span>
        </div>`;
      }).join('');
    }

    ultimoStatus = dados.status;
  }

  function limparAtivos() {
    [el.noBraco, el.noBalanca, el.noEsteira, el.noRJ, el.noSC].forEach(function (no) {
      if (no) no.classList.remove('ativo', 'ativo-rj', 'ativo-sc');
    });
  }

  function atualizarDiagrama(status, destino) {
    limparAtivos();

    // Progresso da linha (0 a 100%) conforme etapa do processo
    const progresso = {
      aguardando: 0,
      recepcao: 10,
      identificacao_id: 25,
      pesagem: 45,
      decisao_destino: 60,
      esteira: 80,
      desviada: 100,
      entregue: 100,
      erro: 0,
    };
    const pct = progresso[status] ?? 0;
    if (el.linhaAtiva) el.linhaAtiva.style.width = pct + '%';

    if (status === 'recepcao' || status === 'identificacao_id') {
      el.noBraco?.classList.add('ativo');
    } else if (status === 'pesagem') {
      el.noBalanca?.classList.add('ativo');
    } else if (status === 'decisao_destino' || status === 'esteira') {
      el.noEsteira?.classList.add('ativo');
    } else if (status === 'desviada' || status === 'entregue') {
      if (destino === 'RJ') el.noRJ?.classList.add('ativo', 'ativo-rj');
      if (destino === 'SC') el.noSC?.classList.add('ativo', 'ativo-sc');
    }
  }

  function atualizarEstadoEsp(espOrigem, status) {
    const emErro = status === 'erro';

    if (el.esp1) {
      const ativoEsp1 = espOrigem === 'ESP-01' && !emErro;
      el.esp1.textContent = emErro && espOrigem === 'ESP-01' ? 'ERRO' : (ativoEsp1 ? 'ATIVO' : 'REPOUSO');
      el.esp1.className = 'esp-item__status ' +
        (emErro && espOrigem === 'ESP-01' ? 'esp-item__status--erro'
          : ativoEsp1 ? 'esp-item__status--ativo' : 'esp-item__status--ok');
    }

    if (el.esp2) {
      const ativoEsp2 = espOrigem === 'ESP-02' && !emErro;
      el.esp2.textContent = emErro && espOrigem === 'ESP-02' ? 'ERRO' : (ativoEsp2 ? 'ATIVO' : 'REPOUSO');
      el.esp2.className = 'esp-item__status ' +
        (emErro && espOrigem === 'ESP-02' ? 'esp-item__status--erro'
          : ativoEsp2 ? 'esp-item__status--ativo' : 'esp-item__status--ok');
    }
  }

  function atualizarSequencia(status) {
    if (!el.sequencia) return;
    const indiceAtual = ORDEM_SEQUENCIA.indexOf(status);

    el.sequencia.querySelectorAll('.sequencia-item').forEach(function (item) {
      const s = item.dataset.status;
      const indiceItem = ORDEM_SEQUENCIA.indexOf(s);
      item.classList.remove('concluido', 'atual');

      if (indiceAtual === -1) return; // aguardando ou erro: nada marcado
      if (indiceItem < indiceAtual) item.classList.add('concluido');
      if (indiceItem === indiceAtual) item.classList.add('atual');
    });
  }

  // ---- Botão abortar: pede confirmação e reseta o estado via API ----
  if (el.btnAbortar) {
    el.btnAbortar.addEventListener('click', async function () {
      const confirmar = window.confirm('Abortar o ciclo atual da esteira?');
      if (!confirmar) return;

      try {
        await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            esp_origem: 'ESP-02',
            status: 'erro',
            destino: 'AGUARDANDO',
          }),
        });
        buscarTelemetria();
      } catch (erro) {
        console.error('[Telemetria] Falha ao abortar:', erro.message);
      }
    });
  }

  // Inicialização
  buscarTelemetria();
  setInterval(buscarTelemetria, INTERVALO_MS);
  setInterval(atualizarCronometro, 1000);
})();
