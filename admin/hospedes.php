<?php
require '../conexao.php';
session_start();

// Obter parâmetro de pesquisa (sem filtro verificado)
$pesquisa = $_GET['pesquisa'] ?? '';

// Exibir flash message se existir
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Montar SQL com filtro só para pesquisa
$sql = "SELECT * FROM hospedes WHERE 1=1";

if (!empty($pesquisa)) {
    $pesq = $conexao->real_escape_string($pesquisa);
    $sql .= " AND (H_nome LIKE '%$pesq%' OR H_email LIKE '%$pesq%' OR H_documento_ident LIKE '%$pesq%')";
}

// Novo: filtro por bloqueados/ativos
$filtro = $_GET['filtro'] ?? '';
if ($filtro === 'bloqueados') {
    $sql .= " AND H_bloqueado = 1";
} elseif ($filtro === 'ativos') {
    $sql .= " AND (H_bloqueado = 0 OR H_bloqueado IS NULL)";
}

$resultado = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="../public/css/admin.css">
    <link rel="stylesheet" href="hospedes.css">
    <meta charset="UTF-8">
    <title>Hóspedes</title>
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
        tr.hospede-row:hover { background: #f0f4ff; transition: background 0.3s; }
        .btn-bloquear {
            padding: 6px 16px;
            border-radius: 20px;
            border: none;
            background: #2e5090;
            color: #fff;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            margin: 0 2px;
        }
        .btn-bloquear:hover {
            background: #f44336;
            transform: scale(1.08);
        }
        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            margin-left: 4px;
        }
        .badge-bloqueado { background: #f44336; }
        .badge-ativo { background: #4CAF50; }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=3Lghg94mD5Gd&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
        <h1>Todos os Hóspedes</h1>
    </div>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_hospede.php">+ Adicionar Hóspede</a>
    | <a href="hospedes.php?filtro=ativos">Ativos</a>
    | <a href="hospedes.php?filtro=bloqueados">Bloqueados</a>
    | <a href="hospedes.php">Todos</a>

    <!-- Formulário de pesquisa -->
    <form method="get" style="margin-top: 20px; margin-bottom: 20px;">
        <input type="text" name="pesquisa" placeholder="Pesquisar por nome, email ou documento" value="<?= htmlspecialchars($pesquisa) ?>">
        <button type="submit">Filtrar</button>
        <a href="hospedes.php" style="margin-left: 10px;">Limpar Filtros</a>
    </form>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Nome Completo</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Documento</th>
            <th>Verificado</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php while ($h = $resultado->fetch_assoc()): ?>
        <tr class="hospede-row">
            <td><?= $h['H_id_hospede'] ?></td>
            <td>
                <?= $h['H_nome'] ?>
                <?php if ($h['H_bloqueado']): ?>
                    <span title="Bloqueado" style="color:#f44336;font-size:18px;vertical-align:middle;">&#128274;</span>
                <?php endif; ?>
            </td>
            <td><?= $h['H_email'] ?></td>
            <td><?= $h['H_telefone'] ?></td>
            <td><?= $h['H_documento_ident'] ?></td>
            <td><?= $h['H_verificado_email'] ?></td>
            <td>
                <?php if ($h['H_bloqueado']): ?>
                    <span class="badge-status badge-bloqueado">Bloqueado</span>
                <?php else: ?>
                    <span class="badge-status badge-ativo">Ativo</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="editar_hospede.php?id=<?= $h['H_id_hospede'] ?>">Editar</a> |
                <a href="eliminar_hospede.php?id=<?= $h['H_id_hospede'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                |
                <?php if ($h['H_bloqueado']): ?>
                    <button class="btn-bloquear" data-id="<?= $h['H_id_hospede'] ?>" data-acao="desbloquear">Desbloquear</button>
                <?php else: ?>
                    <button class="btn-bloquear" data-id="<?= $h['H_id_hospede'] ?>" data-acao="bloquear">Bloquear</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table> 
    <a href="admin.php">← Voltar</a>

    <script>
    document.querySelectorAll('.btn-bloquear').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            const acao = this.dataset.acao;
            fetch(`bloquear_hospede.php?id=${id}&acao=${acao}`)
                .then(r => r.text())
                .then(resp => {
                    if (resp === 'ok') {
                        showToast(acao === 'bloquear' ? 'Hóspede bloqueado!' : 'Hóspede desbloqueado!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Erro ao atualizar!', 'error');
                    }
                });
        }
    });

    function showToast(msg, type='success') {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.style.position = 'fixed';
            toast.style.top = '30px';
            toast.style.right = '30px';
            toast.style.zIndex = 9999;
            toast.style.padding = '16px 28px';
            toast.style.borderRadius = '8px';
            toast.style.fontWeight = 'bold';
            toast.style.fontSize = '16px';
            toast.style.boxShadow = '0 2px 12px rgba(0,0,0,0.15)';
            toast.style.transition = 'all 0.4s';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.style.background = type === 'success' ? '#4CAF50' : '#f44336';
        toast.style.color = '#fff';
        toast.style.display = 'block';
        toast.style.opacity = 1;
        setTimeout(() => {
            toast.style.opacity = 0;
            setTimeout(() => toast.style.display = 'none', 400);
        }, 2000);
    }
    </script>
</body>
</html>
