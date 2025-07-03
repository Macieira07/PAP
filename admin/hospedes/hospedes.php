<?php
require '../../conexao.php';
session_start();

// Configurações de paginação
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$por_pagina = 15;
$offset = ($pagina - 1) * $por_pagina;

// Filtros e pesquisa
$pesquisa = $_GET['pesquisa'] ?? '';
$filtro = $_GET['filtro'] ?? '';
$ordenar = in_array($_GET['ordenar'] ?? 'H_nome', ['H_nome', 'H_email', 'H_data_criacao', 'H_bloqueado']) ? ($_GET['ordenar'] ?? 'H_nome') : 'H_nome';
$ordem = strtolower($_GET['ordem'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

// Construir query SQL
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM hospedes WHERE 1=1";
$params = [];

if (!empty($pesquisa)) {
    $sql .= " AND (H_nome LIKE ? OR H_email LIKE ? OR H_documento_ident LIKE ?)";
    $params = array_merge($params, array_fill(0, 3, "%$pesquisa%"));
}

if ($filtro === 'bloqueados') {
    $sql .= " AND H_bloqueado = 1";
} elseif ($filtro === 'ativos') {
    $sql .= " AND (H_bloqueado = 0 OR H_bloqueado IS NULL)";
}

// Corrigir: LIMIT e OFFSET devem ser inseridos diretamente na query
$sql .= " ORDER BY $ordenar $ordem LIMIT $por_pagina OFFSET $offset";
// Não adicionar $por_pagina e $offset em $params

// Preparar e executar a query
$stmt = $conexao->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Obter total de registros
$total = $conexao->query("SELECT FOUND_ROWS() as total")->fetch_assoc()['total'];
$total_paginas = ceil($total / $por_pagina);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Hóspedes</title>
    <link rel="stylesheet" href="../global.css">
    <style>
        .hospedes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .hospedes-container h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .filtros-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            width: 100%;
        }
        .filtros-container {
            flex: 1 1 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filtros-container input, .filtros-container select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-adicionar-hospede {
            background: linear-gradient(90deg, #2e5090 60%, #4e8cff 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(46,80,144,0.08);
            transition: background 0.2s, transform 0.2s;
            margin-left: auto;
            display: block;
        }
        .btn-adicionar-hospede:hover {
            background: linear-gradient(90deg, #4e8cff 60%, #2e5090 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .table-responsive {
            overflow-x: auto;
        }
        .paginacao {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        .paginacao a, .paginacao span {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .paginacao span.current {
            background: #2e5090;
            color: white;
            border-color: #2e5090;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-ativo {
            background: #d4edda;
            color: #155724;
        }
        .badge-bloqueado {
            background: #f8d7da;
            color: #721c24;
        }
        .acoes-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="hospedes-container">
        <h1>Gestão de Hóspedes</h1>
        
        <div class="filtros-bar">
            <form method="get" class="filtros-container">
                <input type="text" name="pesquisa" placeholder="Pesquisar..." value="<?= htmlspecialchars($pesquisa) ?>">
                <select name="filtro">
                    <option value="">Todos</option>
                    <option value="ativos" <?= $filtro === 'ativos' ? 'selected' : '' ?>>Ativos</option>
                    <option value="bloqueados" <?= $filtro === 'bloqueados' ? 'selected' : '' ?>>Bloqueados</option>
                </select>
                <select name="ordenar">
                    <option value="H_nome" <?= $ordenar === 'H_nome' ? 'selected' : '' ?>>Ordenar por Nome</option>
                    <option value="H_email" <?= $ordenar === 'H_email' ? 'selected' : '' ?>>Ordenar por Email</option>
                    <option value="H_data_criacao" <?= $ordenar === 'H_data_criacao' ? 'selected' : '' ?>>Ordenar por Data</option>
                </select>
                <select name="ordem">
                    <option value="asc" <?= $ordem === 'asc' ? 'selected' : '' ?>>Ascendente</option>
                    <option value="desc" <?= $ordem === 'desc' ? 'selected' : '' ?>>Descendente</option>
                </select>
                <button type="submit">Aplicar</button>
                <a href="hospedes.php" class="btn">Limpar</a>
            </form>
            <button id="btnAdicionarHospede" class="btn-adicionar-hospede">+ Adicionar Hóspede</button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Documento</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hospede = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $hospede['H_id_hospede'] ?></td>
                        <td><?= htmlspecialchars($hospede['H_nome']) ?></td>
                        <td><?= htmlspecialchars($hospede['H_email']) ?></td>
                        <td><?= htmlspecialchars($hospede['H_telefone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($hospede['H_documento_ident']) ?></td>
                        <td>
                            <span class="badge <?= $hospede['H_bloqueado'] ? 'badge-bloqueado' : 'badge-ativo' ?>">
                                <?= $hospede['H_bloqueado'] ? 'Bloqueado' : 'Ativo' ?>
                            </span>
                        </td>
                        <td class="acoes-cell">
                            <button class="btn btn-sm btn-editar" data-id="<?= $hospede['H_id_hospede'] ?>">Editar</button>
                            <button class="btn btn-sm btn-bloquear" data-id="<?= $hospede['H_id_hospede'] ?>" data-acao="<?= $hospede['H_bloqueado'] ? 'desbloquear' : 'bloquear' ?>">
                                <?= $hospede['H_bloqueado'] ? 'Desbloquear' : 'Bloquear' ?>
                            </button>
                            <a href="eliminar_hospede.php?id=<?= $hospede['H_id_hospede'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja eliminar este hóspede?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_paginas > 1): ?>
        <div class="paginacao">
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <?php if ($i == $pagina): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal para adicionar/editar hóspede -->
    <div id="modalHospede" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <button class="modal-close" onclick="fecharModal()">&times;</button>
            <div id="modalConteudo"></div>
        </div>
    </div>

    <script>
    // Funções para manipular o modal
    function abrirModal(url) {
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalConteudo').innerHTML = html;
                document.getElementById('modalHospede').style.display = 'flex';
            });
    }

    function fecharModal() {
        document.getElementById('modalHospede').style.display = 'none';
        document.getElementById('modalConteudo').innerHTML = '';
    }

    // Event listeners
    document.getElementById('btnAdicionarHospede').addEventListener('click', () => {
        abrirModal('adicionar_hospede.php?modal=1');
    });

    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            abrirModal(`editar_hospede.php?id=${id}&modal=1`);
        });
    });

    document.querySelectorAll('.btn-bloquear').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const acao = this.getAttribute('data-acao');
            
            fetch(`bloquear_hospede.php?id=${id}&acao=${acao}`)
                .then(response => response.text())
                .then(result => {
                    if (result === 'ok') {
                        location.reload();
                    } else {
                        alert('Ocorreu um erro ao atualizar o estado do hóspede.');
                    }
                });
        });
    });
    </script>
        <a href="../admin.php">← Voltar</a>
</body>
</html>