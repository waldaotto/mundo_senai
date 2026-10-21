<!-- Importações Bootstrap 5 & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    /* Estilização Base Dark */
    html, body, .main-container, main {
        background-color: #0d1117 !important;
        color: #c9d1d9 !important;
    }

    /* Container Master-Detail Layout */
    .user-layout-container {
        padding-top: 1.5rem;
        padding-bottom: 2rem;
    }

    .card-dark-panel {
        background-color: #161b22 !important;
        border: 1px solid #30363d !important;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Inputs e Busca */
    .form-control-dark {
        background-color: #0d1117 !important;
        border: 1px solid #30363d !important;
        color: #f0f6fc !important;
    }
    .form-control-dark:focus {
        border-color: #58a6ff !important;
        box-shadow: 0 0 0 2px rgba(88, 166, 255, 0.3) !important;
    }

    .btn-senai-primary {
        background-color: #238636 !important;
        border-color: #2ea043 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
    .btn-senai-primary:hover {
        background-color: #2ea043 !important;
    }

    /* Lista de Usuários na Esquerda */
    .user-list-group {
        max-height: 520px;
        overflow-y: auto;
    }
    
    .user-list-item {
        background-color: #161b22;
        border-bottom: 1px solid #21262d;
        color: #c9d1d9;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 12px 16px;
    }

    .user-list-item:hover {
        background-color: #21262d;
    }

    .user-list-item.active {
        background-color: #1f293d !important;
        border-left: 4px solid #58a6ff !important;
    }

    /* Detalhes do Usuário na Direita */
    .user-avatar-lg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #21262d;
        border: 3px solid #30363d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        color: #8b949e;
    }

    .info-label {
        color: #8b949e;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .info-value {
        color: #f0f6fc;
        font-weight: 500;
        font-size: 1rem;
    }

    /* Modais Dark */
    .modal-content-dark {
        background-color: #161b22 !important;
        border: 1px solid #30363d !important;
        color: #f0f6fc !important;
    }
    .modal-content-dark .modal-header,
    .modal-content-dark .modal-footer {
        border-color: #30363d !important;
    }
</style>

<div class="container-fluid px-4 user-layout-container">
    
    <!-- Header Superior -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold text-light mb-0"><i class="fa-solid fa-users me-2"></i>Gestão de Usuários</h3>
            <span class="text-secondary small">Selecione um usuário da lista para visualizar todos os seus detalhes</span>
        </div>
        <button class="btn btn-senai-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="limparFormulario()">
            <i class="fa-solid fa-user-plus me-1"></i> Novo Usuário
        </button>
    </div>

    <!-- Layout Dividido em Colunas (Mestre x Detalhe) -->
    <div class="row g-3">
        
        <!-- ESQUERDA: Busca e Lista de Usuários -->
        <div class="col-lg-5 col-md-6">
            <div class="card card-dark-panel">
                <div class="card-body p-3">
                    
                    <!-- Campo de Busca Estilo Esboço -->
                    <div class="input-group mb-3">
                        <input type="text" id="campoPesquisa" class="form-control form-control-dark" placeholder="Buscar por Nome, E-mail, Matrícula..." onkeyup="filtrarUsuarios()">
                        <button class="btn btn-outline-secondary" type="button" onclick="filtrarUsuarios()">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>

                    <!-- Lista de Usuários -->
                    <div class="user-list-group rounded overflow-hidden" id="listaUsuarios">
                        <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                            <?php foreach ($usuarios as $index =>$u): ?>
                                <div class="user-list-item d-flex align-items-center justify-content-between item-usuario <?= $index === 0 ? 'active' : '' ?>" 
                                     id="item-user-<?= $u['id'] ?? $index ?>"
                                     onclick='selecionarUsuario(<?= json_encode($u) ?>, this)'>
                                    
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 40px; height: 40px; border: 1px solid #30363d;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-light nome-usuario"><?= htmlspecialchars($u['nome'] ?? 'Sem Nome') ?></div>
                                            <small class="text-secondary d-block email-usuario"><?= htmlspecialchars($u['email'] ?? '') ?></small>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <span class="badge bg-<?= ($u['status'] ?? '') === 'Ativo' ? 'success' : 'secondary' ?> mb-1">
                                            <?= htmlspecialchars($u['status'] ?? 'Ativo') ?>
                                        </span>
                                        <div class="small text-muted"><?= htmlspecialchars($u['cargo'] ?? 'Usuário') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted" id="msgSemUsuarios">
                                <i class="fa-solid fa-folder-open fs-2 mb-2 d-block opacity-50"></i>
                                Nenhum usuário cadastrado.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- DIREITA: Detalhes do Usuário Selecionado -->
        <div class="col-lg-7 col-md-6">
            <div class="card card-dark-panel h-100">
                <div class="card-body p-4 d-flex flex-column" id="painelDetalhes">
                    
                    <!-- Exibição Inicial ou Padrão -->
                    <div class="d-flex align-items-center gap-4 border-bottom border-secondary border-opacity-25 pb-4 mb-4">
                        <!-- Foto do Usuário -->
                        <div class="user-avatar-lg shadow-sm" id="detalheAvatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="fw-bold text-light mb-1" id="detalheNome">Selecione um usuário</h3>
                                    <span class="badge bg-primary fs-7" id="detalheCargo">Perfil</span>
                                    <span class="badge bg-success ms-1" id="detalheStatus">Ativo</span>
                                </div>
                                <div class="d-flex gap-2" id="acoesUsuario">
                                    <button class="btn btn-sm btn-outline-warning" id="btnEditar" onclick="editarUsuarioSelecionado()">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" id="btnExcluir" onclick="excluirUsuarioSelecionado()">
                                        <i class="fa-solid fa-trash me-1"></i> Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Informação -->
                    <div class="row g-4 flex-grow-1">
                        <div class="col-md-6">
                            <div class="info-label"><i class="fa-solid fa-hashtag me-1"></i> ID do Sistema</div>
                            <div class="info-value" id="detalheId">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label"><i class="fa-solid fa-envelope me-1"></i> E-mail Corporativo</div>
                            <div class="info-value" id="detalheEmail">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label"><i class="fa-solid fa-id-card me-1"></i> Matrícula / CPF</div>
                            <div class="info-value" id="detalheDocumento">-</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label"><i class="fa-solid fa-briefcase me-1"></i> Cargo / Função</div>
                            <div class="info-value" id="detalheCargoTexto">-</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Formulário CRUD -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-dark">
            <form id="formUsuario" action="<?=url_to('/user/salvar')?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fs-6" id="tituloModalUsuario">Cadastrar Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="usuarioId">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label fw-semibold">Nome Completo</label>
                        <input type="text" class="form-control form-control-dark" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">E-mail</label>
                        <input type="email" class="form-control form-control-dark" id="email" name="email" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="documento" class="form-label fw-semibold">CPF / Matrícula</label>
                            <input type="text" class="form-control form-control-dark" id="documento" name="documento">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cargo" class="form-label fw-semibold">Cargo / Função</label>
                            <input type="text" class="form-control form-control-dark" id="cargo" name="cargo">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select form-control-dark" id="status" name="status">
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-senai-primary btn-sm">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

</main>
</body>
</html>

<!-- Lógica do Painel Interativo Mestre-Detalhe -->
<script>
    let usuarioAtual = null;

    // Função executada ao clicar em um usuário da lista
    function selecionarUsuario(usuario, elemento) {
        usuarioAtual = usuario;

        // Atualiza a seleção visual na lista
        document.querySelectorAll('.user-list-item').forEach(item => item.classList.remove('active'));
        if(elemento) elemento.classList.add('active');

        // Preenche os dados no painel da direita
        document.getElementById('detalheId').innerText = usuario.id || 'N/A';
        document.getElementById('detalheNome').innerText = usuario.nome || 'Sem Nome';
        document.getElementById('detalheEmail').innerText = usuario.email || 'N/A';
        document.getElementById('detalheDocumento').innerText = usuario.documento || 'N/A';
        document.getElementById('detalheCargo').innerText = usuario.cargo || 'Geral';
        document.getElementById('detalheCargoTexto').innerText = usuario.cargo || 'N/A';
        
        const statusBadge = document.getElementById('detalheStatus');
        statusBadge.innerText = usuario.status || 'Ativo';
        statusBadge.className = 'badge ms-1 bg-' + (usuario.status === 'Ativo' ? 'success' : 'secondary');

        // Mostra os botões de ação
        document.getElementById('acoesUsuario').style.display = 'flex';
    }

    // Filtrar lista em tempo real
    function filtrarUsuarios() {
        const termo = document.getElementById('campoPesquisa').value.toLowerCase();
        const itens = document.querySelectorAll('.item-usuario');

        itens.forEach(item => {
            const texto = item.innerText.toLowerCase();
            item.style.display = texto.includes(termo) ? 'flex' : 'none';
        });
    }

    function limparFormulario() {
        document.getElementById('formUsuario').reset();
        document.getElementById('usuarioId').value = '';
        document.getElementById('tituloModalUsuario').innerText = 'Cadastrar Usuário';
    }

    function editarUsuarioSelecionado() {
        if (!usuarioAtual) return;
        document.getElementById('usuarioId').value = usuarioAtual.id || '';
        document.getElementById('nome').value = usuarioAtual.nome || '';
        document.getElementById('email').value = usuarioAtual.email || '';
        document.getElementById('documento').value = usuarioAtual.documento || '';
        document.getElementById('cargo').value = usuarioAtual.cargo || '';
        document.getElementById('status').value = usuarioAtual.status || 'Ativo';

        document.getElementById('tituloModalUsuario').innerText = 'Editar Usuário';
        const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
        modal.show();
    }

    function excluirUsuarioSelecionado() {
        if (!usuarioAtual) return;
        if (confirm(`Tem certeza que deseja excluir ${usuarioAtual.nome}?`)) {
            window.location.href = '<?=url_to('/user/deletar?id=')?>' + usuarioAtual.id;
        }
    }

    // Ao carregar a página, seleciona o primeiro usuário da lista automaticamente
    document.addEventListener("DOMContentLoaded", function() {
        const primeiroItem = document.querySelector('.item-usuario');
        if (primeiroItem) {
            primeiroItem.click();
        } else {
            document.getElementById('acoesUsuario').style.display = 'none';
        }
    });
</script>