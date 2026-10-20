
  function abrirModalCadastro() {
    document.getElementById('modalCadastroCaixa').style.display = 'flex';
  }

  function abrirModalDetalhes(dados) {
    document.getElementById('m_id').innerText = '📦 Caixa #' + (dados.id || '--');
    document.getElementById('m_destino').innerText = dados.destino || 'N/A';
    document.getElementById('m_processo').innerText = dados.processo_atual || dados.processo || 'Preparação';
    document.getElementById('m_tag').innerText = dados.tag_uid || dados.tag || 'N/A';
    document.getElementById('m_peso').innerText = (dados.peso ? dados.peso + ' kg' : 'N/A');
    document.getElementById('m_data').innerText = dados.data_hora || dados.created_at || '--';

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