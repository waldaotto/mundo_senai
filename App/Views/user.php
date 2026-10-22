<?php
/**
 * View: Gestão de Usuários (lista + detalhes)
 *
 * Espera receber $usuarios (array de arrays) vindo do Controller.
 */

// Mapa de cargos (1 a 10). Ajuste os nomes conforme a sua regra; níveis sem nome viram "Nível N".
$cargos = [
    1  => 'Operador',
    5  => 'Supervisor',
    10 => 'Administrador',
];

// Normaliza os dados uma única vez:
// - remove a senha (nunca pode ir para o HTML/JS)
// - converte status/cargo numéricos em texto para exibição
$usuarios = is_array($usuarios ?? null) ? $usuarios : [];
$usuarios = array_map(function (array $u) use ($cargos): array {
    unset($u['senha']);

    $u['status_texto'] = ((string) ($u['status'] ?? '0') === '1') ? 'Ativo' : 'Inativo';

    $nivel = (int) ($u['cargo'] ?? 0);
    $u['cargo_texto'] = $nivel > 0 ? ($cargos[$nivel] ?? 'Nível ' . $nivel) : 'Sem cargo';
    $u['cpf'] = $this->formatar_cpf($u['documento']);

    return $u;
}, $usuarios);

// Flags seguras para json_encode dentro de um atributo HTML delimitado por aspas simples.
// ATENÇÃO: não usar JSON_HEX_QUOT aqui, pois ele troca TODAS as aspas duplas por \u0022
// e o objeto deixa de ser um JavaScript válido.
$flagsJson = JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
?>
<!-- Importações Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    /* Estilização base dark */
    html, body, .main-container, main {
        background-color: #0d1117 !important;
        color: #c9d1d9 !important;
    }

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

    /* Inputs e busca */
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

    /* Lista de usuários (esquerda) */
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

    /* Detalhes do usuário (direita) */
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

    /* Modais dark */
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

    <!-- Header superior -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold text-light mb-0"><i class="fa-solid fa-users me-2"></i>Gestão de Usuários</h3>
            <span class="text-secondary small">Selecione um usuário da lista para visualizar todos os seus detalhes</span>
        </div>
        <button class="btn btn-senai-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="limparFormulario()">
            <i class="fa-solid fa-user-plus me-1"></i> Novo Usuário
        </button>
    </div>

    <!-- Layout dividido em colunas (mestre x detalhe) -->
    <div class="row g-3">

        <!-- ESQUERDA: busca e lista de usuários -->
        <div class="col-lg-5 col-md-6">
            <div class="card card-dark-panel">
                <div class="card-body p-3">

                    <!-- Campo de busca -->
                    <div class="input-group mb-3">
                        <input type="text" id="campoPesquisa" class="form-control form-control-dark"
                               placeholder="Buscar por nome, e-mail, matrícula..." onkeyup="filtrarUsuarios()">
                        <button class="btn btn-outline-secondary" type="button" onclick="filtrarUsuarios()">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>

                    <!-- Lista de usuários -->
                    <div class="user-list-group rounded overflow-hidden" id="listaUsuarios">
                        <?php if (!empty($usuarios)): ?>
                            <?php foreach ($usuarios as $index => $u): ?>
                                <div class="user-list-item d-flex align-items-center justify-content-between item-usuario <?= $index === 0 ? 'active' : '' ?>"
                                     id="item-user-<?= (int) ($u['id'] ?? $index) ?>"
                                     onclick='selecionarUsuario(<?= json_encode($u, $flagsJson) ?>, this)'>

                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center text-primary fw-bold"
                                             style="width: 40px; height: 40px; border: 1px solid #30363d;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-light nome-usuario">
                                                <?= htmlspecialchars((string) ($u['nome'] ?? 'Sem Nome')) ?>
                                            </div>
                                            <small class="text-secondary d-block email-usuario">
                                                <?= htmlspecialchars((string) ($u['email'] ?? '')) ?>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <span class="badge bg-<?= $u['status_texto'] === 'Ativo' ? 'success' : 'secondary' ?> mb-1">
                                            <?= htmlspecialchars($u['status_texto']) ?>
                                        </span>
                                        <div class="small text-muted"><?= htmlspecialchars($u['cargo_texto']) ?></div>
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

        <!-- DIREITA: detalhes do usuário selecionado -->
        <div class="col-lg-7 col-md-6">
            <div class="card card-dark-panel h-100">
                <div class="card-body p-4 d-flex flex-column" id="painelDetalhes">

                    <div class="d-flex align-items-center gap-4 border-bottom border-secondary border-opacity-25 pb-4 mb-4">
                        <!-- Avatar -->
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

                    <!-- Campos de informação -->
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

