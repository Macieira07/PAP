<?php
require '../../conexao.php';
session_start();

// Mensagem flash
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Filtros
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_preco_min = $_GET['preco_min'] ?? '';
$filtro_preco_max = $_GET['preco_max'] ?? '';
$filtro_nome = $_GET['nome'] ?? '';
$pagina = $_GET['pagina'] ?? 1;
$ordenacao = $_GET['ordenacao'] ?? 'S_id_servico';
$direcao = $_GET['direcao'] ?? 'ASC';

// Construir a query com filtros
$sql = "SELECT s.*, c.nome as categoria_nome 
        FROM servicos s 
        LEFT JOIN categorias_servico c ON s.S_categoria_id = c.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($filtro_categoria)) {
    $sql .= " AND s.S_categoria_id = ?";
    $params[] = $filtro_categoria;
    $types .= 'i';
}

if (!empty($filtro_preco_min)) {
    $sql .= " AND s.S_preco >= ?";
    $params[] = $filtro_preco_min;
    $types .= 'd';
}

if (!empty($filtro_preco_max)) {
    $sql .= " AND s.S_preco <= ?";
    $params[] = $filtro_preco_max;
    $types .= 'd';
}

if (!empty($filtro_nome)) {
    $sql .= " AND s.S_nome LIKE ?";
    $params[] = '%' . $filtro_nome . '%';
    $types .= 's';
}

// Ordenação
$ordenacao_valida = ['S_id_servico', 'S_nome', 'S_preco', 'categoria_nome'];
$ordenacao = in_array($ordenacao, $ordenacao_valida) ? $ordenacao : 'S_id_servico';
$direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';
$sql .= " ORDER BY $ordenacao $direcao";

// Paginação
$itens_por_pagina = 10;
$offset = ($pagina - 1) * $itens_por_pagina;

// Query para contar total de registos
$sql_count = str_replace('SELECT s.*, c.nome as categoria_nome', 'SELECT COUNT(*) as total', $sql);
$stmt_count = $conexao->prepare($sql_count);

if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_resultados = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_resultados / $itens_por_pagina);

// Query principal com paginação
$sql .= " LIMIT ? OFFSET ?";
$params[] = $itens_por_pagina;
$params[] = $offset;
$types .= 'ii';

