const REGIOES = {
  1: 'Norte',
  2: 'Nordeste',
  3: 'Centro-Oeste',
  4: 'Sudeste',
  5: 'Sul'
};

var caixaSelecionada = null;

function formatarData(valor) {
  if (!valor) return '--';
  const m = String(valor).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}:\d{2}))?/);
  if (!m) return valor;
  return `${m[3]}/${m[2]}/${m[1]}` + (m[4] ? ` ${m[4]}` : '');
}

function abrirModalCadastro() {
  document.getElementById('modalCadastroCaixa').style.display = 'flex';
}

function fecharModal(modalId) {
  const el = document.getElementById(modalId);
  if (el) el.style.display = 'none';
}

function fecharModalOverlay(event, modalId) {
  if (event.target.id === modalId) {
    fecharModal(modalId);
  }
}

// Captura cliques nos cards via Base64 sem falhas de escape no HTML
document.addEventListener('click', function (event) {
  const card = event.target.closest('.card-item-caixa');
  if (!card) return;

  const rawBase64 = card.getAttribute('data-caixa');
  if (!rawBase64) return;

  try {
    const dados = JSON.parse(atob(rawBase64));
    caixaSelecionada = dados;

    const destino = dados.destino || REGIOES[dados.regiao_id] || 'N/A';
    const processo = dados.processo ? String(dados.processo).toUpperCase() : 'INDEFINIDO';
    const tag = dados.tag_uid || 'N/A';
    const peso = (dados.peso !== undefined && dados.peso !== null) ? dados.peso + ' kg' : '0.00 kg';

    document.getElementById('m_id').innerText = '📦 Caixa #' + (dados.id ?? '--');
    document.getElementById('m_destino').innerText = destino;
    document.getElementById('m_processo').innerText = processo;
    document.getElementById('m_tag').innerText = tag;
    document.getElementById('m_peso').innerText = peso;
    document.getElementById('m_data').innerText = formatarData(dados.data_criada || dados.created_at);

    document.getElementById('modalCaixa').style.display = 'flex';
  } catch (err) {
    console.error("Erro ao decodificar os dados da caixa:", err);
  }
});

// Ação do Botão EDITAR
function executarEdicaoDirecta() {
  if (!caixaSelecionada) {
    alert("Nenhuma caixa foi selecionada.");
    return;
  }

  fecharModal('modalCaixa');

  document.getElementById('e_caixa_id').value = caixaSelecionada.id;
  document.getElementById('e_modal_title').innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Editar Caixa #${caixaSelecionada.id}`;

  if (caixaSelecionada.regiao_id) {
    document.getElementById('e_destino_id').value = caixaSelecionada.regiao_id;
  }
  if (caixaSelecionada.processo) {
    document.getElementById('e_processo_id').value = String(caixaSelecionada.processo).toLowerCase();
  }

  document.getElementById('modalEditarCaixa').style.display = 'flex';
}

// Ação do Botão EXCLUIR via formulário POST
function executarExclusaoDirecta() {
  if (!caixaSelecionada || !caixaSelecionada.id) {
    alert("Selecione uma caixa válida para excluir.");
    return;
  }

  if (confirm(`Tem certeza que deseja excluir a Caixa #${caixaSelecionada.id}?`)) {
    document.getElementById('deletar_caixa_id').value = caixaSelecionada.id;
    document.getElementById('formDeletarCaixa').submit();
  }
}