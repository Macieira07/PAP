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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        .acoes-funcionarios-container {
            display: flex;
            gap: 18px;
            margin-bottom: 10px;
            align-items: center;
        }
        .link-voltar, .link-adicionar {
            color: var(--cor-primaria);
            font-weight: 600;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background 0.15s;
        }
        .link-voltar:hover, .link-adicionar:hover {
            background: var(--cor-link-hover);
            text-decoration: none;
        }
        .link-adicionar {
            color: var(--cor-primaria);
        }
        .filtro-funcionarios-container {
            display: flex;
            justify-content: center;
            margin: 30px 0 20px 0;
        }
        .filtro-funcionarios-form {
            display: flex;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 18px 20px;
            width: 100%;
            max-width: 700px;
            gap: 12px;
            align-items: center;
        }
        .filtro-funcionarios-form input[type="text"] {
            flex: 1;
            border: 1.5px solid var(--cor-input-borda);
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 16px;
            background: #fff;
            color: var(--cor-texto);
            transition: var(--transicao);
        }
        .filtro-funcionarios-form button {
            background: var(--cor-primaria);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 10px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: var(--sombra-suave);
            transition: var(--transicao);
        }
        .filtro-funcionarios-form button:hover {
            background: var(--cor-primaria-escura);
        }
        .funcionarios-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .funcionarios-table th {
            background: var(--cor-primaria);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        .funcionarios-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--cor-borda-clara);
            vertical-align: middle;
        }
        .funcionarios-table tr:last-child td {
            border-bottom: none;
        }
        .funcionarios-table tr:hover {
            background: var(--cor-table-row-hover);
        }
        .acao-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }
        .button-info {
            background: #17a2b8;
            color: #fff;
        }
        .button-info:hover {
            background: #138496;
        }
    </style>
</head>
<body>
<div class="top-bar">
    <a href="admin.php">← Voltar</a>
</div>

<div class="acoes-funcionarios-container">
    <a href="../admin.php" class="link-voltar"><i class="fa fa-arrow-left"></i> Voltar</a>
    <a href="#" id="btnAdicionarFuncionario" class="link-adicionar"><i class="fa fa-user-plus"></i> Adicionar Funcionário</a>
</div>
<div class="filtro-funcionarios-container">
    <form method="get" action="funcionarios.php" class="filtro-funcionarios-form">
        <input type="text" name="nome" placeholder="Filtrar por nome" value="<?= $nomeFiltro ?>" maxlength="50" style="max-width:180px;">
        <input type="text" name="cargo" placeholder="Filtrar por cargo" value="<?= $cargoFiltro ?>" maxlength="30" style="max-width:140px;">
        <button type="submit"><i class="fa fa-filter"></i> Filtrar</button>
    </form>
