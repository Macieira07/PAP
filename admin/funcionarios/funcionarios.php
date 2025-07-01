<?php
require '../../conexao.php';

if (isset($_GET['mensagem'])) {
    $mensagem = $_GET['mensagem'];
    $tipo = $_GET['tipo'];
    echo "
    <script>
        window.onload = function() {
            let tipo = '$tipo';
            let mensagem = '$mensagem';

            let notification = document.createElement('div');
            notification.classList.add('notification', tipo);
            notification.innerHTML = mensagem;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        };
    </script>
    <style>
        .notification {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .notification.erro { background-color: #f44336; }
        .notification.sucesso { background-color: #4CAF50; }
    </style>
    ";
}

if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

$nomeFiltro = $_GET['nome'] ?? '';
$cargoFiltro = $_GET['cargo'] ?? '';
$porPagina = 10;
$paginaAtual = $_GET['pagina'] ?? 1;
$offset = ($paginaAtual - 1) * $porPagina;

$sql = "SELECT * FROM funcionarios WHERE 1=1";
if ($nomeFiltro) $sql .= " AND F_nome LIKE '%$nomeFiltro%'";
if ($cargoFiltro) $sql .= " AND F_cargo LIKE '%$cargoFiltro%'";
$sql .= " LIMIT $offset, $porPagina";
$resultado = $conexao->query($sql);

$sqlTotal = "SELECT COUNT(*) FROM funcionarios WHERE 1=1";
if ($nomeFiltro) $sqlTotal .= " AND F_nome LIKE '%$nomeFiltro%'";
if ($cargoFiltro) $sqlTotal .= " AND F_cargo LIKE '%$cargoFiltro%'";
$totalRegistros = $conexao->query($sqlTotal)->fetch_row()[0];
$totalPaginas = ceil($totalRegistros / $porPagina);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Funcionários</title>
    <link rel="stylesheet" href="../global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="../assets/logos/favicon-32x32.png" sizes="32x32">
    <style>
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }
        .flash-message.success { background-color: #4CAF50; }
        .flash-message.error { background-color: #f44336; }
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 30px 20px 20px 20px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.2);
            position: relative;
        }
    </style>
</head>
<body>
<div class="top-bar">
    <a href="admin.php">← Voltar</a>
</div>

<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=TDEKFc4RXwN_&format=png&color=000000" style="height: 50px;">
    <h1>Todos os Funcionários</h1>
</div>

<!-- Filtros -->
<form method="get" action="funcionarios.php">
    <input type="text" name="nome" placeholder="Filtrar por nome" value="<?= $nomeFiltro ?>" autofocus>
    <input type="text" name="cargo" placeholder="Filtrar por cargo" value="<?= $cargoFiltro ?>">
    <button type="submit"><i class="fa-solid fa-filter"></i> Filtrar</button>
</form>
<a href="#" id="btnAdicionarFuncionario" class="btn-adicionar"><i class="fa-solid fa-user-plus"></i> Adicionar Funcionário</a>
<!-- Tabela de Funcionários -->
<table border="1">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Cargo</th>
        <th>Telefone</th>
        <th>Data de Contratação</th>
        <th>Turno</th>
        <th>Ações</th>
    </tr>
    <?php while ($f = $resultado->fetch_assoc()): ?>
    <tr>
        <td><?= $f['F_id_funcionario'] ?></td>
        <td><?= $f['F_nome'] ?></td>
        <td><?= $f['F_email'] ?></td>
        <td><?= $f['F_cargo'] ?></td>
        <td><?= $f['F_telefone'] ?></td>
        <td><?= $f['F_data_contratacao'] ?></td>
        <td>
            <?php
            $stmt_turno = $conexao->prepare("SELECT * FROM turnos WHERE F_id_funcionario=?");
            $stmt_turno->bind_param("i", $f['F_id_funcionario']);
            $stmt_turno->execute();
            $turno = $stmt_turno->get_result()->fetch_assoc();
            ?>
<?php if ($turno): ?>
    <?= $turno['turno'] ?> (<?= $turno['data_inicio'] ?> - <?= $turno['data_fim'] ?>)
    <br><a href="#" class="btnEditarTurno" data-id="<?= $turno['T_id_turno'] ?>"><i class="fa-solid fa-pen-to-square"></i> Editar Turno</a>
<?php else: ?>

                Nenhum turno registrado
            <?php endif; ?>
        </td>
        <td>
            <a href="#" class="btnEditarFuncionario" data-id="<?= $f['F_id_funcionario'] ?>"><i class="fa-solid fa-pen-to-square"></i></a>
            <a href="eliminar_funcionario.php?id=<?= $f['F_id_funcionario'] ?>" onclick="return confirm('Tem a certeza que quer eliminar?')"><i class="fa-solid fa-trash"></i></a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Férias -->
<h2>Férias</h2>
<table border="1">
    <tr><th>Funcionário</th><th>Data de Início</th><th>Data de Fim</th><th>Ações</th></tr>
    <?php
    $stmt_ferias = $conexao->prepare("SELECT fa.*, f.F_nome FROM ferias_ausencias fa JOIN funcionarios f ON fa.F_id_funcionario = f.F_id_funcionario WHERE tipo_ausencia='Férias'");
    $stmt_ferias->execute();
    $resultado_ferias = $stmt_ferias->get_result();
    while ($ferias = $resultado_ferias->fetch_assoc()): ?>
    <tr>
        <td><?= $ferias['F_nome'] ?></td>
        <td><?= $ferias['data_inicio'] ?></td>
        <td><?= $ferias['data_fim'] ?></td>
        <td><a href="editar_ferias.php?id=<?= $ferias['F_id_ausencia'] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Faltas -->
<h2>Faltas</h2>
<table border="1">
    <tr><th>Funcionário</th><th>Data de Início</th><th>Data de Fim</th><th>Ações</th></tr>
    <?php
    $stmt_faltas = $conexao->prepare("SELECT fa.*, f.F_nome FROM ferias_ausencias fa JOIN funcionarios f ON fa.F_id_funcionario = f.F_id_funcionario WHERE tipo_ausencia='Falta'");
    $stmt_faltas->execute();
    $resultado_faltas = $stmt_faltas->get_result();
    while ($faltas = $resultado_faltas->fetch_assoc()): ?>
    <tr>
        <td><?= $faltas['F_nome'] ?></td>
        <td><?= $faltas['data_inicio'] ?></td>
        <td><?= $faltas['data_fim'] ?></td>
        <td><a href="editar_ferias.php?id=<?= $faltas['F_id_ausencia'] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Paginação -->
<div style="margin-top: 20px;">
    <?php if ($paginaAtual > 1): ?>
        <a href="?pagina=<?= $paginaAtual - 1 ?>&nome=<?= $nomeFiltro ?>&cargo=<?= $cargoFiltro ?>">← Anterior</a>
    <?php endif; ?>
    <?php if ($paginaAtual < $totalPaginas): ?>
        <a href="?pagina=<?= $paginaAtual + 1 ?>&nome=<?= $nomeFiltro ?>&cargo=<?= $cargoFiltro ?>">Próximo →</a>
    <?php endif; ?>
</div>
<a href="admin.php">← Voltar</a>

<!-- Modal para adicionar/editar funcionário -->
<div id="modalFuncionario" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:420px; min-width:320px; position:relative;">
        <button onclick="fecharModalFuncionario()" style="position:absolute; top:10px; right:10px; font-size:20px; background:none; border:none; cursor:pointer;">&times;</button>
        <div id="modalConteudoFuncionario"></div>
    </div>
</div>
<script>
function abrirModalFuncionario() {
    document.getElementById('modalFuncionario').style.display = 'flex';
}
function fecharModalFuncionario() {
    document.getElementById('modalFuncionario').style.display = 'none';
    document.getElementById('modalConteudoFuncionario').innerHTML = '';
}
document.getElementById('btnAdicionarFuncionario').onclick = function(e) {
    e.preventDefault();
    fetch('adicionar_funcionario.php?modal=1')
        .then(r => r.text())
        .then(html => {
            document.getElementById('modalConteudoFuncionario').innerHTML = html;
            abrirModalFuncionario();
            bindFormAjaxFuncionario();
            initWizardFuncionario();
        });
};
document.querySelectorAll('.btnEditarFuncionario').forEach(btn => {
    btn.onclick = function(e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        fetch('editar_funcionario.php?id=' + id + '&modal=1')
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalConteudoFuncionario').innerHTML = html;
                abrirModalFuncionario();
                bindFormAjaxFuncionario();
                initWizardFuncionario();
            });
    };
});
function bindFormAjaxFuncionario() {
    const form = document.querySelector('#modalConteudoFuncionario form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            let url = 'adicionar_funcionario.php?modal=1';
            if (form.hasAttribute('data-id')) {
                const id = form.getAttribute('data-id');
                url = 'editar_funcionario.php?id=' + id + '&modal=1';
            }
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.text())
            .then(resp => {
                if (resp.trim() === 'OK') {
                    fecharModalFuncionario();
                    window.location.reload();
                } else {
                    document.getElementById('modalConteudoFuncionario').innerHTML = resp;
                    abrirModalFuncionario();
                    bindFormAjaxFuncionario();
                    initWizardFuncionario();
                }
            })
            .catch(function(err) {
                alert('Erro ao adicionar/editar funcionário: ' + err);
            });
        };
    }
}
function initWizardFuncionario() {
    var btnProximo = document.getElementById('btnWizardProximoFuncionario');
    var btnAnterior = document.getElementById('btnWizardAnteriorFuncionario');
    if (btnProximo) {
        btnProximo.onclick = function() {
            var form = document.getElementById('formFuncionario');
            var nome = form.querySelector('[name=nome]').value.trim();
            var email = form.querySelector('[name=email]').value.trim();
            var senha = form.querySelector('[name=senha]').value.trim();
            var cargo = form.querySelector('[name=cargo]').value.trim();
            if (!nome || !email || !senha || !cargo) {
                alert('Preencha todos os campos obrigatórios do passo 1.');
                return;
            }
            document.getElementById('wizardStep1Funcionario').style.display = 'none';
            document.getElementById('wizardStep2Funcionario').style.display = 'block';
        };
    }
    if (btnAnterior) {
        btnAnterior.onclick = function() {
            document.getElementById('wizardStep2Funcionario').style.display = 'none';
            document.getElementById('wizardStep1Funcionario').style.display = 'block';
        };
    }
}
// Adicionar suporte para editar turno em modal
function bindFormAjaxEditarTurno() {
    const form = document.querySelector('#modalConteudoFuncionario #formEditarTurno');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            let url = form.getAttribute('action') || window.currentEditarTurnoUrl;
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.text())
            .then(resp => {
                if (resp.trim() === 'OK') {
                    fecharModalFuncionario();
                    window.location.reload();
                } else {
                    document.getElementById('modalConteudoFuncionario').innerHTML = resp;
                    abrirModalFuncionario();
                    bindFormAjaxEditarTurno();
                }
            })
            .catch(function(err) {
                alert('Erro ao editar turno: ' + err);
            });
        };
    }
}
document.querySelectorAll('.btnEditarTurno').forEach(btn => {
    btn.onclick = function(e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        window.currentEditarTurnoUrl = 'editar_turno.php?id=' + id + '&modal=1';
        fetch(window.currentEditarTurnoUrl)
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalConteudoFuncionario').innerHTML = html;
                abrirModalFuncionario();
                bindFormAjaxEditarTurno();
            });
    };
});
</script>
</body>
</html>
