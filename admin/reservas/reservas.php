<?php
require '../../conexao.php';
session_start();

// Filtros
$where = "1=1";
$params = [];

// Filtro por status
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = $conexao->real_escape_string($_GET['status']);
    $where .= " AND r.R_estado = ?";
    $params[] = $status;
}

// Filtro por origem
if (isset($_GET['origem']) && $_GET['origem'] !== '') {
    $origem = $conexao->real_escape_string($_GET['origem']);
    $where .= " AND r.R_origem = ?";
    $params[] = $origem;
}

// Filtro por busca
if (isset($_GET['busca']) && $_GET['busca'] !== '') {
    $busca = "%" . $conexao->real_escape_string($_GET['busca']) . "%";
    $where .= " AND (h.H_nome LIKE ? OR c.C_nome LIKE ?)";
    $params[] = $busca;
    $params[] = $busca;
}

// Filtro por data
if (isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '') {
    $data_inicio = $conexao->real_escape_string($_GET['data_inicio']);
    $where .= " AND r.R_data_checkin >= ?";
    $params[] = $data_inicio;
}

if (isset($_GET['data_fim']) && $_GET['data_fim'] !== '') {
    $data_fim = $conexao->real_escape_string($_GET['data_fim']);
    $where .= " AND r.R_data_checkout <= ?";
    $params[] = $data_fim;
}

// Paginação
$por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_atual - 1) * $por_pagina;

// Consulta das reservas com contagem total
$query = "SELECT r.*, h.H_nome, h.H_telefone, h.H_email, c.C_nome, c.C_preco_noite
          FROM reservas r
          JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
          JOIN casas c ON r.R_id_casa = c.C_id_casa
          WHERE $where
          ORDER BY r.R_data_checkin DESC
          LIMIT ?, ?";

$stmt = $conexao->prepare($query);
// Corrigir montagem dos tipos: todos os filtros são string, os dois últimos (paginação) são inteiros
$types = str_repeat('s', count($params)) . 'ii';
$params_bind = $params;
$params_bind[] = $inicio;
$params_bind[] = $por_pagina;
$stmt->bind_param($types, ...$params_bind);
$stmt->execute();
$resultado = $stmt->get_result();

// Total para paginação
$query_total = "SELECT COUNT(*) as total 
                FROM reservas r
                JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
                JOIN casas c ON r.R_id_casa = c.C_id_casa
                WHERE $where";

$stmt_total = $conexao->prepare($query_total);
if (count($params) > 0) {
    $types_total = str_repeat('s', count($params));
    $stmt_total->bind_param($types_total, ...$params);
}
$stmt_total->execute();
$total_reservas = $stmt_total->get_result()->fetch_assoc()['total'];
$paginas = ceil($total_reservas / $por_pagina);

