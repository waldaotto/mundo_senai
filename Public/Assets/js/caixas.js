const REGIOES = {
  1: 'Norte',
  2: 'Nordeste',
  3: 'Centro-Oeste',
  4: 'Sudeste',
  5: 'Sul'
};

function formatarData(valor) {
  if (!valor) return '--';
  // aceita "YYYY-MM-DD" ou "YYYY-MM-DD HH:MM:SS" sem sofrer com fuso horário
  const m = String(valor).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}:\d{2}))?/);
  if (!m) return valor;
  return `${m[3]}/${m[2]}/${m[1]}` + (m[4] ? ` ${m[4]}` : '');
}

function abrirModalCadastro() {
  document.getElementById('modalCadastroCaixa').style.display = 'flex';
}

function abrirModalDetalhes(dados) {
  const destino = dados.destino || REGIOES[dados.regiao_id] || 'N/A';
  const processo = dados.processo ? String(dados.processo).toUpperCase() : 'INDEFINIDO';
  const tag = dados.tag_uid || 'N/A';
  const peso = (dados.peso !== undefined && dados.peso !== null) ? dados.peso + ' kg' : 'N/A';

  document.getElementById('m_id').innerText = '📦 Caixa #' + (dados.id ?? '--');
  document.getElementById('m_destino').innerText = destino;
  document.getElementById('m_processo').innerText = processo;
  document.getElementById('m_tag').innerText = tag;
  document.getElementById('m_peso').innerText = peso;
  document.getElementById('m_data').innerText = formatarData(dados.data_criada || dados.created_at);

  document.getElementById('modalCaixa').style.display = 'flex';
}

function fecharModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

function fecharModalOverlay(event, modalId) {
  if (event.target.id === modalId) {
    fecharModal(modalId);
  }
}