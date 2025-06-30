<?php
require '../conexao.php';
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
    <link rel="stylesheet" href="global.css">
    <meta charset="UTF-8">
    <title>Casas</title>
</head>
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
    </style>
<body class="dark-mode">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=9ECnYpBa4VDd&format=png&color=000000" alt="Ícone Casas" style="height: 50px;">
        <h1>Lista de Alojamentos</h1>
    </div>

    <a href="admin.php">← Voltar</a> | 
    <a href="#" id="btnAdicionarCasa">+ Adicionar Casa</a>
    <form method="get" action="casas.php" style="margin-top: 20px;">
        <input type="text" name="pesquisa" placeholder="Pesquisar por nome, estado ou capacidade" value="<?= isset($_GET['pesquisa']) ? $_GET['pesquisa'] : '' ?>">
        <button type="submit">Pesquisar</button>
    </form>

    <table border="1" cellpadding="10" style="margin-top: 20px;">
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
                <td><?= $casa['C_estado'] ?></td>
                <td>
                    <a href="#" class="btnEditarCasa" data-id="<?= $casa['C_id_casa'] ?>">Editar</a> |
                    <a href="eliminar_casa.php?id=<?= $casa['C_id_casa'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
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
    <div id="modalCasa" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:30px; border-radius:8px; min-width:350px; max-width:90vw; position:relative; max-height:90vh; overflow-y:auto;">
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
                fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(resp => {
                    if (resp.trim() === 'OK') {
                        window.location.reload();
                    } else {
                        document.getElementById('modalConteudoCasa').innerHTML = resp;
                        bindFormAjaxCasa();
                    }
                });
            };
        }
    }
    </script>
    <script>document.body.classList.toggle("dark-mode");</script>
</body>
</html>
