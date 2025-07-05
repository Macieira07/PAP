<?php
require '../../conexao.php';
session_start();

// Mensagens de feedback
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo = $_SESSION['tipo_mensagem'];
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
    
    echo "<script>
        window.onload = function() {
            mostrarNotificacao('$mensagem', '$tipo');
        };
    </script>";
}

// Filtros
$nomeFiltro = $_GET['nome'] ?? '';
$cargoFiltro = $_GET['cargo'] ?? '';
$tipoFiltro = $_GET['tipo_ausencia'] ?? '';
$funcFiltro = $_GET['funcionario'] ?? '';

// Paginação
$porPagina = 10;
$paginaAtual = $_GET['pagina'] ?? 1;
$offset = ($paginaAtual - 1) * $porPagina;

// Consulta funcionários
$sqlFuncionarios = "SELECT * FROM funcionarios WHERE 1=1";
if ($nomeFiltro) $sqlFuncionarios .= " AND F_nome LIKE '%".$conexao->real_escape_string($nomeFiltro)."%'";
if ($cargoFiltro) $sqlFuncionarios .= " AND F_cargo LIKE '%".$conexao->real_escape_string($cargoFiltro)."%'";
$sqlFuncionarios .= " LIMIT $offset, $porPagina";
$resultadoFuncionarios = $conexao->query($sqlFuncionarios);

// Total de funcionários
$sqlTotalFuncionarios = "SELECT COUNT(*) FROM funcionarios WHERE 1=1";
if ($nomeFiltro) $sqlTotalFuncionarios .= " AND F_nome LIKE '%".$conexao->real_escape_string($nomeFiltro)."%'";
if ($cargoFiltro) $sqlTotalFuncionarios .= " AND F_cargo LIKE '%".$conexao->real_escape_string($cargoFiltro)."%'";
$totalRegistros = $conexao->query($sqlTotalFuncionarios)->fetch_row()[0];
$totalPaginas = ceil($totalRegistros / $porPagina);

// Consulta férias/faltas
$sqlFeriasFaltas = "SELECT fa.*, f.F_nome FROM ferias_ausencias fa 
                   JOIN funcionarios f ON fa.F_id_funcionario = f.F_id_funcionario 
                   WHERE 1=1";
