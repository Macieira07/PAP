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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        .acoes-hospedes-container {
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
        .filtro-hospedes-container {
            display: flex;
            justify-content: center;
            margin: 30px 0 20px 0;
        }
        .filtro-hospedes-form {
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
        .filtro-hospedes-form input,
        .filtro-hospedes-form select {
            flex: 1 1 120px;
            border: 1.5px solid var(--cor-input-borda);
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 15px;
            background: #fff;
            color: var(--cor-texto);
            transition: var(--transicao);
        }
        .filtro-hospedes-form button {
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
        .filtro-hospedes-form button:hover {
            background: var(--cor-primaria-escura);
        }
        .hospedes-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .hospedes-table th {
            background: var(--cor-primaria);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        .hospedes-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--cor-borda-clara);
            vertical-align: middle;
        }
        .hospedes-table tr:last-child td {
            border-bottom: none;
        }
        .hospedes-table tr:hover {
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
    <div class="hospedes-container">
        <h1 style="text-align:center; margin-bottom:30px; color:#2e5090; display:flex; align-items:center; justify-content:center; gap:12px; font-size:2.2rem;">
            <img src="https://img.icons8.com/?size=100&id=3Lghg94mD5Gd&format=png&color=000000" alt="Hóspedes" style="height:38px; width:38px; vertical-align:middle;"> Gestão de Hóspedes
        </h1>
        <div class="acoes-hospedes-container" style="display:flex; gap:18px; margin-bottom:10px; align-items:center; justify-content:center;">
            <a href="../admin.php" class="link-voltar" style="color: var(--cor-primaria); font-weight:600; text-decoration:none; font-size:16px; display:flex; align-items:center; gap:6px; padding:6px 12px; border-radius:6px; transition:background 0.15s;">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
            <a href="#" id="btnAdicionarHospede" class="link-adicionar" style="color: var(--cor-primaria); font-weight:600; text-decoration:none; font-size:16px; display:flex; align-items:center; gap:6px; padding:6px 12px; border-radius:6px; transition:background 0.15s;">
                <i class="fa fa-user-plus"></i> Adicionar Hóspede
            </a>
        </div>
        <div class="filtro-hospedes-container">
            <form method="get" class="filtro-hospedes-form">
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
                <button type="submit"><i class="fa fa-filter"></i> Aplicar</button>
                <a href="hospedes.php" class="button button-outline">Limpar</a>
            </form>
        </div>
        <div class="table-responsive">
            <table class="hospedes-table">
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
                            <span class="badge <?= $hospede['H_bloqueado'] ? 'badge-error' : 'badge-success' ?>"> <?= $hospede['H_bloqueado'] ? 'Bloqueado' : 'Ativo' ?> </span>
                        </td>
                        <td class="acoes-cell">
                            <div class="acao-btns">
                                <button class="button button-warning btn-editar" data-id="<?= $hospede['H_id_hospede'] ?>"><i class="fa fa-pen"></i> Editar</button>
                                <button class="button button-info btn-bloquear" data-id="<?= $hospede['H_id_hospede'] ?>" data-acao="<?= $hospede['H_bloqueado'] ? 'desbloquear' : 'bloquear' ?>">
                                    <i class="fa fa-ban"></i> <?= $hospede['H_bloqueado'] ? 'Desbloquear' : 'Bloquear' ?>
                                </button>
                                <a href="eliminar_hospede.php?id=<?= $hospede['H_id_hospede'] ?>" class="button button-danger" onclick="return confirm('Tem certeza que deseja eliminar este hóspede?')"><i class="fa fa-times"></i> Eliminar</a>
                            </div>
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
</body>
</html>
