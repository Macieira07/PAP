<?php
require '../conexao.php';
session_start();

// Mensagem flash
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Buscar serviços com nome da categoria para exibir
$sql = "SELECT s.*, c.nome as categoria_nome 
        FROM servicos s 
        LEFT JOIN categorias_servico c ON s.S_categoria_id = c.id
        ORDER BY s.S_id_servico ASC";
$resultado = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Serviços</title>
    <link rel="stylesheet" href="global.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <style>
        /* Estilos do modal */
        .modal {
            display: none; /* Escondido por padrão */
            position: fixed;
            z-index: 1000;
            padding-top: 60px;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
        }
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 80vh;
            box-shadow: 0 0 10px #000;
            border-radius: 4px;
        }
        .modal-close {
            position: absolute;
            top: 30px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
        }
        /* Cursor e efeito nas miniaturas */
        img.thumbnail {
            cursor: pointer;
            transition: 0.3s;
        }
        img.thumbnail:hover {
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Todos os Serviços</h1>
    </div>
    <a href="adicionar_servico.php">+ Novo Serviço</a> | <a href="admin.php">← Voltar ao Painel</a>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Preço (€)</th>
                <th>Categoria</th>
                <th>Imagem</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($servico = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $servico['S_id_servico'] ?></td>
                    <td><?= htmlspecialchars($servico['S_nome']) ?></td>
                    <td><?= nl2br(htmlspecialchars($servico['S_descricao'])) ?></td>
                    <td><?= number_format($servico['S_preco'], 2) ?></td>
                    <td><?= htmlspecialchars($servico['categoria_nome']) ?></td>
                    <td>
                        <?php if (!empty($servico['S_imagem'])): ?>
                            <img 
                                src="../<?= $servico['S_imagem'] ?>" 
                                alt="Imagem Serviço" 
                                class="thumbnail" 
                                style="max-height: 60px;" 
                                onclick="openModal(this.src)">
                        <?php else: ?>
                            (sem imagem)
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="editar_servico.php?id=<?= $servico['S_id_servico'] ?>">Editar</a> | 
                        <a href="eliminar_servico.php?id=<?= $servico['S_id_servico'] ?>" onclick="return confirm('Tem a certeza que deseja eliminar este serviço?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($resultado->num_rows == 0): ?>
                <tr><td colspan="7">Não existem serviços registados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal para imagem grande -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="modal-close" onclick="closeModal(event)">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        function openModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = "block";
            modalImg.src = src;
        }

        function closeModal(event) {
            // Se o evento existir, impedir que feche ao clicar na imagem
            if(event) event.stopPropagation();
            const modal = document.getElementById('imageModal');
            modal.style.display = "none";
            document.getElementById('modalImage').src = "";
        }
    </script>
</body>
</html>
