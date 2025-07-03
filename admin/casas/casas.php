    <?php
require '../../conexao.php';
// Pesquisa
$pesquisa = isset($_GET['pesquisa']) ? $_GET['pesquisa'] : '';
// Mensagem flash
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}
// Paginação
$casas_por_pagina = 10;
$página_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($página_atual - 1) * $casas_por_pagina;

$query = "SELECT * FROM casas WHERE C_nome LIKE ? OR C_estado LIKE ? OR C_capacidade LIKE ? LIMIT ?, ?";
$stmt = $conexao->prepare($query);
$pesquisa_completa = "%$pesquisa%";
$stmt->bind_param("sssii", $pesquisa_completa, $pesquisa_completa, $pesquisa_completa, $offset, $casas_por_pagina);
$stmt->execute();
$resultado = $stmt->get_result();

// Total de casas para paginação
$query_total = "SELECT COUNT(*) FROM casas WHERE C_nome LIKE ? OR C_estado LIKE ? OR C_capacidade LIKE ?";
$stmt_total = $conexao->prepare($query_total);
$stmt_total->bind_param("sss", $pesquisa_completa, $pesquisa_completa, $pesquisa_completa);
$stmt_total->execute();
$total_resultados = $stmt_total->get_result()->fetch_row()[0];
$total_páginas = ceil($total_resultados / $casas_por_pagina);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../global.css">
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta charset="UTF-8">
    <title>Casas</title>
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
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .header-casas {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }
        .header-casas img {
            height: 56px;
            margin-bottom: 8px;
        }
        .header-casas h1 {
            margin: 0;
            font-size: 2.1em;
            font-weight: 700;
        }
        /* Tabela com visual igual ao das reservas */
        .casas-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .casas-table th {
            background: var(--cor-primaria);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        .casas-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--cor-borda-clara);
            vertical-align: middle;
        }
        .casas-table tr:last-child td {
            border-bottom: none;
        }
        .casas-table tr:hover {
            background: var(--cor-table-row-hover);
        }
        .filtro-casas-container {
            display: flex;
            justify-content: center;
            margin: 30px 0 20px 0;
        }
        .filtro-casas-form {
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
        .filtro-casas-form input[type="text"] {
            flex: 1;
            border: 1.5px solid var(--cor-input-borda);
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 16px;
            background: #fff;
            color: var(--cor-texto);
            transition: var(--transicao);
        }
        .filtro-casas-form button {
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
        .filtro-casas-form button:hover {
            background: var(--cor-primaria-escura);
        }
        .acoes-casas-container {
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
    </style>
</head>
<body class="dark-mode">
    <div class="header-casas">
        <img src="https://img.icons8.com/?size=100&id=9ECnYpBa4VDd&format=png&color=000000" alt="Ícone Casas">
        <h1>Lista de Alojamentos</h1>
    </div>

    <div class="acoes-casas-container">
      <a href="../admin.php" class="link-voltar"><i class="fa fa-arrow-left"></i> Voltar</a>
      <a href="#" id="btnAdicionarCasa" class="link-adicionar"><i class="fa fa-plus"></i> Adicionar Casa</a>
    </div>
    <div class="filtro-casas-container">
      <form method="get" action="casas.php" class="filtro-casas-form">
        <input type="text" name="pesquisa" placeholder="Pesquisar por nome, estado ou capacidade" value="<?= isset($_GET['pesquisa']) ? $_GET['pesquisa'] : '' ?>">
        <button type="submit">Pesquisar</button>
      </form>
    </div>

    <table class="casas-table">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Capacidade</th>
            <th>Preço/Noite</th>
            <th>Estado</th>
            <th>Ações</th>
        </tr>
        <?php while ($casa = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $casa['C_id_casa'] ?></td>
                <td><?= $casa['C_nome'] ?></td>
                <td><?= $casa['C_capacidade'] ?></td>
                <td><?= $casa['C_preco_noite'] ?>€</td>
                <td>
                    <?php
                        $estado = strtolower($casa['C_estado']);
                        $badgeClass = '';
                        switch ($estado) {
                            case 'disponível': $badgeClass = 'badge-success'; break;
                            case 'ocupada': $badgeClass = 'badge-warning'; break;
                            case 'manutenção': $badgeClass = 'badge-error'; break;
                            default: $badgeClass = 'badge-error'; break;
                        }
                    ?>
                    <span class="badge <?= $badgeClass ?>">
                        <?= ucfirst($casa['C_estado']) ?>
                    </span>
                </td>
                <td>
                    <a href="#" class="button button-warning btnEditarCasa" data-id="<?= $casa['C_id_casa'] ?>">
                        <i class="fa fa-edit"></i> Editar
                    </a>
                    <a href="eliminar_casa.php?id=<?= $casa['C_id_casa'] ?>" class="button button-danger" onclick="return confirm('Tem certeza?')">
                        <i class="fa fa-trash"></i> Eliminar
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="paginacao" style="margin-top: 20px;">
        <?php for ($i = 1; $i <= $total_páginas; $i++): ?>
            <a href="casas.php?pagina=<?= $i ?>&pesquisa=<?= $pesquisa ?>"><?= $i ?></a> 
        <?php endfor; ?>
    </div>

    <!-- Modal para adicionar/editar casa -->
    <div id="modalCasa" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="max-width:420px; min-width:320px; position:relative;">
            <button onclick="fecharModalCasa()" style="position:absolute; top:10px; right:10px; font-size:20px; background:none; border:none; cursor:pointer;">&times;</button>
            <div id="modalConteudoCasa"></div>
        </div>
    </div>

    <script>
    function abrirModalCasa() {
        document.getElementById('modalCasa').style.display = 'flex';
    }
    function fecharModalCasa() {
        document.getElementById('modalCasa').style.display = 'none';
        document.getElementById('modalConteudoCasa').innerHTML = '';
    }
    // Adicionar casa
    document.getElementById('btnAdicionarCasa').onclick = function(e) {
        e.preventDefault();
        fetch('adicionar_casa.php?modal=1')
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalConteudoCasa').innerHTML = html;
                abrirModalCasa();
                initWizardCasa();
                bindFormAjaxCasa();
            });
    };
    // Editar casa
    document.querySelectorAll('.btnEditarCasa').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            fetch('editar_casa.php?id=' + id + '&modal=1')
                .then(r => r.text())
                .then(html => {
                    document.getElementById('modalConteudoCasa').innerHTML = html;
                    abrirModalCasa();
                    initWizardCasa();
                    bindFormAjaxCasa();
                });
        };
    });
    // Submissão AJAX do formulário
    function bindFormAjaxCasa() {
        const form = document.querySelector('#modalConteudoCasa form');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                // Detectar se é edição ou adição pelo atributo data-id
                let url = 'adicionar_casa.php?modal=1';
                if (form.hasAttribute('data-id')) {
                    const id = form.getAttribute('data-id');
                    url = 'editar_casa.php?id=' + id + '&modal=1';
                }
                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(resp => {
                    if (resp.trim() === 'OK') {
                        fecharModalCasa();
                        window.location.reload();
                    } else {
                        document.getElementById('modalConteudoCasa').innerHTML = resp;
                        abrirModalCasa();
                        initWizardCasa();
                        bindFormAjaxCasa();
                    }
                })
                .catch(function(err) {
                    alert('Erro ao adicionar/editar casa: ' + err);
                });
            };
        }
    }

    function initWizardCasa() {
        var btnProximo = document.getElementById('btnWizardProximoCasa');
        var btnAnterior = document.getElementById('btnWizardAnteriorCasa');
        if (btnProximo) {
            btnProximo.onclick = function() {
                var nome = document.querySelector('[name=nome]').value.trim();
                var capacidade = document.querySelector('[name=capacidade]').value.trim();
                if (!nome || !capacidade) {
                    alert('Preencha todos os campos obrigatórios.');
                    return;
                }
                document.getElementById('wizardStep1').style.display = 'none';
                document.getElementById('wizardStep2').style.display = 'block';
            };
        }
        if (btnAnterior) {
            btnAnterior.onclick = function() {
                document.getElementById('wizardStep2').style.display = 'none';
                document.getElementById('wizardStep1').style.display = 'block';
            };
        }
        // Estado colorido
        var estadoSelect = document.getElementById('estadoSelectCasa');
        if (estadoSelect) {
            function updateEstadoColor() {
                var cor = '';
                switch(estadoSelect.value) {
                    case 'disponível': cor = '#28a745'; break;
                    case 'ocupada': cor = '#ff9800'; break;
                    case 'manutenção': cor = '#1976d2'; break;
                }
                estadoSelect.style.color = cor;
            }
            estadoSelect.addEventListener('change', updateEstadoColor);
            updateEstadoColor();
        }
    }
    </script>
</body>
</html>
