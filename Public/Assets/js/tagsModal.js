document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalTag');
  const uid = document.getElementById('uid');
  const abrir = document.getElementById('abrirModal');
  const fechar = document.getElementById('fecharModal');

  if (!modal || !abrir || !fechar) return;

  const abrirModal = () => {
    modal.hidden = false;
    uid.focus();
  };

  const fecharModal = () => {
    modal.hidden = true;
    uid.value = '';
  };

  abrir.addEventListener('click', abrirModal);
  fechar.addEventListener('click', fecharModal);

  // Fecha ao clicar fora da caixa
  modal.addEventListener('click', (e) => {
    if (e.target === modal) fecharModal();
  });

  // Fecha com ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) fecharModal();
  });
});