$stmt = $conexao->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Buscar categorias para o dropdown
$categorias = $conexao->query("SELECT * FROM categorias_servico");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Serviços</title>
    <link rel="stylesheet" href="../global.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
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
        
        /* Estilos do modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        /* Cursor e efeito nas miniaturas */
        img.thumbnail {
            cursor: pointer;
            transition: 0.3s;
        }
        img.thumbnail:hover {
            opacity: 0.7;
        }
        
        /* Filtros */
        .filtros {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .filtros .filtro-group {
            margin-bottom: 10px;
        }
        .filtros label {
            display: inline-block;
            width: 100px;
        }
        
        /* Paginação */
        .paginacao {
            margin-top: 20px;
            text-align: center;
        }
        .paginacao a {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 2px;
            background: #eee;
            border-radius: 3px;
            text-decoration: none;
        }
        .paginacao a.active {
            background: #007bff;
            color: white;
        }
        
        /* Ordenação */
        .sortable {
            cursor: pointer;
        }
        .sortable:hover {
            background: #f0f0f0;
        }
        .sort-icon {
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Todos os Serviços</h1>
    </div>
    
    <a href="../admin.php">← Voltar</a> | 
    <a href="#" id="btnAdicionarServico">+ Adicionar Serviço</a>
    
    <!-- Filtros -->
    <div class="filtros">
        <form method="get" action="servicos.php" style="margin-top: 20px;">
            <input type="text" name="nome" placeholder="Pesquisar por nome" value="<?= isset($_GET['nome']) ? $_GET['nome'] : '' ?>">
            <button type="submit">Pesquisar</button>
        </form>
    </div>

    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Preço (€)</th>
            <th>Categoria</th>
            <th>Imagem</th>
            <th>Ações</th>
        </tr>
        <?php if ($resultado->num_rows > 0): ?>
            <?php while($servico = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $servico['S_id_servico'] ?></td>
                    <td><?= htmlspecialchars($servico['S_nome']) ?></td>
                    <td><?= nl2br(htmlspecialchars($servico['S_descricao'])) ?></td>
                    <td><?= number_format($servico['S_preco'], 2) ?></td>
                    <td><?= htmlspecialchars($servico['categoria_nome']) ?></td>
                    <td>
                        <?php if (!empty($servico['S_imagem'])): ?>
<img src="/fotos_servicos<?= $servico['S_imagem'] ?>" alt="Imagem Serviço" class="thumbnail" style="height: 150px; width: auto;">
              <?php else: ?>
                            (sem imagem)
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="#" class="btnEditarServico" data-id="<?= $servico['S_id_servico'] ?>">Editar</a> |
                        <a href="eliminar_servico.php?id=<?= $servico['S_id_servico'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">Não existem serviços registados com os filtros atuais.</td></tr>
        <?php endif; ?>
    </table>

    <!-- Paginação -->
    <?php if ($total_paginas > 1): ?>
    <div class="paginacao">
        <?php if ($pagina > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => 1])) ?>">« Primeira</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>">‹ Anterior</a>
        <?php endif; ?>
        
        <?php 
        $inicio = max(1, $pagina - 2);
        $fim = min($total_paginas, $pagina + 2);
        
        if ($inicio > 1) echo '<span>...</span>';
        
        for ($i = $inicio; $i <= $fim; $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>" <?= $i == $pagina ? 'class="active"' : '' ?>>
                <?= $i ?>
            </a>
        <?php endfor; 
        
        if ($fim < $total_paginas) echo '<span>...</span>';
        ?>
        
        <?php if ($pagina < $total_paginas): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">Próxima ›</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $total_paginas])) ?>">Última »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Modal para adicionar/editar serviço -->
    <div id="modalServico" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="max-width:420px; min-width:320px; position:relative;">
            <button onclick="fecharModalServico()" style="position:absolute; top:10px; right:10px; font-size:20px; background:none; border:none; cursor:pointer;">&times;</button>
            <div id="modalConteudoServico"></div>
        </div>
    </div>

    <script>
    function abrirModalServico() {
        document.getElementById('modalServico').style.display = 'flex';
    }
    function fecharModalServico() {
        document.getElementById('modalServico').style.display = 'none';
        document.getElementById('modalConteudoServico').innerHTML = '';
    }
    
    // Adicionar serviço
    document.getElementById('btnAdicionarServico').onclick = function(e) {
        e.preventDefault();
        fetch('adicionar_servico.php?modal=1')
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalConteudoServico').innerHTML = html;
                abrirModalServico();
                initWizardServico();
                bindFormAjaxServico();
            });
    };
    
    // Editar serviço
    document.querySelectorAll('.btnEditarServico').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            fetch('editar_servico.php?id=' + id + '&modal=1')
                .then(r => r.text())
                .then(html => {
                    document.getElementById('modalConteudoServico').innerHTML = html;
                    abrirModalServico();
                    initWizardServico();
                    bindFormAjaxServico();
                });
        };
    });
    
    // Submissão AJAX do formulário
    function bindFormAjaxServico() {
        const form = document.querySelector('#modalConteudoServico form');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                
                // Detectar se é edição ou adição pelo atributo data-id
                let url = 'adicionar_servico.php?modal=1';
                if (form.hasAttribute('data-id')) {
                    const id = form.getAttribute('data-id');
                    url = 'editar_servico.php?id=' + id + '&modal=1';
                }
                
                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(resp => {
                    if (resp.trim() === 'OK') {
                        fecharModalServico();
                        window.location.reload();
                    } else {
                        document.getElementById('modalConteudoServico').innerHTML = resp;
                        abrirModalServico();
                        initWizardServico();
                        bindFormAjaxServico();
                    }
                })
                .catch(function(err) {
                    alert('Erro ao adicionar/editar serviço: ' + err);
                });
            };
        }
    }
    
    function initWizardServico() {
        var btnProximo = document.getElementById('btnWizardProximoServico');
        var btnAnterior = document.getElementById('btnWizardAnteriorServico');
        if (btnProximo) {
            btnProximo.onclick = function() {
                var nome = document.querySelector('[name=nome_servico]').value.trim();
                var descricao = document.querySelector('[name=descricao]').value.trim();
                var preco = document.querySelector('[name=preco]').value.trim();
                if (!nome || !descricao || !preco) {
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
    }
    </script>
</body>
</html>