</div>
<div class="table-responsive">
<table class="funcionarios-table">
    <thead>
    <tr>
        <th style="max-width:40px;">ID</th>
        <th style="max-width:160px;">Nome</th>
        <th style="max-width:180px;">Email</th>
        <th style="max-width:120px;">Cargo</th>
        <th style="max-width:120px;">Telefone</th>
        <th style="max-width:120px;">Data de Contratação</th>
        <th style="max-width:160px;">Turno</th>
        <th style="max-width:120px;">Ações</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($f = $resultado->fetch_assoc()): ?>
    <tr>
        <td><?= $f['F_id_funcionario'] ?></td>
        <td title="<?= htmlspecialchars($f['F_nome']) ?>">
            <?= strlen($f['F_nome']) > 25 ? htmlspecialchars(mb_substr($f['F_nome'],0,25)).'…' : htmlspecialchars($f['F_nome']) ?>
        </td>
        <td title="<?= htmlspecialchars($f['F_email']) ?>">
            <?= strlen($f['F_email']) > 28 ? htmlspecialchars(mb_substr($f['F_email'],0,28)).'…' : htmlspecialchars($f['F_email']) ?>
        </td>
        <td><span class="badge badge-info" title="<?= htmlspecialchars($f['F_cargo']) ?>">
            <?= strlen($f['F_cargo']) > 18 ? htmlspecialchars(mb_substr($f['F_cargo'],0,18)).'…' : htmlspecialchars($f['F_cargo']) ?>
        </span></td>
        <td><?= htmlspecialchars($f['F_telefone']) ?></td>
        <td><?= date('d/m/Y', strtotime($f['F_data_contratacao'])) ?></td>
        <td>
            <?php
            $stmt_turno = $conexao->prepare("SELECT * FROM turnos WHERE F_id_funcionario=?");
            $stmt_turno->bind_param("i", $f['F_id_funcionario']);
            $stmt_turno->execute();
            $turno = $stmt_turno->get_result()->fetch_assoc();
            ?>
            <?php if ($turno): ?>
                <span class="badge badge-success" title="Turno: <?= htmlspecialchars($turno['turno']) ?>">
                    <?= strlen($turno['turno']) > 12 ? htmlspecialchars(mb_substr($turno['turno'],0,12)).'…' : htmlspecialchars($turno['turno']) ?>
                </span><br>
                <span class="badge badge-info">Início: <?= date('d/m/Y', strtotime($turno['data_inicio'])) ?></span>
                <span class="badge badge-warning">Fim: <?= date('d/m/Y', strtotime($turno['data_fim'])) ?></span>
                <br><button class="button button-warning btnEditarTurno" data-id="<?= $turno['T_id_turno'] ?>"><i class="fa fa-pen"></i> Editar Turno</button>
            <?php else: ?>
                <span class="badge badge-error">Nenhum turno registrado</span>
            <?php endif; ?>
        </td>
        <td class="acao">
            <div class="acao-btns">
                <button class="button button-warning btnEditarFuncionario" data-id="<?= $f['F_id_funcionario'] ?>"><i class="fa fa-pen"></i> Editar</button>
                <a href="eliminar_funcionario.php?id=<?= $f['F_id_funcionario'] ?>" class="button button-danger" onclick="return confirm('Tem a certeza que quer eliminar?')"><i class="fa fa-times"></i> Eliminar</a>
            </div>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
<?php if ($totalPaginas > 1): ?>
<nav aria-label="Navegação de página" style="margin:18px 0; text-align:center;">
    <ul class="pagination" style="display:inline-flex;gap:4px;list-style:none;padding:0;">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li>
                <a href="?pagina=<?= $i ?>&nome=<?= urlencode($nomeFiltro) ?>&cargo=<?= urlencode($cargoFiltro) ?>"
                   class="<?= $i == $paginaAtual ? 'active' : '' ?>"
                   style="padding:6px 12px;border-radius:4px;border:1px solid #ccc;background:<?= $i==$paginaAtual?'#176b87':'#fff' ?>;color:<?= $i==$paginaAtual?'#fff':'#176b87' ?>;font-weight:600;text-decoration:none;transition:background .2s;">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Botão adicionar férias/falta -->
<div style="margin-bottom: 12px;">
  <button class="btn btn-info" id="btnAdicionarFeriasFalta"><i class="fa-solid fa-plus"></i> Adicionar Férias/Falta</button>
</div>
<!-- Modal Férias/Faltas -->
<div id="modalFeriasFalta" class="modal" style="display:none;">
  <div class="modal-content" id="modalFeriasFaltaContent" style="min-width:340px; max-width:98vw;" onclick="event.stopPropagation()">
    <button class="modal-close close-btn" style="position:absolute;top:10px;right:16px;font-size:24px;" onclick="fecharModalFeriasFalta()">×</button>
    <form id="formFeriasFalta" style="display:flex;flex-direction:column;gap:10px;">
      <h2 style="margin:0 0 10px 0; color:#176b87;">Adicionar Férias/Falta</h2>
      <label>Funcionário:
        <select name="funcionario_id" required>
          <?php $result = $conexao->query("SELECT F_id_funcionario, F_nome FROM funcionarios");
          while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['F_id_funcionario']}'>{$row['F_nome']}</option>";
          } ?>
        </select>
      </label>
      <label>Início: <input type="date" name="inicio" required></label>
      <label>Fim: <input type="date" name="fim" required></label>
      <label>Tipo:
        <select name="tipo" required>
          <option value="Férias">Férias</option>
          <option value="Falta">Falta</option>
        </select>
      </label>
      <button type="submit" class="btn btn-info">Salvar</button>
      <div id="feriasFaltaMsg" style="margin-top:8px;"></div>
    </form>
  </div>