if ($tipoFiltro) $sqlFeriasFaltas .= " AND fa.tipo_ausencia='".$conexao->real_escape_string($tipoFiltro)."'";
if ($funcFiltro) $sqlFeriasFaltas .= " AND f.F_nome LIKE '%".$conexao->real_escape_string($funcFiltro)."%'";
$sqlFeriasFaltas .= " ORDER BY fa.data_inicio DESC";
$resultadoFeriasFaltas = $conexao->query($sqlFeriasFaltas);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Funcionários</title>
    <link rel="stylesheet" href="../global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" href="../assets/logos/favicon-32x32.png" sizes="32x32">
    <style>
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        .wizard-nav { display: flex; justify-content: space-between; margin-top: 20px; }
        .badge-ferias { background: #e3f6fd; color: #176b87; }
        .badge-falta { background: #fee2e2; color: #991b1b; }
        .modal-content { max-height: 90vh; overflow-y: auto; }
    </style>
</head>
<body>
<div class="admin-container">
    <h1 style="text-align:center; margin-bottom:30px; color:#2e5090; display:flex; align-items:center; justify-content:center; gap:12px; font-size:2.2rem;">
        <i class="fas fa-users"></i> Gestão de Funcionários
    </h1>

    <!-- Ações principais -->
    <div class="acoes-funcionarios-container">
        <a href="../admin.php" class="link-voltar">
            <i class="fa fa-arrow-left"></i> Voltar
        </a>
        <button id="btnAdicionarFuncionario" class="link-adicionar">
            <i class="fa fa-user-plus"></i> Adicionar Funcionário
        </button>
        <button id="btnAdicionarFeriasFalta" class="link-adicionar">
            <i class="fas fa-calendar-plus"></i> Adicionar Férias/Falta
        </button>
        <button id="btnAdicionarTurno" class="link-adicionar">
            <i class="fas fa-clock"></i> Adicionar Turno
        </button>
    </div>

    <!-- Filtros funcionários -->
    <div class="filtro-funcionarios-container">
        <form method="get" class="filtro-funcionarios-form">
            <input type="text" name="nome" placeholder="Filtrar por nome" value="<?= htmlspecialchars($nomeFiltro) ?>">
            <input type="text" name="cargo" placeholder="Filtrar por cargo" value="<?= htmlspecialchars($cargoFiltro) ?>">
            <button type="submit"><i class="fa fa-filter"></i> Filtrar</button>
        </form>
    </div>

    <!-- Tabela de Funcionários -->
    <div class="table-responsive">
        <table class="funcionarios-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Telefone</th>
                    <th>Data Contratação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($f = $resultadoFuncionarios->fetch_assoc()): ?>
                <tr>
                    <td><?= $f['F_id_funcionario'] ?></td>
                    <td><?= htmlspecialchars($f['F_nome']) ?></td>
                    <td><?= htmlspecialchars($f['F_email']) ?></td>
                    <td><?= htmlspecialchars($f['F_cargo']) ?></td>
                    <td><?= htmlspecialchars($f['F_telefone']) ?></td>
                    <td><?= date('d/m/Y', strtotime($f['F_data_contratacao'])) ?></td>
                    <td class="acao">
                        <div class="acao-btns">
                            <button class="button button-warning btnEditarFuncionario" data-id="<?= $f['F_id_funcionario'] ?>">
                                <i class="fa fa-pen"></i> Editar
                            </button>
                            <button class="button button-info btnVerFuncionario" data-id="<?= $f['F_id_funcionario'] ?>">
                                <i class="fa fa-eye"></i> Ver
                            </button>
                            <a href="eliminar_funcionario.php?id=<?= $f['F_id_funcionario'] ?>" class="button button-danger" onclick="return confirm('Tem certeza que deseja eliminar?')">
                                <i class="fa fa-times"></i> Eliminar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPaginas > 1): ?>
    <nav aria-label="Navegação de página" style="margin:18px 0; text-align:center;">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li>
                    <a href="?pagina=<?= $i ?>&nome=<?= urlencode($nomeFiltro) ?>&cargo=<?= urlencode($cargoFiltro) ?>"
                       class="<?= $i == $paginaAtual ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- Tabela de Férias/Faltas -->
    <h2 style="margin-top: 40px; color: #2e5090;">
        <i class="fas fa-calendar-alt"></i> Registros de Férias e Faltas
    </h2>
    
    <!-- Filtros férias/faltas -->
    <div class="filtro-funcionarios-container">
        <form method="get" class="filtro-funcionarios-form">
            <select name="tipo_ausencia" class="badge">
                <option value="">Todos</option>
                <option value="Férias" <?= $tipoFiltro === 'Férias' ? 'selected' : '' ?>>Férias</option>
                <option value="Falta" <?= $tipoFiltro === 'Falta' ? 'selected' : '' ?>>Falta</option>
            </select>
            <input type="text" name="funcionario" placeholder="Filtrar por funcionário" value="<?= htmlspecialchars($funcFiltro) ?>">
            <button type="submit"><i class="fa fa-filter"></i> Filtrar</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="funcionarios-table">
            <thead>
                <tr>
                    <th>Funcionário</th>
                    <th>Tipo</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Motivo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fa = $resultadoFeriasFaltas->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fa['F_nome']) ?></td>
                    <td>
                        <span class="badge <?= $fa['tipo_ausencia'] === 'Férias' ? 'badge-ferias' : 'badge-falta' ?>">
                            <?= htmlspecialchars($fa['tipo_ausencia']) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($fa['data_inicio'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($fa['data_fim'])) ?></td>
                    <td><?= htmlspecialchars($fa['motivo'] ?? 'N/A') ?></td>
                    <td class="acao">
                        <div class="acao-btns">
                            <button class="button button-warning btnEditarFerias" data-id="<?= $fa['F_id_ausencia'] ?>">
                                <i class="fa fa-pen"></i> Editar
                            </button>
                            <a href="eliminar_ausencia.php?id=<?= $fa['F_id_ausencia'] ?>" class="button button-danger" onclick="return confirm('Tem certeza que deseja eliminar?')">
                                <i class="fa fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Adicionar/Editar Funcionário -->
<div id="modalFuncionario" class="modal">
    <div class="modal-content" id="modalFuncionarioContent">
        <button class="modal-close" onclick="fecharModal('modalFuncionario')">×</button>
        <div id="conteudoModalFuncionario"></div>
    </div>
</div>

<!-- Modal Adicionar/Editar Férias/Falta -->
<div id="modalFeriasFalta" class="modal">
    <div class="modal-content" id="modalFeriasFaltaContent">
        <button class="modal-close" onclick="fecharModal('modalFeriasFalta')">×</button>
        <div id="conteudoModalFeriasFalta"></div>
    </div>
</div>

<!-- Modal Adicionar/Editar Turno -->
<div id="modalTurno" class="modal">
    <div class="modal-content" id="modalTurnoContent">
        <button class="modal-close" onclick="fecharModal('modalTurno')">×</button>
        <div id="conteudoModalTurno"></div>
    </div>
</div>

<script>
// Funções gerais
function mostrarNotificacao(mensagem, tipo) {
    const notification = document.createElement('div');
    notification.className = `notification ${tipo}`;
    notification.innerHTML = mensagem;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

function abrirModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Funcionários
document.getElementById('btnAdicionarFuncionario').addEventListener('click', function() {
    fetch('adicionar_funcionario.php?modal=1')
        .then(response => response.text())
        .then(html => {
            document.getElementById('conteudoModalFuncionario').innerHTML = html;
            abrirModal('modalFuncionario');
            initWizardFuncionario();
        });
});

document.querySelectorAll('.btnEditarFuncionario').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        fetch(`editar_funcionario.php?id=${id}&modal=1`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('conteudoModalFuncionario').innerHTML = html;
                abrirModal('modalFuncionario');
                initWizardFuncionario();
            });
    });
});

