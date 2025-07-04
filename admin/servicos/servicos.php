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
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        .acoes-servicos-container {
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
        .filtro-servicos-container {
            display: flex;
            justify-content: center;
            margin: 30px 0 20px 0;
        }
        .filtro-servicos-form {
            display: flex;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 18px 20px;
            width: 100%;
            max-width: 900px;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filtro-servicos-form .filtro-group {
            flex: 1 1 180px;
            min-width: 140px;
        }
        .filtro-servicos-form input,
        .filtro-servicos-form select {
            width: 100%;
            border: 1.5px solid var(--cor-input-borda);
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 15px;
            background: #fff;
            color: var(--cor-texto);
            transition: var(--transicao);
        }
        .filtro-servicos-form button {
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
        .filtro-servicos-form button:hover {
            background: var(--cor-primaria-escura);
        }
        .servicos-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .servicos-table th {
            background: var(--cor-primaria);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        .servicos-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--cor-borda-clara);
            vertical-align: middle;
        }
        .servicos-table tr:last-child td {
            border-bottom: none;
        }
        .servicos-table tr:hover {
            background: var(--cor-table-row-hover);
        }
        
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
        @media (max-width: 700px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr { display: none; }
            td {
                position: relative;
                padding-left: 50%;
                min-height: 40px;
                border: none;
                border-bottom: 1px solid #eee;
            }
            td:before {
                position: absolute;
                top: 10px;
                left: 10px;
                width: 45%;
                white-space: nowrap;
                font-weight: bold;
            }
            td:nth-of-type(1):before { content: 'ID'; }
            td:nth-of-type(2):before { content: 'Nome'; }
            td:nth-of-type(3):before { content: 'Descrição'; }
            td:nth-of-type(4):before { content: 'Preço (€)'; }
            td:nth-of-type(5):before { content: 'Categoria'; }
            td:nth-of-type(6):before { content: 'Imagem'; }
            td:nth-of-type(7):before { content: 'Ações'; }
        }
        /* Imagem responsiva */
        img.thumbnail {
            max-width: 120px;
            max-height: 80px;
            width: auto;
            height: auto;
            border-radius: 4px;
            border: 1px solid #ccc;
            margin-bottom: 2px;
            cursor: pointer;
        }
        .galeria-miniatura {
            height: 40px;
            width: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin: 2px 2px 0 0;
            display: inline-block;
            vertical-align: middle;
            cursor: pointer;
        }
        /* Lightbox */
        .lightbox-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.85);
            z-index: 99999;
            justify-content: center;
            align-items: center;
        }
        .lightbox-content {
            position: relative;
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .lightbox-img {
            max-width: 80vw;
            max-height: 70vh;
            border-radius: 6px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.3);
        }
        .lightbox-close {
            position: absolute;
            top: 10px;
            right: 18px;
            font-size: 32px;
            color: #333;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 2;
        }
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 36px;
            color: #333;
            background: rgba(255,255,255,0.7);
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }
        .lightbox-nav.left { left: 10px; }
        .lightbox-nav.right { right: 10px; }
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
    <!-- Bloco centralizado com ícone, título e links -->
    <div style="display: flex; flex-direction: column; align-items: center; gap: 10px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
            <h1 style="margin: 0;">Todos os Serviços</h1>
        </div>
        <div style="margin-top: 5px;">
            <a href="../admin.php">← Voltar</a> |
            <a href="#" id="btnAdicionarServico">+ Adicionar Serviço</a>
        </div>
    </div>
    <div class="filtro-servicos-container">
      <form method="get" action="servicos.php" class="filtro-servicos-form">
        <div class="filtro-group">
          <input type="text" name="nome" placeholder="Pesquisar por nome" value="<?= htmlspecialchars(
            $filtro_nome) ?>">
        </div>
        <div class="filtro-group">
          <select name="categoria">
            <option value="">Todas as categorias</option>
            <?php while($cat = $categorias->fetch_assoc()): ?>
              <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="filtro-group">
          <input type="number" name="preco_min" placeholder="Preço mínimo" value="<?= htmlspecialchars($filtro_preco_min) ?>">
        </div>
        <div class="filtro-group">
          <input type="number" name="preco_max" placeholder="Preço máximo" value="<?= htmlspecialchars($filtro_preco_max) ?>">
        </div>
        <button type="submit"><i class="fa fa-search"></i> Filtrar</button>
      </form>
    </div>

    <div class="admin-container">
        <table class="servicos-table">
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
                            <?php
                            $imagens = [];
                            if (!empty($servico['S_imagem'])) {
                                $imagens[] = ['src' => '/' . htmlspecialchars($servico['S_imagem']), 'alt' => 'Imagem do serviço ' . htmlspecialchars($servico['S_nome'])];
                            }
                            $galeria = $conexao->query("SELECT caminho_imagem FROM servicos_imagens WHERE servico_id = " . (int)$servico['S_id_servico']);
                            while ($img = $galeria->fetch_assoc()) {
                                $imagens[] = ['src' => '/' . htmlspecialchars($img['caminho_imagem']), 'alt' => 'Galeria de ' . htmlspecialchars($servico['S_nome'])];
                            }
                            ?>
                            <?php if (count($imagens) > 0): ?>
                                <button type="button" onclick="abrirLightbox(<?= $servico['S_id_servico'] ?>, 0)" style="background:#2e5090;color:#fff;padding:6px 18px;border:none;border-radius:4px;cursor:pointer;">Visualizar galeria</button>
                                <script>
                                window.lightboxImagens = window.lightboxImagens || {};
                                window.lightboxImagens[<?= $servico['S_id_servico'] ?>] = <?= json_encode($imagens) ?>;
                                </script>
                            <?php else: ?>
                                (sem imagens)
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acao-btns">
                                <a href="#" class="button button-warning btnEditarServico" data-id="<?= $servico['S_id_servico'] ?>">
                                    <i class="fa fa-pen"></i> Editar
                                </a>
                                <a href="eliminar_servico.php?id=<?= $servico['S_id_servico'] ?>" class="button button-danger btnExcluirServico" data-nome="<?= htmlspecialchars($servico['S_nome']) ?>" onclick="return confirm('Tem certeza?')">
                                    <i class="fa fa-times"></i> Eliminar
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">Não existem serviços registados com os filtros atuais.</td></tr>
            <?php endif; ?>
        </table>
    </div>

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

    <!-- Lightbox HTML -->
    <div id="lightboxOverlay" class="lightbox-overlay">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="fecharLightbox()">&times;</button>
            <button class="lightbox-nav left" onclick="navegarLightbox(-1)">&#8592;</button>
            <img id="lightboxImg" class="lightbox-img" src="" alt="">
            <button class="lightbox-nav right" onclick="navegarLightbox(1)">&#8594;</button>
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
    
    // Substituir confirmação de exclusão por modal/alerta customizado
    document.querySelectorAll('.btnExcluirServico').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            const nome = this.getAttribute('data-nome');
            if (confirm('Tem certeza que deseja eliminar o serviço "' + nome + '"?')) {
                window.location.href = this.getAttribute('href');
            }
        };
    });
    
    // Toast para feedback
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
    
    // Exibir toast se houver flash message
    window.addEventListener('DOMContentLoaded', function() {
        const flash = document.querySelector('.flash-message');
        if (flash) {
            showToast(flash.textContent, flash.classList.contains('success') ? 'success' : 'error');
        }
    });

    let lightboxServicoId = null;
    let lightboxIndex = 0;
    function abrirLightbox(servicoId, index) {
        lightboxServicoId = servicoId;
        lightboxIndex = index;
        atualizarLightbox();
        document.getElementById('lightboxOverlay').style.display = 'flex';
    }
    function fecharLightbox() {
        document.getElementById('lightboxOverlay').style.display = 'none';
    }
    function navegarLightbox(delta) {
        const imagens = window.lightboxImagens[lightboxServicoId];
        lightboxIndex += delta;
        if (lightboxIndex < 0) lightboxIndex = imagens.length - 1;
        if (lightboxIndex >= imagens.length) lightboxIndex = 0;
        atualizarLightbox();
    }
    function atualizarLightbox() {
        const imagens = window.lightboxImagens[lightboxServicoId];
        const img = imagens[lightboxIndex];
        document.getElementById('lightboxImg').src = img.src;
        document.getElementById('lightboxImg').alt = img.alt;
    }
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('lightboxOverlay').style.display === 'flex') {
            if (e.key === 'ArrowLeft') navegarLightbox(-1);
            if (e.key === 'ArrowRight') navegarLightbox(1);
            if (e.key === 'Escape') fecharLightbox();
        }
    });
    </script>
</body>
</html>