</div>

<!-- Filtros férias/faltas -->
<div class="flex" style="gap:10px; margin-bottom:12px; flex-wrap:wrap;">
  <form method="get" style="display:flex;gap:8px;align-items:center;">
    <span class="badge badge-info">Filtrar:</span>
    <select name="tipo_ausencia" class="badge badge-info" style="font-weight:600;">
      <option value="">Todos</option>
      <option value="Férias" <?= (($_GET['tipo_ausencia']??'')==='Férias'?'selected':'') ?>>Férias</option>
      <option value="Falta" <?= (($_GET['tipo_ausencia']??'')==='Falta'?'selected':'') ?>>Falta</option>
    </select>
    <input type="text" name="funcionario" placeholder="Funcionário" value="<?= htmlspecialchars($_GET['funcionario']??'') ?>" class="badge badge-info" style="font-weight:600;">
    <button type="submit" class="btn btn-info btn-small"><i class="fa-solid fa-filter"></i> Filtrar</button>
  </form>
</div>
<!-- Tabela Férias/Faltas -->
<div class="table-responsive">
<table class="table table-hover table-striped">
  <thead>
    <tr>
      <th>Funcionário</th>
      <th>Tipo</th>
      <th>Início</th>
      <th>Fim</th>
      <th>Motivo</th>
      <th class="acao">Ações</th>
    </tr>
  </thead>
  <tbody>
  <?php
    $tipoFiltro = $_GET['tipo_ausencia'] ?? '';
    $funcFiltro = $_GET['funcionario'] ?? '';
    $sql = "SELECT fa.*, f.F_nome FROM ferias_ausencias fa JOIN funcionarios f ON fa.F_id_funcionario = f.F_id_funcionario WHERE 1=1";
    if ($tipoFiltro) $sql .= " AND fa.tipo_ausencia='".$conexao->real_escape_string($tipoFiltro)."'";
    if ($funcFiltro) $sql .= " AND f.F_nome LIKE '%".$conexao->real_escape_string($funcFiltro)."%'";
    $sql .= " ORDER BY fa.data_inicio DESC";
    $result = $conexao->query($sql);
    while ($fa = $result->fetch_assoc()): ?>
  <tr>
    <td><?= htmlspecialchars($fa['F_nome']) ?></td>
    <td><span class="badge badge-info"><?= htmlspecialchars($fa['tipo_ausencia']) ?></span></td>
    <td><span class="badge badge-info"><?= date('d/m/Y', strtotime($fa['data_inicio'])) ?></span></td>
    <td><span class="badge badge-info"><?= date('d/m/Y', strtotime($fa['data_fim'])) ?></span></td>
    <td><?= htmlspecialchars($fa['motivo'] ?? ($fa['FA_motivo'] ?? '')) ?></td>
    <td class="acao">
      <button class="btn btn-view btn-small btnEditarFerias" data-id="<?= $fa['F_id_ausencia'] ?>"><i class="fa-solid fa-pen-to-square"></i></button>
      <a href="eliminar_ferias.php?id=<?= $fa['F_id_ausencia'] ?>" class="btn button-danger btn-small" onclick="return confirm('Tem a certeza que quer eliminar este registro?')"><i class="fa-solid fa-trash"></i></a>
    </td>
  </tr>
<?php endwhile; ?>
  </tbody>
</table>
</div>