<!-- Modal: formulário CRUD -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-dark">
            <form id="formUsuario" action="<?= url_to('/user/salvar') ?>" method="POST">
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
                            <select class="form-select form-control-dark" id="cargo" name="cargo">
                                <?php for ($n = 1; $n <= 10; $n++): ?>
                                    <option value="<?= $n ?>"><?= $n ?> - <?= htmlspecialchars($cargos[$n] ?? 'Nível ' . $n) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select form-control-dark" id="status" name="status">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
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

<!-- Lógica do painel interativo mestre-detalhe -->
<script>
    const URL_DELETAR = <?= json_encode(url_to('/user/deletar?id=')) ?>;

    let usuarioAtual = null;

    // Executada ao clicar em um usuário da lista
    function selecionarUsuario(usuario, elemento) {
        usuarioAtual = usuario;

        // Atualiza a seleção visual na lista
        document.querySelectorAll('.user-list-item').forEach(function (item) {
            item.classList.remove('active');
        });
        if (elemento) elemento.classList.add('active');

        // Preenche o painel da direita
        document.getElementById('detalheId').innerText        = usuario.id || 'N/A';
        document.getElementById('detalheNome').innerText      = usuario.nome || 'Sem Nome';
        document.getElementById('detalheEmail').innerText     = usuario.email || 'N/A';
        document.getElementById('detalheDocumento').innerText = usuario.cpf || 'N/A';
        document.getElementById('detalheCargo').innerText     = usuario.cargo_texto || 'Geral';
        document.getElementById('detalheCargoTexto').innerText = usuario.cargo_texto || 'N/A';

        const statusBadge = document.getElementById('detalheStatus');
        statusBadge.innerText = usuario.status_texto || 'Inativo';
        statusBadge.className = 'badge ms-1 bg-' + (usuario.status_texto === 'Ativo' ? 'success' : 'secondary');

        // Mostra os botões de ação
        document.getElementById('acoesUsuario').style.display = 'flex';
    }

    // Filtra a lista em tempo real
    function filtrarUsuarios() {
        const termo = document.getElementById('campoPesquisa').value.toLowerCase();

        document.querySelectorAll('.item-usuario').forEach(function (item) {
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

        document.getElementById('usuarioId').value  = usuarioAtual.id || '';
        document.getElementById('nome').value       = usuarioAtual.nome || '';
        document.getElementById('email').value      = usuarioAtual.email || '';
        document.getElementById('documento').value  = usuarioAtual.documento || '';
        document.getElementById('cargo').value      = usuarioAtual.cargo || 1;
        document.getElementById('status').value     = usuarioAtual.status ?? 1;

        document.getElementById('tituloModalUsuario').innerText = 'Editar Usuário';

        const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
        modal.show();
    }

    function excluirUsuarioSelecionado() {
        if (!usuarioAtual) return;

        if (confirm('Tem certeza que deseja excluir ' + usuarioAtual.nome + '?')) {
            window.location.href = URL_DELETAR + encodeURIComponent(usuarioAtual.id);
        }
    }

    // Ao carregar a página, seleciona o primeiro usuário da lista
    document.addEventListener('DOMContentLoaded', function () {
        const primeiroItem = document.querySelector('.item-usuario');

        if (primeiroItem) {
            primeiroItem.click();
        } else {
            document.getElementById('acoesUsuario').style.display = 'none';
        }
    });
</script>