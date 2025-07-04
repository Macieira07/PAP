<?php
include '../conexao.php';
$mensagem = '';
// Adicionar modelo (processa o POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_modelo'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');
    if (!$titulo || !$conteudo) {
        $mensagem = '<p style="color:red;">Título e conteúdo são obrigatórios.</p>';
    } else {
        $stmt = $conexao->prepare("INSERT INTO modelos_newsletter (MN_titulo, MN_descricao, MN_conteudo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $titulo, $descricao, $conteudo);
        if ($stmt->execute()) {
            $mensagem = '<p style="color:green;">Modelo adicionado com sucesso!</p>';
        } else {
            $mensagem = '<p style="color:red;">Erro ao adicionar modelo.</p>';
        }
        $stmt->close();
    }
}
// Apagar modelo
if (isset($_GET['apagar'])) {
    $id = intval($_GET['apagar']);
    $stmt = $conexao->prepare("DELETE FROM modelos_newsletter WHERE MN_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem = "<p style='color:green;'>Modelo apagado com sucesso.</p>";
    } else {
        $mensagem = "<p style='color:red;'>Erro ao apagar modelo.</p>";
    }
    $stmt->close();
}
// Buscar modelos
$result = $conexao->query("SELECT * FROM modelos_newsletter ORDER BY MN_data_criacao DESC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Modelos Newsletter - Listar</title>
    <link rel="stylesheet" href="global.css" />
    <meta charset="UTF-8">
</head>
<body>
    <script src="https://cdn.tiny.cloud/1/mktwxkq2t7w5yim7b7gqo3ndcmusjcxuwkqkuhi8mwa08ux2/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#conteudo',
        menubar: false,
        plugins: 'image link media emoticons code',
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link media emoticons | code',
        height: 300
    });
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('formModelo');
        if (form) {
            form.addEventListener('submit', function (e) {
                tinymce.triggerSave();
                const titulo = document.getElementById('titulo').value.trim();
                const conteudo = document.getElementById('conteudo').value.trim();
                if (!titulo) {
                    alert('O título é obrigatório!');
                    e.preventDefault();
                    return false;
                }
                if (!conteudo) {
                    alert('O conteúdo é obrigatório!');
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
    </script>
    <style>
        .modelos-container {
            max-width: 900px;
            margin: 30px auto 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.05);
            padding: 18px 12px 18px 12px;
        }
        .modelos-title {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-bottom: 18px;
        }
        .modelos-title img {
            width: 60px;
            height: 60px;
            margin-bottom: 8px;
        }
        .modelos-table-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 0;
        }
        .modelos-table {
            width: 90%;
            max-width: 700px;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(51,84,147,0.05);
        }
        .modelos-table th {
            background: #335493;
            color: #fff;
            font-weight: 600;
            padding: 16px 10px;
            text-align: left;
            font-size: 1.1em;
        }
        .modelos-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e6e6e6;
            color: #222;
            font-size: 1em;
        }
        .modelos-table tr:last-child td {
            border-bottom: none;
        }
        .modelos-table tbody tr:nth-child(even) {
            background: #f6f8fa;
        }
        .modelos-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .modelos-links {
            display: flex;
            justify-content: center;
            gap: 18px;
            margin-bottom: 24px;
            margin-top: 18px;
        }
        @media (max-width: 700px) {
            .modelos-container { padding: 8px; }
            .modelos-table { width: 100%; font-size: 0.95em; }
        }
    </style>
    <div class="modelos-links">
        <a href="#" id="btn-toggle-form" style="font-weight:600; color:#335493; text-decoration:none; padding:8px 18px; border-radius:6px; background:#e6e6e6; transition:background .2s;">Adicionar Modelo</a>
        <a href="admin.php">← Voltar</a>
    </div>
    <div class="modelos-container">
        <div class="modelos-title">
            <img src="https://img.icons8.com/?size=100&id=SaUMpeyy7rHl&format=png&color=000000" alt="ícone newsletter">
            <h1 style="margin: 0;">Modelos Newsletter</h1>
        </div>
        <?= $mensagem ?>
        <!-- Formulário de adicionar modelo (escondido por padrão) -->
        <form id="formModelo" method="POST" action="" style="margin-bottom: 32px; display:none;">
            <h3 style="text-align:center; margin-bottom:10px;">Adicionar Novo Modelo</h3>
            <label for="titulo">Título:</label><br />
            <input type="text" id="titulo" name="titulo" required style="width:100%;max-width:500px;"/><br /><br />

            <label for="descricao">Descrição curta (opcional):</label><br />
            <textarea id="descricao" name="descricao" rows="2" style="width:100%;max-width:500px;"></textarea><br /><br />

            <label for="conteudo">Conteúdo HTML:</label><br />
            <textarea id="conteudo" name="conteudo"></textarea><br /><br />
            <button type="submit" name="adicionar_modelo" value="1">Adicionar Modelo</button>
        </form>
        <script>
        const btnToggle = document.getElementById('btn-toggle-form');
        const formModelo = document.getElementById('formModelo');
        btnToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (formModelo.style.display === 'none' || formModelo.style.display === '') {
                formModelo.style.display = 'block';
                btnToggle.textContent = 'Cancelar';
            } else {
                formModelo.style.display = 'none';
                btnToggle.textContent = 'Adicionar Modelo';
            }
        });
        </script>
        <div class="modelos-table-wrapper">
            <table class="modelos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Data Criação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['MN_id']) ?></td>
                        <td><?= htmlspecialchars($row['MN_titulo']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['MN_descricao'])) ?></td>
                        <td><?= htmlspecialchars($row['MN_data_criacao']) ?></td>
                        <td class="modelos-actions">
                            <a href="editar_modelo.php?id=<?= $row['MN_id'] ?>">Editar</a> |
                            <a href="modelos_newsletter.php?apagar=<?= $row['MN_id'] ?>" onclick="return confirm('Tem a certeza que quer apagar este modelo?');">Apagar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>