<script>
function abrirModalFuncionario(url) {
  const modal = document.getElementById('modalFuncionario');
  const content = document.getElementById('modalFuncionarioContent');
  content.innerHTML = '<div style="text-align:center;padding:40px 0;">Carregando...</div>';
  modal.style.display = 'flex';
  fetch(url + (url.includes('?') ? '&' : '?') + 'modal=1')
    .then(r => r.text())
    .then(html => {
      content.innerHTML = html;
      setTimeout(initWizardFuncionario, 50); // Garante que o JS do wizard é reexecutado
    });
}
function fecharModalFuncionario() {
  document.getElementById('modalFuncionario').style.display = 'none';
}
// Reexecuta o JS do wizard após carregar o modal via AJAX
function initWizardFuncionario() {
  var btnProx = document.getElementById('btnWizardProximoFuncionario');
  var btnAnt = document.getElementById('btnWizardAnteriorFuncionario');
  if (btnProx) btnProx.onclick = function() {
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
  if (btnAnt) btnAnt.onclick = function() {
    document.getElementById('wizardStep2Funcionario').style.display = 'none';
    document.getElementById('wizardStep1Funcionario').style.display = 'block';
  };
  // Intercepta o submit do formulário para AJAX
  var form = document.getElementById('formFuncionario');
  if (form) {
    form.onsubmit = function(e) {
      e.preventDefault();
      var formData = new FormData(form);
      fetch('adicionar_funcionario.php?modal=1', {
        method: 'POST',
        body: formData
      })
      .then(r => r.text())
      .then(resp => {
        if (resp.trim() === 'OK') {
          fecharModalFuncionario();
          window.location.reload();
        } else {
          document.getElementById('modalFuncionarioContent').innerHTML = resp;
          setTimeout(initWizardFuncionario, 50);
        }
      });
      return false;
    };
  }
}
function reatribuirEventosFuncionario() {
  // Abrir modal ao clicar em Adicionar Funcionário
  const btnAdd = document.getElementById('btnAdicionarFuncionario');
  if (btnAdd) btnAdd.onclick = function(e) {
    e.preventDefault();
    abrirModalFuncionario('adicionar_funcionario.php');
  };
  // Abrir modal ao clicar em Editar Funcionário
  Array.from(document.getElementsByClassName('btnEditarFuncionario')).forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      abrirModalFuncionario('editar_funcionario.php?id=' + id);
    };
  });
}
window.addEventListener('DOMContentLoaded', reatribuirEventosFuncionario);
// Fechar modal ao clicar fora
window.addEventListener('click', function(e) {
  const modal = document.getElementById('modalFuncionario');
  if (e.target === modal) fecharModalFuncionario();
});
// Fechar modal com tecla ESC
window.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') fecharModalFuncionario();
});
function abrirModalFeriasFalta() {
  document.getElementById('modalFeriasFalta').style.display = 'flex';
  document.getElementById('feriasFaltaMsg').innerHTML = '';
  document.getElementById('formFeriasFalta').reset();
}
function fecharModalFeriasFalta() {
  document.getElementById('modalFeriasFalta').style.display = 'none';
}
document.getElementById('btnAdicionarFeriasFalta').onclick = function(e) {
  e.preventDefault();
  abrirModalFeriasFalta();
};
// Fechar modal ao clicar fora
window.addEventListener('click', function(e) {
  const modal = document.getElementById('modalFeriasFalta');
  if (e.target === modal) fecharModalFeriasFalta();
});
// Fechar modal com tecla ESC
window.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') fecharModalFeriasFalta();
});
// Envio AJAX do formulário de férias/faltas
if (document.getElementById('formFeriasFalta')) {
  document.getElementById('formFeriasFalta').onsubmit = function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    fetch('../adicionar_ferias_ausencias.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.text())
    .then(resp => {
      if (resp.includes('Location:')) {
        fecharModalFeriasFalta();
        window.location.reload();
      } else {
        document.getElementById('feriasFaltaMsg').innerHTML = '<span style="color:red;">Erro ao adicionar. Tente novamente.</span>';
      }
    });
    return false;
  };
}
</script>
<style>
@media (max-width: 900px) {
    .funcionarios-table th, .funcionarios-table td { font-size: 13px; padding: 8px 6px; }
    .funcionarios-table th, .funcionarios-table td { max-width: 90px; overflow-x: auto; }
}
@media (max-width: 600px) {
    .funcionarios-table, .funcionarios-table thead, .funcionarios-table tbody, .funcionarios-table th, .funcionarios-table td, .funcionarios-table tr {
        display: block;
    }
    .funcionarios-table thead tr { display: none; }
    .funcionarios-table tr { margin-bottom: 18px; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
    .funcionarios-table td { position: relative; padding-left: 50%; min-height: 36px; }
    .funcionarios-table td:before {
        position: absolute;
        top: 8px; left: 12px;
        width: 45%;
        white-space: nowrap;
        font-weight: bold;
        color: #176b87;
        content: attr(data-label);
    }
}
.pagination .active { background: #176b87 !important; color: #fff !important; }
</style>
</body>
</html>
