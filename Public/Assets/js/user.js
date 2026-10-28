/**
 * Lógica do Cliente - Gestão de Usuários (CRUD & Pesquisa)
 */

// Função de filtro/pesquisa local na tabela para funcionários
function filtrarUsuarios() {
    const termo = document.getElementById('campoPesquisa').value.toLowerCase();
    const linhas = document.querySelectorAll('#corpoTabelaUsuarios tr');

    linhas.forEach(linha => {
        if (linha.id === 'linhaSemDados') return;
        const textoLinha = linha.innerText.toLowerCase();
        if (textoLinha.includes(termo)) {
            linha.style.display = '';
        } else {
            linha.style.display = 'none';
        }
    });
}

// Ação do CRUD - Limpar formulário para Novo Registro
function limparFormulario() {
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('tituloModalUsuario').innerText = 'Cadastrar Usuário';
}

// Ação do CRUD - Preencher formulário para Edição
function editarUsuario(usuario) {
    document.getElementById('usuarioId').value = usuario.id || '';
    document.getElementById('nome').value = usuario.nome || '';
    document.getElementById('email').value = usuario.email || '';
    document.getElementById('documento').value = usuario.documento || '';
    document.getElementById('cargo').value = usuario.cargo || '';
    document.getElementById('status').value = usuario.status || 'Ativo';
    
    document.getElementById('tituloModalUsuario').innerText = 'Editar Usuário';
    
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
}

// Visualização Detalhada
function verDetalhes(usuario) {
    const conteudo = `
        <p><strong>ID:</strong> ${usuario.id || 'N/A'}</p>
        <p><strong>Nome:</strong> ${usuario.nome || 'N/A'}</p>
        <p><strong>E-mail:</strong> ${usuario.email || 'N/A'}</p>
        <p><strong>CPF / Matrícula:</strong> ${usuario.documento || 'N/A'}</p>
        <p><strong>Cargo / Função:</strong> ${usuario.cargo || 'N/A'}</p>
        <p><strong>Status:</strong> ${usuario.status || 'N/A'}</p>
    `;
    document.getElementById('conteudoDetalhes').innerHTML = conteudo;
    const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
    modal.show();
}

// Ação do CRUD - Deletar
function excluirUsuario(id) {
    if (confirm('Tem certeza que deseja excluir o usuário ID ' + id + '?')) {
        // Requisição de exclusão para o backend
        window.location.href = '/user/deletar?id=' + id;
    }
}