// Mensagem flash
if (isset($_SESSION['flash'])) {
    $flash_msg = $_SESSION['flash']['msg'];
    $flash_type = $_SESSION['flash']['type'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Gestão de Reservas</title>
    <style>
        /* Estilos específicos para a página de reservas */
        .reservas-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header-reservas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--cor-titulo);
        }
        
        .filter-group input, 
        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--cor-borda);
            border-radius: 5px;
        }
        
        .btn-add-reserva {
            background: var(--cor-primaria);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-add-reserva:hover {
            background: var(--cor-primaria-escura);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .reservas-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .reservas-table th {
            background: var(--cor-primaria);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        
        .reservas-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--cor-borda-clara);
            vertical-align: middle;
        }
        
        .reservas-table tr:last-child td {
            border-bottom: none;
        }
        
        .reservas-table tr:hover {
            background: var(--cor-table-row-hover);
        }
        
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .badge-pendente {
            background: #FFF3CD;
            color: #856404;
        }
        
        .badge-confirmada {
            background: #D4EDDA;
            color: #155724;
        }
        
        .badge-cancelada {
            background: #F8D7DA;
            color: #721C24;
        }
        
        .badge-concluida {
            background: #E2E3E5;
            color: #383D41;
        }
        
        .badge-checkin-hoje {
            background: #CCE5FF;
            color: #004085;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        
        .action-btn i {
            font-size: 12px;
        }
        
        .btn-edit {
            background: #FFC107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background: #E0A800;
        }
        
        .btn-pdf {
            background: #17A2B8;
            color: white;
        }
        
        .btn-pdf:hover {
            background: #138496;
        }
        
        .btn-cancel {
            background: #DC3545;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #C82333;
        }
        
        .btn-confirm {
            background: #28A745;
            color: white;
        }
        
        .btn-confirm:hover {
            background: #218838;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
        }
        
        .pagination a, 
        .pagination span {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            border: 1px solid var(--cor-borda);
        }
        
        .pagination a {
            color: var(--cor-primaria);
            background: white;
        }
        
        .pagination a:hover {
            background: var(--cor-link-hover);
        }
        
        .pagination .active {
            background: var(--cor-primaria);
            color: white;
            border-color: var(--cor-primaria);
        }
        
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: fadeIn 0.3s, fadeOut 0.3s 3s forwards;
            opacity: 0;
        }
        
        .flash-success {
            background: var(--cor-sucesso);
            opacity: 1;
        }
        
        .flash-error {
            background: var(--cor-erro);
            opacity: 1;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-20px); }
        }
        
        .hospede-info {
            display: flex;
            flex-direction: column;
        }
        
        .hospede-telefone {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
        
        .reserva-detalhes {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        
        .reserva-valor {
            font-weight: 600;
            color: var(--cor-primaria);
        }
        
        .no-reservas {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .reservas-table {
                display: block;
                overflow-x: auto;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="reservas-container">
        <?php include '../saldo_widget.php'; ?>
        
        <div class="header-reservas">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="https://img.icons8.com/?size=100&id=MCnPOwFJpCvG&format=png&color=000000" alt="Ícone Reservas" style="height: 50px;">
                <h1 style="margin: 0;">Gestão de Reservas</h1>
            </div>
            <a href="#" id="btnAdicionarReserva" class="btn-add-reserva">
                <i class="fas fa-plus"></i> Nova Reserva
            </a>
        </div>
        
        <?php if (isset($flash_msg)): ?>
            <div class="flash-message flash-<?= $flash_type ?>">
                <i class="fas fa-<?= $flash_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $flash_msg ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-form">
            <form method="get" action="reservas.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">Todos</option>
                            <option value="pendente" <?= (isset($_GET['status']) )&& $_GET['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="confirmada" <?= (isset($_GET['status'])) && $_GET['status'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                            <option value="cancelada" <?= (isset($_GET['status'])) && $_GET['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            <option value="concluída" <?= (isset($_GET['status'])) && $_GET['status'] == 'concluída' ? 'selected' : '' ?>>Concluída</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="origem">Origem</label>
                        <select name="origem" id="origem">
                            <option value="">Todas</option>
                            <option value="presencial" <?= (isset($_GET['origem'])) && $_GET['origem'] == 'presencial' ? 'selected' : '' ?>>Presencial</option>
                            <option value="online" <?= (isset($_GET['origem'])) && $_GET['origem'] == 'online' ? 'selected' : '' ?>>Online</option>
                            <option value="chamada" <?= (isset($_GET['origem'])) && $_GET['origem'] == 'chamada' ? 'selected' : '' ?>>Chamada</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="data_inicio">Check-in a partir de</label>
                        <input type="date" name="data_inicio" id="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="data_fim">Check-out até</label>
                        <input type="date" name="data_fim" id="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group" style="flex: 2;">
                        <label for="busca">Pesquisar (Nome ou Casa)</label>
                        <input type="text" name="busca" id="busca" value="<?= $_GET['busca'] ?? '' ?>" placeholder="Digite para buscar...">
                    </div>
                    
                    <div class="filter-group" style="align-self: flex-end;">
                        <button type="submit" class="action-btn btn-confirm" style="padding: 8px 16px;">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="reservas.php" class="action-btn btn-cancel" style="padding: 8px 16px;">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="reservas-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hóspede</th>
                        <th>Casa</th>
                        <th>Datas</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows === 0): ?>
                        <tr>
                            <td colspan="7" class="no-reservas">
                                <i class="fas fa-calendar-times" style="font-size: 24px; margin-bottom: 10px;"></i>
                                <p>Nenhuma reserva encontrada com os filtros atuais.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($r = $resultado->fetch_assoc()): 
                            $noites = (new DateTime($r['R_data_checkout']))->diff(new DateTime($r['R_data_checkin']))->days;
                            $is_checkin_today = date('Y-m-d') == $r['R_data_checkin'];
                        ?>
                            <tr>
                                <td><?= $r['R_id_reserva'] ?></td>
                                <td>
                                    <div class="hospede-info">
                                        <span><?= htmlspecialchars($r['H_nome']) ?></span>
                                        <span class="hospede-telefone"><?= htmlspecialchars($r['H_telefone']) ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($r['C_nome']) ?></td>
                                <td>
                                    <div class="reserva-detalhes">
                                        <span><strong>Entrada:</strong> <?= date('d/m/Y', strtotime($r['R_data_checkin'])) ?></span>
                                        <span><strong>Saída:</strong> <?= date('d/m/Y', strtotime($r['R_data_checkout'])) ?></span>
                                        <span><strong>Noites:</strong> <?= $noites ?></span>
                                    </div>
                                </td>
                                <td class="reserva-valor"><?= number_format($r['R_preco_total'], 2) ?>€</td>
                                <td>
                                    <?php 
                                        $badgeClass = 'badge-status ';
                                        switch ($r['R_estado']) {
                                            case 'pendente': 
                                                $badgeClass .= 'badge-pendente';
                                                break;
                                            case 'confirmada': 
                                                $badgeClass .= $is_checkin_today ? 'badge-checkin-hoje' : 'badge-confirmada';
                                                break;
                                            case 'cancelada': 
                                                $badgeClass .= 'badge-cancelada';
                                                break;
                                            case 'concluída': 
                                                $badgeClass .= 'badge-concluida';
                                                break;
                                        }
                                    ?>
                                    <span class="<?= $badgeClass ?>">
                                        <?= ucfirst($r['R_estado']) ?>
                                        <?= $is_checkin_today && $r['R_estado'] == 'confirmada' ? ' (Hoje)' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="#" class="action-btn btn-edit btnEditarReserva" data-id="<?= $r['R_id_reserva'] ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="gerar_pdf_reserva.php?id=<?= $r['R_id_reserva'] ?>" class="action-btn btn-pdf" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <?php if ($r['R_estado'] == 'pendente'): ?>
                                            <a href="processar_acao_reserva.php?acao=confirmar&id=<?= $r['R_id_reserva'] ?>" class="action-btn btn-confirm">
                                                <i class="fas fa-check"></i> Confirmar
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($r['R_estado'] != 'cancelada'): ?>
                                            <a href="processar_acao_reserva.php?acao=cancelar&id=<?= $r['R_id_reserva'] ?>" class="action-btn btn-cancel">
                                                <i class="fas fa-times"></i> Cancelar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($paginas > 1): ?>
            <div class="pagination">
                <?php if ($pagina_atual > 1): ?>
                    <a href="?pagina=1&<?= http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?pagina=<?= $pagina_atual - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php 
                    $inicio_paginas = max(1, $pagina_atual - 2);
                    $fim_paginas = min($paginas, $pagina_atual + 2);
                    
                    if ($inicio_paginas > 1) {
                        echo '<span>...</span>';
                    }
                    
                    for ($i = $inicio_paginas; $i <= $fim_paginas; $i++): 
                ?>
                    <a href="?pagina=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>" class="<?= $i == $pagina_atual ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($fim_paginas < $paginas): ?>
                    <span>...</span>
                <?php endif; ?>
                
                <?php if ($pagina_atual < $paginas): ?>
                    <a href="?pagina=<?= $pagina_atual + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?pagina=<?= $paginas ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para adicionar/editar reserva -->
    <div id="modalReserva" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="max-width:800px; min-width:320px; position:relative;">
            <button onclick="fecharModalReserva()" style="position:absolute; top:10px; right:10px; font-size:20px; background:none; border:none; cursor:pointer;">&times;</button>
            <div id="modalConteudoReserva"></div>
        </div>
    </div>

    <script>
    // Funções para controlar o modal
    function abrirModalReserva() {
        document.getElementById('modalReserva').style.display = 'flex';
    }
    
    function fecharModalReserva() {
        document.getElementById('modalReserva').style.display = 'none';
        document.getElementById('modalConteudoReserva').innerHTML = '';
    }
    
    // Abrir modal para adicionar nova reserva
    document.getElementById('btnAdicionarReserva').onclick = function(e) {
        e.preventDefault();
        fetch('adicionar_reserva.php?modal=1')
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalConteudoReserva').innerHTML = html;
                abrirModalReserva();
                initWizardReserva();
                bindFormAjaxReserva();
            });
    };
    
    // Abrir modal para editar reserva
    document.querySelectorAll('.btnEditarReserva').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            fetch('editar_reserva.php?id=' + id + '&modal=1')
                .then(r => r.text())
                .then(html => {
                    document.getElementById('modalConteudoReserva').innerHTML = html;
                    abrirModalReserva();
                    // Chamar funções do modal carregado
                    if (typeof initFlatpickrEditarReserva === 'function') {
                        initFlatpickrEditarReserva();
                    }
                    if (typeof initWizardReserva === 'function') {
                        initWizardReserva();
                    }
                    bindFormAjaxReserva();
                });
        };
    });
    
    // Configurar o envio do formulário via AJAX
    function bindFormAjaxReserva() {
        const form = document.querySelector('#modalConteudoReserva form');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                let url = 'adicionar_reserva.php?modal=1';
                if (form.hasAttribute('data-id')) {
                    const id = form.getAttribute('data-id');
                    url = 'editar_reserva.php?id=' + id + '&modal=1';
                }
                
                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(resp => {
                    if (resp.trim() === 'OK') {
                        // Recarregar a página para ver as mudanças
                        window.location.reload();
                    } else {
                        // Mostrar erros de validação
                        document.getElementById('modalConteudoReserva').innerHTML = resp;
                        abrirModalReserva();
                        initWizardReserva();
                        bindFormAjaxReserva();
                    }
                })
                .catch(err => {
                    alert('Erro ao processar a reserva: ' + err);
                });
            };
        }
    }
    
    // Configurar o wizard de 2 passos
    function initWizardReserva() {
        var btnProximo = document.getElementById('btnWizardProximoReserva');
        var btnAnterior = document.getElementById('btnWizardAnteriorReserva');
        
        if (btnProximo) {
            btnProximo.onclick = function() {
                // Validação dos campos obrigatórios do passo 1
                var camposObrigatorios = document.querySelectorAll('#wizardStep1 [required]');
                var valido = true;
                
                camposObrigatorios.forEach(function(campo) {
                    if (!campo.value.trim()) {
                        campo.style.borderColor = 'red';
                        valido = false;
                    } else {
                        campo.style.borderColor = '';
                    }
                });
                
                if (!valido) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                    return;
                }
                
                // Verificar se as datas são válidas
                var checkin = document.querySelector('[name=data_checkin]').value;
                var checkout = document.querySelector('[name=data_checkout]').value;
                
                if (new Date(checkout) <= new Date(checkin)) {
                    alert('A data de check-out deve ser posterior à data de check-in.');
                    return;
                }
                
                // Avançar para o passo 2
                document.getElementById('wizardStep1').style.display = 'none';
                document.getElementById('wizardStep2').style.display = 'block';
                document.getElementById('wizardStep1Bar').style.background = 'var(--cor-borda)';
                document.getElementById('wizardStep2Bar').style.background = 'var(--cor-primaria)';
            };
        }
        
        if (btnAnterior) {
            btnAnterior.onclick = function() {
                // Voltar para o passo 1
                document.getElementById('wizardStep2').style.display = 'none';
                document.getElementById('wizardStep1').style.display = 'block';
                document.getElementById('wizardStep1Bar').style.background = 'var(--cor-primaria)';
                document.getElementById('wizardStep2Bar').style.background = 'var(--cor-borda)';
            };
        }
    }
    
    // Fechar mensagem flash após 3 segundos
    setTimeout(() => {
        const flashMsg = document.querySelector('.flash-message');
        if (flashMsg) flashMsg.remove();
    }, 3000);
    </script>
</body>
</html>