document.querySelectorAll('.btnVerFuncionario').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        fetch(`ver_funcionario.php?id=${id}&modal=1`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('conteudoModalFuncionario').innerHTML = html;
                abrirModal('modalFuncionario');
            });
    });
});

// Férias/Faltas
document.getElementById('btnAdicionarFeriasFalta').addEventListener('click', function() {
    fetch('adicionar_ferias_ausencias.php?modal=1')
        .then(response => response.text())
        .then(html => {
            document.getElementById('conteudoModalFeriasFalta').innerHTML = html;
            abrirModal('modalFeriasFalta');
        });
});

document.querySelectorAll('.btnEditarFerias').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        fetch(`editar_ferias.php?id=${id}&modal=1`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('conteudoModalFeriasFalta').innerHTML = html;
                abrirModal('modalFeriasFalta');
            });
    });
});

// Turnos
document.getElementById('btnAdicionarTurno').addEventListener('click', function() {
    fetch('adicionar_turno.php?modal=1')
        .then(response => response.text())
        .then(html => {
            document.getElementById('conteudoModalTurno').innerHTML = html;
            abrirModal('modalTurno');
        });
});

// Wizard Funcionário
function initWizardFuncionario() {
    const form = document.getElementById('formFuncionario');
    if (!form) return;

    const steps = Array.from(document.querySelectorAll('.wizard-step'));
    let currentStep = 0;
    
    // Mostrar primeiro passo
    steps[currentStep].classList.add('active');
    
    // Botões de navegação
    const btnProximo = document.getElementById('btnProximo');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnFinalizar = document.getElementById('btnFinalizar');
    
    if (btnProximo) {
        btnProximo.addEventListener('click', function() {
            if (!validarPasso(currentStep)) return;
            
            steps[currentStep].classList.remove('active');
            currentStep++;
            steps[currentStep].classList.add('active');
            atualizarBotoes();
        });
    }
    
    if (btnAnterior) {
        btnAnterior.addEventListener('click', function() {
            steps[currentStep].classList.remove('active');
            currentStep--;
            steps[currentStep].classList.add('active');
            atualizarBotoes();
        });
    }
    
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function() {
            if (!validarPasso(currentStep)) return;
            
            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(response => {
                if (response === 'OK') {
                    mostrarNotificacao('Operação realizada com sucesso!', 'sucesso');
                    fecharModal('modalFuncionario');
                    window.location.reload();
                } else {
                    document.getElementById('conteudoModalFuncionario').innerHTML = response;
                    initWizardFuncionario();
                }
            });
        });
    }
    
    function validarPasso(passo) {
        // Validação básica - pode ser expandida
        const inputs = steps[passo].querySelectorAll('[required]');
        let valido = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.style.borderColor = 'red';
                valido = false;
            } else {
                input.style.borderColor = '';
            }
        });
        
        if (!valido) {
            mostrarNotificacao('Preencha todos os campos obrigatórios!', 'erro');
        }
        
        return valido;
    }
    
    function atualizarBotoes() {
        if (btnAnterior) btnAnterior.style.display = currentStep === 0 ? 'none' : 'block';
        if (btnProximo) btnProximo.style.display = currentStep === steps.length - 1 ? 'none' : 'block';
        if (btnFinalizar) btnFinalizar.style.display = currentStep === steps.length - 1 ? 'block' : 'none';
    }
    
    atualizarBotoes();
}

// Fechar modal ao clicar fora
window.addEventListener('click', function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
});

// Fechar com ESC
window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});
</script>
</body>
</html>