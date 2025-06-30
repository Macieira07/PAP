<?php
require '../conexao.php';
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
    <link rel="stylesheet" href="global.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos do modal */
        .modal {
            display: none;
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
        /* Modal de adição/edição */
        .modal-form {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-form-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        /* Serviços recomendados */
        .recomendados {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .recomendados h4 {
            margin-top: 0;
        }
        /* Notas internas */
        .nota-interna {
            font-size: 0.8em;
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Todos os Serviços</h1>
    </div>
    
    <button onclick="openModalForm('adicionar')" class="btn">+ Novo Serviço</button> 
    <a href="admin.php" class="btn">← Voltar ao Painel</a>
    
    <!-- Filtros -->
    <div class="filtros">
        <form id="filtrosForm" method="get">
            <div class="filtro-group">
                <label for="categoria">Categoria:</label>
                <select name="categoria" id="categoria" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php while($cat = $categorias->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filtro-group">
                <label for="preco_min">Preço:</label>
                <input type="number" step="0.01" name="preco_min" id="preco_min" placeholder="Mínimo" value="<?= htmlspecialchars($filtro_preco_min) ?>" onchange="this.form.submit()">
                <span>a</span>
                <input type="number" step="0.01" name="preco_max" id="preco_max" placeholder="Máximo" value="<?= htmlspecialchars($filtro_preco_max) ?>" onchange="this.form.submit()">
            </div>
            
            <div class="filtro-group">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($filtro_nome) ?>" placeholder="Pesquisar...">
                <button type="submit">Filtrar</button>
                <button type="button" onclick="resetFiltros()">Limpar</button>
            </div>
        </form>
    </div>
    
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th class="sortable" onclick="sortTable('S_id_servico')">ID 
                    <?php if($ordenacao == 'S_id_servico'): ?>
                        <i class="fas fa-sort-<?= $direcao == 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                    <?php endif; ?>
                </th>
                <th class="sortable" onclick="sortTable('S_nome')">Nome
                    <?php if($ordenacao == 'S_nome'): ?>
                        <i class="fas fa-sort-<?= $direcao == 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                    <?php endif; ?>
                </th>
                <th>Descrição</th>
                <th class="sortable" onclick="sortTable('S_preco')">Preço (€)
                    <?php if($ordenacao == 'S_preco'): ?>
                        <i class="fas fa-sort-<?= $direcao == 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                    <?php endif; ?>
                </th>
                <th class="sortable" onclick="sortTable('categoria_nome')">Categoria
                    <?php if($ordenacao == 'categoria_nome'): ?>
                        <i class="fas fa-sort-<?= $direcao == 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                    <?php endif; ?>
                </th>
                <th>Imagem</th>
                <th>Reservas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($servico = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $servico['S_id_servico'] ?></td>
                        <td><?= htmlspecialchars($servico['S_nome']) ?>
                            <?php if (!empty($servico['S_nota_interna'])): ?>
                                <div class="nota-interna"><?= htmlspecialchars($servico['S_nota_interna']) ?></div>
                            <?php endif; ?>
                        </td>
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
                            <?php 
                            // Buscar reservas associadas a este serviço
                            $stmt_reservas = $conexao->prepare("
                                SELECT r.R_id_reserva, r.R_data_checkin, r.R_data_checkout 
                                FROM reserva_servicos rs
                                JOIN reservas r ON rs.reserva_id = r.R_id_reserva
                                WHERE rs.servico_id = ?
                                ORDER BY r.R_data_checkin DESC
                                LIMIT 3
                            ");
                            $stmt_reservas->bind_param("i", $servico['S_id_servico']);
                            $stmt_reservas->execute();
                            $reservas = $stmt_reservas->get_result();
                            
                            if ($reservas->num_rows > 0) {
                                echo "<ul style='margin:0;padding-left:20px;'>";
                                while ($reserva = $reservas->fetch_assoc()) {
                                    $checkin = date('d/m/Y', strtotime($reserva['R_data_checkin']));
                                    $checkout = date('d/m/Y', strtotime($reserva['R_data_checkout']));
                                    echo "<li><a href='reservas.php?id={$reserva['R_id_reserva']}'>Reserva {$reserva['R_id_reserva']}</a> ($checkin - $checkout)</li>";
                                }
                                echo "</ul>";
                                if ($reservas->num_rows == 3) {
                                    echo "<small>+ mais reservas</small>";
                                }
                            } else {
                                echo "Nenhuma";
                            }
                            ?>
                        </td>
                        <td>
                            <button onclick="openModalForm('editar', <?= $servico['S_id_servico'] ?>)" class="btn-small">Editar</button>
                            <a href="eliminar_servico.php?id=<?= $servico['S_id_servico'] ?>" onclick="return confirm('Tem a certeza que deseja eliminar este serviço?')" class="btn-small btn-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">Não existem serviços registados com os filtros atuais.</td></tr>
            <?php endif; ?>
        </tbody>
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

    <!-- Modal para imagem grande -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="modal-close" onclick="closeModal(event)">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <!-- Modal para adicionar/editar serviço -->
    <div id="modalForm" class="modal-form">
        <div class="modal-form-content">
            <span class="close" onclick="closeModalForm()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Modal de imagem
        function openModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = "block";
            modalImg.src = src;
        }

        function closeModal(event) {
            if(event) event.stopPropagation();
            const modal = document.getElementById('imageModal');
            modal.style.display = "none";
            document.getElementById('modalImage').src = "";
        }

        // Modal de formulário
        function openModalForm(action, id = null) {
            const modal = document.getElementById('modalForm');
            const modalContent = document.getElementById('modalContent');
            
            let url = action === 'adicionar' ? 'adicionar_servico.php' : `editar_servico.php?id=${id}`;
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                    modal.style.display = "block";
                    
                    // Adicionar evento ao formulário para submit via AJAX
                    const form = modalContent.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            submitForm(this, action, id);
                        });
                    }
                });
        }

        function closeModalForm() {
            document.getElementById('modalForm').style.display = "none";
            document.getElementById('modalContent').innerHTML = "";
        }

        // Submit do formulário via AJAX
        function submitForm(form, action, id) {
            const formData = new FormData(form);
            const url = action === 'adicionar' ? 'adicionar_servico.php' : `editar_servico.php?id=${id}`;
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text();
                }
            })
            .then(html => {
                if (html) {
                    document.getElementById('modalContent').innerHTML = html;
                    // Rebind events if form is redisplayed (e.g., validation errors)
                    const form = document.getElementById('modalContent').querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            submitForm(this, action, id);
                        });
                    }
                }
            });
        }

        // Ordenação
        function sortTable(column) {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            
            if (params.get('ordenacao') === column) {
                params.set('direcao', params.get('direcao') === 'ASC' ? 'DESC' : 'ASC');
            } else {
                params.set('ordenacao', column);
                params.set('direcao', 'ASC');
            }
            
            window.location.href = url.pathname + '?' + params.toString();
        }

        // Reset de filtros
        function resetFiltros() {
            window.location.href = 'servicos.php';
        }

        // Pesquisa instantânea (AJAX)
        document.getElementById('nome').addEventListener('input', function() {
            const nome = this.value;
            const categoria = document.getElementById('categoria').value;
            const preco_min = document.getElementById('preco_min').value;
            const preco_max = document.getElementById('preco_max').value;
            
            const params = new URLSearchParams();
            if (nome) params.append('nome', nome);
            if (categoria) params.append('categoria', categoria);
            if (preco_min) params.append('preco_min', preco_min);
            if (preco_max) params.append('preco_max', preco_max);
            params.append('ajax', '1');
            
            fetch('servicos.php?' + params.toString())
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableBody = doc.querySelector('tbody');
                    document.querySelector('tbody').innerHTML = newTableBody.innerHTML;
                });
        });

        // Serviços recomendados (será chamado quando selecionar um serviço no modal)
        function mostrarRecomendados(servicoSelecionado) {
            const recomendados = {
                'Limpeza da Piscina': ['Reposição de Gás / Carvão', 'Manutenção do Jardim'],
                'Reparação de Equipamentos (TV, AC, etc.)': ['Serviço de Canalização / Eletricidade'],
                'Serviço de Lavandaria (Toalhas/Roupas de cama)': ['Compra de Produtos de Higiene'],
                'Compra de Produtos de Higiene': ['Serviço de Lavandaria (Toalhas/Roupas de cama)'],
                'Desinfestação / Controlo de Pragas': ['Limpeza Geral (final de estadia)'],
                'Manutenção do Jardim': ['Renovação de Plantas / Jardins']
            };
            
            const servicosRecomendados = recomendados[servicoSelecionado] || [];
            
            if (servicosRecomendados.length > 0) {
                const container = document.createElement('div');
                container.className = 'recomendados';
                container.innerHTML = `
                    <h4>Serviços Recomendados:</h4>
                    <ul>
                        ${servicosRecomendados.map(s => `<li><label><input type="checkbox" onclick="atualizarPreco()"> ${s}</label></li>`).join('')}
                    </ul>
                `;
                
                const form = document.querySelector('#modalContent form');
                if (form) {
                    const fieldset = form.querySelector('fieldset');
                    if (fieldset) {
                        fieldset.parentNode.insertBefore(container, fieldset.nextSibling);
                    }
                }
            }
        }
    </script>
</body>
</html>