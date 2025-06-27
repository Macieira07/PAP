<?php
session_start();
require '../conexao.php';

// Verifica se o usuário está logado (se o id está na sessão)
if (!isset($_SESSION['id'])) {
    // Redireciona para a página de login se não estiver logado
    header('Location: ../login1/login.php');
    exit;
}

// Função para criar uma notificação de reserva
function criarNotificacaoReserva($conexao, $reservaId, $hospedeNome, $casaNome, $dataCheckin) {
    $mensagem = "Nova reserva: $hospedeNome reservou $casaNome para " . date('d/m/Y', strtotime($dataCheckin));
    
    $stmt = $conexao->prepare("INSERT INTO notificacoes (tipo, mensagem, data_criacao) VALUES (?, ?, NOW())");
    $tipo = 'reserva';
    $stmt->bind_param("ss", $tipo, $mensagem);
    $stmt->execute();
}

// Função para verificar novas reservas e criar notificações
function verificarNovasReservas($conexao) {
    // Buscar a data da última notificação de reserva
    $ultimaNotificacao = $conexao->query("SELECT MAX(data_criacao) as ultima_data FROM notificacoes WHERE tipo = 'reserva'")->fetch_assoc();
    $ultimaData = $ultimaNotificacao['ultima_data'] ?? null;
    
    // Construir query para buscar reservas mais recentes que a última notificação
    $query = "SELECT r.R_id_reserva, h.H_nome, c.C_nome, r.R_data_checkin 
              FROM reservas r
              JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
              JOIN casas c ON r.R_id_casa = c.C_id_casa";

    if ($ultimaData) {
        $query .= " WHERE r.R_data_criacao > '" . $conexao->real_escape_string($ultimaData) . "'";
    }

    $query .= " ORDER BY r.R_data_criacao DESC";

    $reservas = $conexao->query($query);

    if ($reservas) {
        while ($reserva = $reservas->fetch_assoc()) {
            criarNotificacaoReserva($conexao, $reserva['R_id_reserva'], $reserva['H_nome'], $reserva['C_nome'], $reserva['R_data_checkin']);
        }
    }
}

// Função para obter o saldo atual (igual à usada em despesas.php)
function obterSaldoAtual($conexao) {
    $resultado = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
    if ($resultado && $resultado->num_rows > 0) {
        return (float) $resultado->fetch_assoc()['saldo'];
    } else {
        return 0;
    }
}

// Usar as funções
verificarNovasReservas($conexao);

$id = $_SESSION['id'];

$stmt = $conexao->prepare("SELECT F_nome, F_email FROM funcionarios WHERE F_id_funcionario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$funcionario = $resultado->fetch_assoc();

// Usar a função para obter saldo
$saldoAtual = obterSaldoAtual($conexao);

// Consultar notificações não lidas
$resultado_notificacoes = $conexao->query("
    SELECT n.*, 
        r.R_id_reserva as reserva_id,
        h.H_nome as hospede_nome,
        c.C_nome as casa_nome,
        r.R_data_checkin as data_checkin
    FROM notificacoes n
    LEFT JOIN reservas r ON n.mensagem LIKE CONCAT('%reserva #', r.R_id_reserva, '%')
    LEFT JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
    LEFT JOIN casas c ON r.R_id_casa = c.C_id_casa
    WHERE n.lida = 0
    ORDER BY n.data_criacao DESC
    LIMIT 5
");

$total_notificacoes = $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE lida = 0")->fetch_assoc()['total'];
?>


    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Painel de Administração - QUINTA FLORES</title>
        <!-- Flatpickr CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <style>
            /* Estilos Gerais */
            body {
                font-family: 'lufga';
                background: #f8f9fa;
                color: #343a40;
                margin: 0;
                padding: 0;
            }

            /* Container Principal */
            .admin-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            /* Cabeçalho - Nova estrutura com 3 colunas */
            .admin-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: 1px solid #e0e0e0;
            }

            /* Três seções do header: esquerda (perfil), meio (título) e direita (saldo e notificações) */
            .header-left {
                flex: 1;
                display: flex;
                justify-content: flex-start;
            }

            .header-center {
                flex: 1;
                display: flex;
                justify-content: center;
            }

            .header-center h1 {
                color: #2e5090;
                margin: 0;
                font-size: 28px;
                text-align: center;
            }

            .header-right {
                flex: 1;
                display: flex;
                justify-content: flex-end;
                align-items: center;
                gap: 20px;
            }

            /* Conta do Usuário - Agora à esquerda */
            .conta-usuario {
                position: relative;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .conta-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                border: 2px solid #2e5090;
            }

            .conta-dropdown {
                display: none;
                position: absolute;
                top: 50px;
                left: 0;
                background: white;
                border: 1px solid #e0e0e0;
                border-radius: 5px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                min-width: 200px;
                z-index: 100;
                padding: 10px;
            }

            .conta-usuario:hover .conta-dropdown {
                display: block;
            }

            .conta-dropdown p {
                margin: 5px 0;
                padding: 5px 10px;
            }

            .conta-dropdown a {
                color: #2e5090;
                text-decoration: none;
                display: block;
                padding: 5px 10px;
                border-radius: 3px;
            }

            .conta-dropdown a:hover {
                background-color: #f0f4ff;
            }

            /* Saldo - Design simplificado */
            .saldo-disponivel {
                display: flex;
                align-items: center;
                font-size: 14px;
                font-weight: 500;
            }

            .saldo-valor {
                padding: 4px 8px;
                border-radius: 4px;
                margin-left: 5px;
                font-weight: 600;
            }

            .saldo-positivo {
                color: #28a745;
            }

            .saldo-negativo {
                color: #dc3545;
            }

            /* Notificações */
            .icone-notificacao {
                position: relative;
                cursor: pointer;
                display: flex;
                align-items: center;
            }

            .icone-notificacao img {
                width: 24px;
                height: 24px;
                opacity: 0.8;
                transition: opacity 0.2s;
            }

            .icone-notificacao:hover img {
                opacity: 1;
            }

            .contador-notificacoes {
                background-color: #dc3545;
                color: white;
                border-radius: 50%;
                width: 18px;
                height: 18px;
                font-size: 11px;
                display: flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                top: -5px;
                right: -5px;
                font-weight: bold;
            }

            /* Menu de Cards */
            .menu-cards {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 20px;
                margin-top: 30px;
            }

            .card-opcao {
                background: white;
                padding: 25px 20px;
                border-radius: 8px;
                text-decoration: none;
                color: #343a40;
                box-shadow: 0 3px 10px rgba(0,0,0,0.08);
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 150px;
            }

            .card-opcao:hover {
                transform: translateY(-5px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                text-decoration: none;
            }

            .card-opcao img {
                width: 50px;
                height: 50px;
                margin-bottom: 15px;
                opacity: 0.9;
            }

            .card-opcao h3 {
                margin: 0;
                font-size: 16px;
                text-align: center;
                font-weight: 600;
            }

            /* Container de Notificações */
            .notificacoes-container {
                position: fixed;
                top: 70px;
                right: 30px;
                width: 380px;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
                z-index: 1000;
                display: none;
                max-height: 500px;
                overflow-y: auto;
                border: 1px solid #e0e0e0;
                font-family: 'Segoe UI', Roboto, sans-serif;
            }

            .notificacoes-header {
                padding: 16px 20px;
                border-bottom: 1px solid #f0f0f0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background-color: #f9f9f9;
                border-radius: 8px 8px 0 0;
            }

            .notificacoes-header h3 {
                margin: 0;
                font-size: 18px;
                color: #333;
                font-weight: 600;
            }

            .notificacoes-header .fechar {
                cursor: pointer;
                color: #666;
                font-size: 20px;
                line-height: 1;
            }

            .notificacoes-body {
                padding: 0;
            }

            .notificacao-item {
                padding: 16px 20px;
                border-bottom: 1px solid #f5f5f5;
                transition: background-color 0.2s;
                display: flex;
                align-items: flex-start;
            }

            .notificacao-item:last-child {
                border-bottom: none;
            }

            .notificacao-item:hover {
                background-color: #fafafa;
            }

            .notificacao-icone {
                margin-right: 15px;
                color: #2e5090;
                font-size: 18px;
                min-width: 24px;
                text-align: center;
            }

            .notificacao-conteudo {
                flex-grow: 1;
            }

            .notificacao-mensagem {
                color: #333;
                font-size: 14px;
                line-height: 1.4;
                margin-bottom: 4px;
            }

            .notificacao-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .notificacao-tempo {
                font-size: 12px;
                color: #888;
            }

            .notificacao-acoes {
                margin-left: 10px;
            }

            .notificacao-btn {
                background: none;
                border: none;
                color: #2e5090;
                font-size: 12px;
                cursor: pointer;
                padding: 4px 8px;
                border-radius: 4px;
                transition: all 0.2s;
            }

            .notificacao-btn:hover {
                background-color: #f0f4ff;
            }

            .sem-notificacoes {
                padding: 30px 20px;
                text-align: center;
                color: #888;
                font-size: 14px;
            }

            /* Responsividade */
            @media (max-width: 768px) {
                .admin-header {
                    flex-direction: column;
                    gap: 15px;
                }
                
                .header-left, .header-center, .header-right {
                    justify-content: center;
                    width: 100%;
                }
                
                .header-center {
                    order: -1; /* Posicionar o título no topo em telas menores */
                }
                
                .menu-cards {
                    grid-template-columns: 1fr 1fr;
                }
                
                .notificacoes-container {
                    width: 90%;
                    right: 5%;
                }
            }

            @media (max-width: 480px) {
                .menu-cards {
                    grid-template-columns: 1fr;
                }
                
                .header-right {
                    flex-direction: column;
                    align-items: center;
                    gap: 10px;
                }
            }
            
            .global-search-container {
                display: flex;
                justify-content: center;
                margin-bottom: 20px;
                position: relative;
            }
            .global-search-input {
                width: 100%;
                max-width: 400px;
                padding: 10px 15px;
                border: 1px solid #ccc;
                border-radius: 25px;
                font-size: 16px;
                outline: none;
                transition: border 0.2s;
            }
            .global-search-input:focus {
                border: 1.5px solid #2e5090;
            }
            .global-search-results {
                position: absolute;
                top: 45px;
                left: 50%;
                transform: translateX(-50%);
                width: 100%;
                max-width: 500px;
                background: #fff;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.08);
                z-index: 999;
                display: none;
            }
            .global-search-results.active {
                display: block;
            }
            .global-search-group {
                border-bottom: 1px solid #f0f0f0;
                padding: 8px 0 0 0;
            }
            .global-search-group:last-child {
                border-bottom: none;
            }
            .global-search-title {
                font-size: 13px;
                color: #888;
                margin: 8px 0 4px 16px;
                font-weight: bold;
            }
            .global-search-item {
                padding: 8px 16px;
                cursor: pointer;
                transition: background 0.15s;
                font-size: 15px;
            }
            .global-search-item:hover {
                background: #f0f4ff;
            }
            .global-search-empty {
                padding: 16px;
                color: #aaa;
                text-align: center;
            }
            .painel-atalhos-resumo {
                display: flex;
                flex-wrap: wrap;
                gap: 24px;
                margin-bottom: 28px;
                align-items: flex-start;
            }
            .atalhos-rapidos {
                display: flex;
                gap: 16px;
            }
            .atalho-btn {
                background: #2e5090;
                color: #fff;
                padding: 12px 22px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                text-decoration: none;
                box-shadow: 0 2px 8px rgba(46,80,144,0.08);
                transition: background 0.2s, transform 0.2s;
            }
            .atalho-btn:hover {
                background: #1d3557;
                transform: translateY(-2px);
            }
            .resumo-diario {
                display: flex;
                gap: 18px;
                background: #f8f9fa;
                border-radius: 10px;
                padding: 14px 24px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }
            .resumo-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                min-width: 110px;
            }
            .resumo-titulo {
                font-size: 13px;
                color: #888;
                margin-bottom: 4px;
            }
            .resumo-valor {
                font-size: 22px;
                font-weight: bold;
                color: #2e5090;
            }
            @media (max-width: 900px) {
                .painel-atalhos-resumo { flex-direction: column; gap: 16px; }
                .resumo-diario { flex-wrap: wrap; gap: 10px; }
            }
            .modal-overlay {
                position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0,0,0,0.4); z-index: 2000; display: flex; align-items: center; justify-content: center;
            }
            .modal-content {
                background: #fff; border-radius: 10px; padding: 32px 28px; min-width: 320px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,0.18);
                position: relative;
            }
            .modal-close {
                position: absolute; top: 12px; right: 18px; font-size: 28px; color: #888; cursor: pointer;
            }
            .modal-content h2 { margin-top: 0; }
            .modal-content .form-group { margin-bottom: 16px; }
            .modal-content label { display: block; margin-bottom: 4px; color: #2e5090; font-weight: 500; }
            .modal-content input, .modal-content select {
                width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px;
            }
            /* Flatpickr datas bloqueadas com X vermelho */
            .flatpickr-day.disabled {
                background: #ffdddd;
                color: #d00;
                position: relative;
            }
            .flatpickr-day.disabled:after {
                content: "✗";
                color: #d00;
                position: absolute;
                right: 2px;
                top: 2px;
                font-size: 14px;
                pointer-events: none;
            }
        </style>
    </head>
    <body>
        <div class="admin-container">
            <div class="admin-header">
                <!-- Seção Esquerda - Perfil do Usuário -->
                <div class="header-left">
                    <div class="conta-usuario">
                        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Conta" class="conta-avatar">
                        <div class="conta-dropdown">
                            <p><strong><?= htmlspecialchars($funcionario['F_nome']) ?></strong></p>
                            <p><?= htmlspecialchars($funcionario['F_email']) ?></p>
                            <a href="../login1/pagina_login.php">Terminar Sessão</a>
                        </div>
                    </div>
                </div>
                
                <!-- Seção Central - Título -->
                <div class="header-center">
                    <h1>Painel de Administração</h1>
                </div>
                
                <!-- Seção Direita - Saldo e Notificações -->
                <div class="header-right">
                    <!-- Saldo Simplificado -->
                    <div class="saldo-disponivel">
                        Saldo: 
                        <span class="saldo-valor <?= $saldoAtual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">
                            €<?= number_format($saldoAtual, 2, ',', '.'); ?>
                        </span>
                    </div>
                    
                    <!-- Ícone de Notificações -->
                    <div class="icone-notificacao" onclick="mostrarNotificacoes()">
                        <img src="https://cdn-icons-png.flaticon.com/512/565/565422.png" alt="Notificações">
                        <?php if ($total_notificacoes > 0): ?>
                        <div class="contador-notificacoes"><?= $total_notificacoes ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Busca Global -->
            <div class="global-search-container">
                <input type="text" id="globalSearchInput" class="global-search-input" placeholder="Buscar hóspedes, reservas, casas, funcionários...">
                <div id="globalSearchResults" class="global-search-results"></div>
            </div>
            
            <!-- Barra de Resumo + Atalhos -->
            <div class="painel-barra" style="display: flex; align-items: flex-start; gap: 24px; margin-bottom: 28px; flex-wrap: wrap;">
                <div class="resumo-diario" style="display: flex; gap: 18px; background: #f8f9fa; border-radius: 10px; padding: 14px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div class="resumo-item">
                        <span class="resumo-titulo">Reservas do Dia</span>
                        <span class="resumo-valor">
                            <?php
                            $hoje = date('Y-m-d');
                            $qtd_reservas = $conexao->query("SELECT COUNT(*) FROM reservas WHERE DATE(R_data_criacao) = '$hoje' AND R_estado != 'cancelada'")->fetch_row()[0];
                            echo $qtd_reservas;
                            ?>
                        </span>
                    </div>
                    <div class="resumo-item">
                        <span class="resumo-titulo">Check-ins Hoje</span>
                        <span class="resumo-valor">
                            <?php
                            $qtd_checkin = $conexao->query("SELECT COUNT(*) FROM reservas WHERE R_data_checkin = '$hoje' AND R_estado != 'cancelada'")->fetch_row()[0];
                            echo $qtd_checkin;
                            ?>
                        </span>
                    </div>
                </div>
                <div class="atalhos-rapidos" style="display: flex; gap: 16px; align-items: center;">
                    <button class="atalho-btn" onclick="abrirModalReserva(event)">+ Nova Reserva</button>
                    <button class="atalho-btn" onclick="abrirModalHospede(event)">+ Novo Hóspede</button>
                
                </div>
            </div>
            <!-- Modal Nova Reserva (wizard em etapas) -->
            <div id="modalReserva" class="modal-overlay" style="display:none;">
                <div class="modal-content" style="max-width: 600px;">
                    <span class="modal-close" onclick="fecharModalReserva()">&times;</span>
                    <h2>Nova Reserva</h2>
                    <form id="formNovaReservaModal">
                        <!-- Etapa 1: Dados da Reserva -->
                        <div class="wizard-step" id="step1">
                            <div class="form-group">
                                <label for="id_casa_modal"><i class="fas fa-home"></i> Casa:</label>
                                <select name="id_casa" id="id_casa_modal" required>
                                    <option value="">-- Selecione --</option>
                                    <?php $casas = $conexao->query("SELECT C_id_casa, C_nome, C_preco_noite FROM casas WHERE C_estado = 'disponível' ORDER BY C_nome");
                                    while ($c = $casas->fetch_assoc()): ?>
                                        <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>">
                                            <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'], 2) ?>€/noite)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data_checkin_modal"><i class="fas fa-calendar-check"></i> Check-in:</label>
                                <input type="text" id="data_checkin_modal" name="data_checkin" placeholder="Selecione a data" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="data_checkout_modal"><i class="fas fa-calendar-times"></i> Check-out:</label>
                                <input type="text" id="data_checkout_modal" name="data_checkout" placeholder="Selecione a data" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="id_hospede_modal"><i class="fas fa-user"></i> Hóspede:</label>
                                <select name="id_hospede" id="id_hospede_modal" required>
                                    <option value="">-- Selecione --</option>
                                    <?php $hospedes = $conexao->query("SELECT H_id_hospede, H_nome, H_telefone, H_bloqueado FROM hospedes ORDER BY H_nome");
                                    while ($h = $hospedes->fetch_assoc()): ?>
                                        <option value="<?= $h['H_id_hospede'] ?>" <?= $h['H_bloqueado'] ? 'disabled style="color:#aaa;"' : '' ?>>
                                            <?= htmlspecialchars($h['H_nome']) ?><?= $h['H_bloqueado'] ? ' (Bloqueado)' : '' ?> - <?= htmlspecialchars($h['H_telefone']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="num_hospedes_modal"><i class="fas fa-users"></i> Número de Hóspedes:</label>
                                <input type="number" id="num_hospedes_modal" name="num_hospedes" min="1" value="1" required>
                            </div>
                            <div class="form-group">
                                <label for="codigo_oferta_modal"><i class="fas fa-tag"></i> Código Promocional:</label>
                                <select id="codigo_oferta_modal" name="codigo_oferta" class="form-control">
                                    <option value="">-- Selecione uma oferta --</option>
                                    <option value="LOVE260">LOVE260</option>
                                    <option value="PARTY260">PARTY260</option>
                                    <option value="RETIRO240">RETIRO240</option>
                                </select>
                            </div>
                            <div id="detalhes-oferta-modal" style="display: none;">
                                <h4 id="titulo-oferta-modal"></h4>
                                <p id="descricao-oferta-modal"></p>
                                <p id="condicoes-oferta-modal" style="font-weight: bold;"></p>
                            </div>
                            <button type="button" class="atalho-btn" style="float:right;" onclick="wizardProximo(1)">Próximo &rarr;</button>
                        </div>
                        <!-- Etapa 2: Serviços Adicionais -->
                        <div class="wizard-step" id="step2" style="display:none;">
                            <fieldset style="margin-bottom:15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                <legend><i class="fas fa-concierge-bell"></i> Serviços Adicionais</legend>
                                <div class="form-group">
                                    <label for="decoracao_tematica_modal"><i class="fas fa-palette"></i> Decoração Temática (130€):</label>
                                    <select id="decoracao_tematica_modal" name="decoracao_tematica">
                                        <option value="">Selecione um tema</option>
                                        <option value="Romântico">Romântico</option>
                                        <option value="Aniversário">Aniversário</option>
                                        <option value="Natal">Natal</option>
                                        <option value="Lua de Mel">Lua de Mel</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-broom"></i> Limpeza Diária (15€/noite):</label>
                                    <input type="checkbox" id="limpeza_diaria_modal" name="limpeza_diaria">
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-gift"></i> Cesto de Boas-Vindas (10€):</label>
                                    <input type="checkbox" id="cesto_boas_vindas_modal" name="cesto_boas_vindas">
                                </div>
                            </fieldset>
                            <div class="form-group">
                                <label for="origem_modal"><i class="fas fa-map-marker-alt"></i> Origem da Reserva:</label>
                                <select name="origem" id="origem_modal" required>
                                    <option value="presencial">Presencial</option>
                                    <option value="chamada">Por Chamada</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <button type="button" class="atalho-btn" onclick="wizardAnterior(2)">&larr; Anterior</button>
                            <button type="button" class="atalho-btn" style="float:right;" onclick="wizardProximo(2)">Próximo &rarr;</button>
                        </div>
                        <!-- Etapa 3: Resumo & Confirmação -->
                        <div class="wizard-step" id="step3" style="display:none;">
                            <div class="total-box">
                                <h3><i class="fas fa-receipt"></i> Total Estimado</h3>
                                <p><i class="fas fa-tag"></i> Preço por noite: <span id="preco_noite_modal">0.00</span>€</p>
                                <p><i class="fas fa-moon"></i> Noites: <span id="noites_modal">0</span></p>
                                <p><i class="fas fa-concierge-bell"></i> Serviços adicionais: <span id="preco_servicos_modal">0.00</span>€</p>
                                <p><i class="fas fa-gift"></i> Desconto: <span id="desconto_oferta_modal">0.00</span>€</p>
                                <p><strong><i class="fas fa-money-bill-wave"></i> Total: <span id="preco_total_modal">0.00</span>€</strong></p>
                            </div>
                            <button type="button" class="atalho-btn" onclick="wizardAnterior(3)">&larr; Anterior</button>
                            <button type="submit" class="atalho-btn" style="float:right;"><i class="fas fa-check-circle"></i> Confirmar Reserva</button>
                        </div>
                    </form>
                    <div id="reservaMsgModal" style="margin-top:10px;"></div>
                </div>
            </div>
            <!-- Modal Novo Hóspede (Etapa única, igual registar.php) -->
            <div id="flashMessage" style="display:none;position:fixed;top:30px;left:50%;transform:translateX(-50%);background:#28a745;color:#fff;padding:14px 32px;border-radius:8px;font-size:17px;z-index:2000;box-shadow:0 2px 12px rgba(0,0,0,0.15);font-weight:600;transition:opacity 0.4s;"></div>
            <div id="modalHospede" class="modal-overlay" style="display:none;">
                <div class="modal-content" style="max-width: 500px;">
                    <span class="modal-close" onclick="fecharModalHospede()">&times;</span>
                    <h2>Novo Hóspede</h2>
                    <form id="formNovoHospedeModal">
                        <div class="form-group">
                            <label for="nome_hospede_modal">Nome:</label>
                            <input type="text" id="nome_hospede_modal" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="email_hospede_modal">Email:</label>
                            <input type="email" id="email_hospede_modal" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="pais_codigo_hospede_modal">País:</label>
                            <select name="pais_codigo" id="pais_codigo_hospede_modal" required>
                                <option value="+351">Portugal (+351)</option>
                                <option value="+34">Espanha (+34)</option>
                                <option value="+33">França (+33)</option>
                                <option value="+1">EUA (+1)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="telefone_hospede_modal">Telemóvel:</label>
                            <input type="text" id="telefone_hospede_modal" name="telefone" required placeholder="Número">
                        </div>
                        <div class="form-group">
                            <label for="documento_hospede_modal">NIF:</label>
                            <input type="text" id="documento_hospede_modal" name="documento" pattern="\d{9}" title="9 dígitos numéricos" required>
                        </div>
                        <div class="form-group">
                            <label for="morada_hospede_modal">Morada (opcional):</label>
                            <input type="text" id="morada_hospede_modal" name="morada">
                        </div>
                        <div class="form-group">
                            <label for="password_hospede_modal">Palavra-passe:</label>
                            <input type="password" id="password_hospede_modal" name="password" minlength="8" required placeholder="Mín. 8 caracteres, 1 maiúscula e 1 número">
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="aceitou" id="aceitou_hospede_modal" value="Sim" required> Aceito os Termos e Condições</label>
                        </div>
                        <button type="submit" class="atalho-btn" id="btnSalvarHospede" style="float:right;">Adicionar Hóspede</button>
                    </form>
                    <div id="hospedeMsgModal" style="margin-top:10px;"></div>
                </div>
            </div>
            
            <!-- Menu de Opções -->
            <div class="menu-cards">
                <a class="card-opcao" href="casas.php">
                    <img src="https://img.icons8.com/?size=100&id=9ECnYpBa4VDd&format=png&color=000000" alt="Casas">
                    <h3>Gerir Casas</h3>
                </a>
                <a class="card-opcao" href="hospedes.php">
                    <img src="https://img.icons8.com/?size=100&id=3Lghg94mD5Gd&format=png&color=000000" alt="Hóspedes">
                    <h3>Gerir Hóspedes</h3>
                </a>
                <a class="card-opcao" href="funcionarios.php">
                    <img src="https://img.icons8.com/?size=100&id=TDEKFc4RXwN_&format=png&color=000000" alt="Funcionários">
                    <h3>Gerir Funcionários</h3>
                </a>
                <a class="card-opcao" href="reservas.php">
                    <img src="https://img.icons8.com/?size=100&id=MCnPOwFJpCvG&format=png&color=000000" alt="Reservas">
                    <h3>Gerir Reservas</h3>
                </a>
                <a class="card-opcao" href="servicos.php">
                    <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Serviços">
                    <h3>Gerir Serviços</h3>
                </a>
                <a class="card-opcao" href="despesas.php">
                    <img src="https://img.icons8.com/?size=100&id=22462&format=png&color=000000" alt="Despesas">
                    <h3>Gerir Despesas</h3>
                </a>
                <a class="card-opcao" href="manutencao.php">
                    <img src="https://img.icons8.com/?size=100&id=11151&format=png&color=000000" alt="Manutenções">
                    <h3>Gerir Manutenções</h3>
                </a>
                <a class="card-opcao" href="receitas.php">
                    <img src="https://img.icons8.com/?size=100&id=p2scHNLP9nSb&format=png&color=000000" alt="Receitas">
                    <h3>Gerir Receitas</h3>
                </a>
                    <a class="card-opcao" href="newsletter.php">
                    <img src="https://img.icons8.com/?size=100&id=bqI4gOgp4z1f&format=png&color=000000" alt="Newsletter">
                    <h3>Enviar Emails</h3>
                </a>
                    <a class="card-opcao" href="modelos_newsletter.php">
                    <img src="https://img.icons8.com/?size=100&id=MmZOzSKjFP8X&format=png&color=000000" alt="Modelos de Newsletter">
                    <h3>Modelos de Emails</h3>
                </a>
                    <a class="card-opcao" href="listar_avaliacoes.php">
                    <img src="https://img.icons8.com/?size=100&id=8ggStxqyboK5&format=png&color=000000" alt="Listar Avaliações">
                    <h3>Avaliações dos hóspedes</h3>
                </a>
                                    <a class="card-opcao" href="atualizar_index.php">
                    <img src="https://img.icons8.com/?size=100&id=8ggStxqyboK5&format=png&color=000000" alt="Listar Avaliações">
                    <h3>Atualização do index</h3>
                </a>
            </div>
        </div>
        <!-- Container de Notificações -->
        <div class="notificacoes-container" id="notificacoesContainer">
            <div class="notificacoes-header">
                <h3>Notificações</h3>
                <span class="fechar" onclick="fecharNotificacoes()">×</span>
            </div>
            <div class="notificacoes-body">
                <?php if ($resultado_notificacoes->num_rows > 0) : ?>
                    <?php while ($notificacao = $resultado_notificacoes->fetch_assoc()) : 
                        $data_criacao = new DateTime($notificacao['data_criacao']);
                        $agora = new DateTime();
                        $diferenca = $data_criacao->diff($agora);

                        if ($diferenca->days > 0) {
                            $tempo = $diferenca->days . ' dias atrás';
                        } elseif ($diferenca->h > 0) {
                            $tempo = $diferenca->h . ' horas atrás';
                        } elseif ($diferenca->i > 0) {
                            $tempo = $diferenca->i . ' minutos atrás';
                        } else {
                            $tempo = 'Agora mesmo';
                        }
                    ?>
                        <div class="notificacao-item" id="notificacao-<?= $notificacao['id'] ?>">
                            <div class="notificacao-icone">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                                </svg>
                            </div>
                            <div class="notificacao-conteudo">
                                <div class="notificacao-mensagem"><?= htmlspecialchars($notificacao['mensagem']) ?></div>
                                <div class="notificacao-meta">
                                    <span class="notificacao-tempo"><?= $tempo ?></span>
                                    <div class="notificacao-acoes">
                                        <button class="notificacao-btn" onclick="marcarComoLida(<?= $notificacao['id'] ?>, event)">Marcar como lida</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sem-notificacoes">Não há novas notificações</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Flatpickr JS -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            // Funções para Notificações
            function mostrarNotificacoes() {
                const container = document.getElementById('notificacoesContainer');
                container.style.display = container.style.display === 'block' ? 'none' : 'block';
            }

            function fecharNotificacoes() {
                document.getElementById('notificacoesContainer').style.display = 'none';
            }
    function marcarComoLida(idNotificacao, event) {
        event.stopPropagation();   
        fetch('marcar_como_lida.php?id=' + idNotificacao)
            .then(response => response.text())
            .then(data => {
                if (data === 'sucesso') {
                    const notificacao = document.getElementById('notificacao-' + idNotificacao);
                    notificacao.style.opacity = '0.6';
                    notificacao.style.transition = 'opacity 0.3s ease';
                    // Remover após a animação
                    setTimeout(() => {
                        notificacao.remove();
                        // Atualizar contador
                        const contador = document.querySelector('.contador-notificacoes');
                        if (contador) {
                            let novoValor = parseInt(contador.textContent) - 1;
                            if (novoValor <= 0) {
                                contador.style.display = 'none';
                                // Esconder o container se não houver mais notificações
                                if (document.querySelectorAll('.notificacao-item').length === 0) {
                                    document.getElementById('notificacoesContainer').style.display = 'none';
                                }
                            } else {
                                contador.textContent = novoValor;
                            }
                        }
                    }, 300);
                }
            })
            .catch(error => console.error('Erro:', error));
    }
            // Fechar notificações ao clicar fora
            document.addEventListener('click', function(event) {
                const notificacoes = document.getElementById('notificacoesContainer');
                const iconeNotificacao = document.querySelector('.icone-notificacao');
                
                if (notificacoes.style.display === 'block' && 
                    !notificacoes.contains(event.target) && 
                    !iconeNotificacao.contains(event.target)) {
                    notificacoes.style.display = 'none';
                }
            });

        // Busca Global
        const input = document.getElementById('globalSearchInput');
        const resultsBox = document.getElementById('globalSearchResults');
        let timeout = null;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const q = this.value.trim();
            if (q.length < 2) {
                resultsBox.classList.remove('active');
                resultsBox.innerHTML = '';
                return;
            }
            timeout = setTimeout(() => {
                fetch('busca_global.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        let html = '';
                        let hasResults = false;
                        function group(title, arr, type) {
                            if (!arr.length) return '';
                            hasResults = true;
                            let items = arr.map(item => {
                                let label = '';
                                let url = '#';
                                if (type === 'hospedes') {
                                    label = `<b>${item.nome}</b> <small>(${item.email})</small>`;
                                    url = `editar_hospede.php?id=${item.id}`;
                                } else if (type === 'reservas') {
                                    label = `<b>Reserva #${item.id}</b> <small>${item.nome_hospede} - ${item.nome_casa} (${item.data_entrada} a ${item.data_saida})</small>`;
                                    url = `editar_reserva.php?id=${item.id}`;
                                } else if (type === 'casas') {
                                    label = `<b>${item.nome}</b> <small>(Casa #${item.id}, Capacidade: ${item.C_capacidade}, Estado: ${item.C_estado})</small>`;
                                    url = `editar_casa.php?id=${item.id}`;
                                } else if (type === 'funcionarios') {
                                    label = `<b>${item.nome}</b> <small>(${item.email})</small>`;
                                    url = `editar_funcionario.php?id=${item.id}`;
                                }
                                return `<div class='global-search-item' data-url='${url}'>${label}</div>`;
                            }).join('');
                            return `<div class='global-search-group'><div class='global-search-title'>${title}</div>${items}</div>`;
                        }
                        html += group('Hóspedes', data.hospedes, 'hospedes');
                        html += group('Reservas', data.reservas, 'reservas');
                        html += group('Casas', data.casas, 'casas');
                        html += group('Funcionários', data.funcionarios, 'funcionarios');
                        if (!hasResults) html = `<div class='global-search-empty'>Nenhum resultado encontrado.</div>`;
                        resultsBox.innerHTML = html;
                        resultsBox.classList.add('active');
                    });
            }, 250);
        });
        // Clique no resultado
        resultsBox.addEventListener('mousedown', function(e) {
            const el = e.target.closest('.global-search-item');
            if (el && el.dataset.url) {
                window.location.href = el.dataset.url;
            }
        });
        // Fechar resultados ao clicar fora
        document.addEventListener('mousedown', function(e) {
            if (!resultsBox.contains(e.target) && e.target !== input) {
                resultsBox.classList.remove('active');
            }
        });
        function abrirModalReserva(e) {
            e.preventDefault();
            document.getElementById('modalReserva').style.display = 'flex';
        }
        function fecharModalReserva() {
            document.getElementById('modalReserva').style.display = 'none';
            document.getElementById('formNovaReservaModal').reset();
            document.getElementById('reservaMsgModal').innerHTML = '';
        }
        document.getElementById('formNovaReservaModal').onsubmit = async function(ev) {
            ev.preventDefault();
            const form = ev.target;
            const dados = new FormData(form);
            document.getElementById('reservaMsgModal').innerHTML = 'Salvando...';
            const resp = await fetch('processar_reserva.php', { method: 'POST', body: dados });
            if (resp.redirected) {
                window.location.href = resp.url;
            } else {
                const txt = await resp.text();
                document.getElementById('reservaMsgModal').innerHTML = txt;
            }
        }
        // Wizard navegação
        function wizardProximo(etapa) {
            if (etapa === 1) {
                // Validação simples dos campos obrigatórios da etapa 1
                if (!document.getElementById('id_casa_modal').value || !document.getElementById('data_checkin_modal').value || !document.getElementById('data_checkout_modal').value || !document.getElementById('id_hospede_modal').value || !document.getElementById('num_hospedes_modal').value) {
                    alert('Preencha todos os campos obrigatórios.');
                    return;
                }
            }
            document.getElementById('step'+etapa).style.display = 'none';
            document.getElementById('step'+(etapa+1)).style.display = 'block';
        }
        function wizardAnterior(etapa) {
            document.getElementById('step'+etapa).style.display = 'none';
            document.getElementById('step'+(etapa-1)).style.display = 'block';
        }
        // --- FLATPICKR E DATAS OCUPADAS ---
        let datasOcupadas = [];
        let checkinPicker, checkoutPicker;

        function atualizarDatasOcupadas() {
            const idCasa = document.getElementById('id_casa_modal').value;
            if (!idCasa) {
                if (checkinPicker) checkinPicker.set('disable', []);
                if (checkoutPicker) checkoutPicker.set('disable', []);
                return;
            }
            fetch('datas_ocupadas.php?id_casa=' + idCasa)
                .then(r => r.json())
                .then(datas => {
                    datasOcupadas = datas;
                    if (checkinPicker) checkinPicker.set('disable', datasOcupadas);
                    if (checkoutPicker) checkoutPicker.set('disable', datasOcupadas);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkinPicker = flatpickr("#data_checkin_modal", {
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: datasOcupadas,
                onOpen: atualizarDatasOcupadas,
                onChange: function(selectedDates, dateStr, instance) {
                    if (checkoutPicker) checkoutPicker.set('minDate', dateStr);
                }
            });
            checkoutPicker = flatpickr("#data_checkout_modal", {
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: datasOcupadas,
                onOpen: atualizarDatasOcupadas
            });
            document.getElementById('id_casa_modal').addEventListener('change', function() {
                atualizarDatasOcupadas();
                if (checkinPicker) checkinPicker.clear();
                if (checkoutPicker) checkoutPicker.clear();
            });
        });
        // --- MELHORIA: Detalhes automáticos das ofertas especiais e lógica de bloqueio de serviços ---
        const ofertas = {
            "LOVE260": {
                titulo: "Oferta Romântica LOVE260",
                descricao: "Desconto especial para casais apaixonados.",
                condicoes: "Válido para reservas de 2 noites ou mais.",
                preco: 260,
                noites: 2,
                hospedes: 2
            },
            "PARTY260": {
                titulo: "Oferta Festa PARTY260",
                descricao: "Ideal para grupos e celebrações.",
                condicoes: "Inclui decoração temática gratuita.",
                preco: 260,
                noites: 2,
                hospedes: 4
            },
            "RETIRO240": {
                titulo: "Oferta Retiro RETIRO240",
                descricao: "Desconto para estadias tranquilas.",
                condicoes: "Válido apenas durante a semana.",
                preco: 240,
                noites: 4,
                hospedes: 10
            }
        };
        const selectOferta = document.getElementById('codigo_oferta_modal');
        const detalhesOferta = document.getElementById('detalhes-oferta-modal');
        const step2 = document.getElementById('step2');
        const decoracaoSelect = document.getElementById('decoracao_tematica_modal');
        let inputOutro = null;
        // Serviços adicionais
        const servicos = [
            { id: 'decoracao_tematica_modal', preco: 130 },
            { id: 'limpeza_diaria_modal', preco: 15, porNoite: true },
            { id: 'cesto_boas_vindas_modal', preco: 10 }
        ];
        // Bloquear serviços adicionais se oferta selecionada
        selectOferta.addEventListener('change', function() {
            const val = this.value;
            if (ofertas[val]) {
                document.getElementById('titulo-oferta-modal').textContent = ofertas[val].titulo;
                document.getElementById('descricao-oferta-modal').textContent = ofertas[val].descricao;
                document.getElementById('condicoes-oferta-modal').textContent = ofertas[val].condicoes;
                detalhesOferta.style.display = 'block';
                // Bloquear etapa 2 (serviços adicionais)
                if (step2) {
                    Array.from(step2.querySelectorAll('input,select')).forEach(el => {
                        el.disabled = true;
                        if (el.type === 'checkbox' || el.tagName === 'SELECT') el.checked = false;
                        if (el.tagName === 'SELECT') el.value = '';
                    });
                    step2.style.opacity = 0.5;
                }
            } else {
                detalhesOferta.style.display = 'none';
                // Desbloquear etapa 2
                if (step2) {
                    Array.from(step2.querySelectorAll('input,select')).forEach(el => {
                        el.disabled = false;
                    });
                    step2.style.opacity = 1;
                }
            }
            calcularTotal();
        });
        // Campo personalizado para "Outro" em Decoração Temática
        if (decoracaoSelect) {
            decoracaoSelect.addEventListener('change', function() {
                if (this.value === 'Outro') {
                    if (!inputOutro) {
                        inputOutro = document.createElement('input');
                        inputOutro.type = 'text';
                        inputOutro.name = 'decoracao_tematica_outro';
                        inputOutro.placeholder = 'Descreva o tema';
                        inputOutro.className = 'form-control';
                        inputOutro.style.marginTop = '8px';
                        this.parentNode.appendChild(inputOutro);
                    }
                    inputOutro.style.display = 'block';
                } else if (inputOutro) {
                    inputOutro.style.display = 'none';
                }
            });
        }
        // --- Cálculo do total ---
        function calcularTotal() {
            const cod = selectOferta.value;
            // Se oferta válida, mostrar valor fixo do pacote
            if (ofertas[cod]) {
                document.getElementById('preco_noite_modal').textContent = '--';
                document.getElementById('noites_modal').textContent = ofertas[cod].noites;
                document.getElementById('preco_servicos_modal').textContent = '--';
                document.getElementById('desconto_oferta_modal').textContent = '--';
                document.getElementById('preco_total_modal').textContent = ofertas[cod].preco.toFixed(2);
                return;
            }
            // Caso normal (sem oferta)
            const casaSel = document.getElementById('id_casa_modal');
            const precoNoite = casaSel && casaSel.selectedOptions[0] ? parseFloat(casaSel.selectedOptions[0].getAttribute('data-preco')) : 0;
            const checkin = document.getElementById('data_checkin_modal').value;
            const checkout = document.getElementById('data_checkout_modal').value;
            let noites = 0;
            if (checkin && checkout) {
                const d1 = new Date(checkin);
                const d2 = new Date(checkout);
                noites = Math.max(0, Math.round((d2-d1)/(1000*60*60*24)));
            }
            document.getElementById('preco_noite_modal').textContent = precoNoite.toFixed(2);
            document.getElementById('noites_modal').textContent = noites;
            // Serviços adicionais
            let precoServicos = 0;
            if (step2 && step2.style.opacity != '0.5') {
                servicos.forEach(s => {
                    const el = document.getElementById(s.id);
                    if (el) {
                        if (el.type === 'checkbox' && el.checked) {
                            precoServicos += s.porNoite ? (s.preco * noites) : s.preco;
                        } else if (el.tagName === 'SELECT' && el.value && s.id === 'decoracao_tematica_modal') {
                            precoServicos += s.preco;
                        }
                    }
                });
            }
            document.getElementById('preco_servicos_modal').textContent = precoServicos.toFixed(2);
            document.getElementById('desconto_oferta_modal').textContent = '--';
            let total = (precoNoite * noites) + precoServicos;
            if (total < 0) total = 0;
            document.getElementById('preco_total_modal').textContent = total.toFixed(2);
        }
        // Atualizar total ao mudar campos relevantes
        document.getElementById('id_casa_modal').addEventListener('change', calcularTotal);
        document.getElementById('data_checkin_modal').addEventListener('change', calcularTotal);
        document.getElementById('data_checkout_modal').addEventListener('change', calcularTotal);
        if (decoracaoSelect) decoracaoSelect.addEventListener('change', calcularTotal);
        ['limpeza_diaria_modal','cesto_boas_vindas_modal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', calcularTotal);
        });
        selectOferta.addEventListener('change', calcularTotal);
        // Inicializar total ao abrir modal
        document.getElementById('modalReserva').addEventListener('show', calcularTotal);
        // Também ao avançar etapas
        window.wizardProximo = function(etapa) {
            if (etapa === 1) {
                if (!document.getElementById('id_casa_modal').value || !document.getElementById('data_checkin_modal').value || !document.getElementById('data_checkout_modal').value || !document.getElementById('id_hospede_modal').value || !document.getElementById('num_hospedes_modal').value) {
                    alert('Preencha todos os campos obrigatórios.');
                    return;
                }
            }
            document.getElementById('step'+etapa).style.display = 'none';
            document.getElementById('step'+(etapa+1)).style.display = 'block';
            calcularTotal();
        }
        // Atualizar total ao abrir modal
        document.getElementById('modalReserva').addEventListener('click', function(e) {
            if (e.target === this) calcularTotal();
        });
        // --- BLOQUEIO DE DATAS CHECKIN/CHECKOUT CONFORME OFERTA ---
        function bloquearDatasOferta() {
            const cod = selectOferta.value;
            if (ofertas[cod]) {
                // Ao selecionar check-in, checkout é automaticamente definido e bloqueado
                if (checkinPicker && checkoutPicker) {
                    checkinPicker.config.onChange = [function(selectedDates) {
                        if (selectedDates.length > 0) {
                            const checkinDate = selectedDates[0];
                            const checkoutDate = new Date(checkinDate);
                            checkoutDate.setDate(checkoutDate.getDate() + ofertas[cod].noites);
                            checkoutPicker.setDate(checkoutDate);
                            checkoutPicker.set('minDate', checkoutDate);
                            checkoutPicker.set('maxDate', checkoutDate);
                            document.getElementById('data_checkout_modal').readOnly = true;
                            document.getElementById('data_checkin_modal').readOnly = false;
                        }
                    }];
                    // Se já houver check-in selecionado
                    if (checkinPicker.selectedDates.length > 0) {
                        const checkinDate = checkinPicker.selectedDates[0];
                        const checkoutDate = new Date(checkinDate);
                        checkoutDate.setDate(checkoutDate.getDate() + ofertas[cod].noites);
                        checkoutPicker.setDate(checkoutDate);
                        checkoutPicker.set('minDate', checkoutDate);
                        checkoutPicker.set('maxDate', checkoutDate);
                        document.getElementById('data_checkout_modal').readOnly = true;
                        document.getElementById('data_checkin_modal').readOnly = false;
                    }
                }
            } else {
                // Restaurar comportamento normal
                if (checkinPicker && checkoutPicker) {
                    checkinPicker.config.onChange = [function(selectedDates) {
                        if (selectedDates.length > 0) {
                            const checkinDate = selectedDates[0];
                            checkoutPicker.set('minDate', new Date(checkinDate.getTime() + 86400000));
                            checkoutPicker.set('maxDate', null);
                            document.getElementById('data_checkout_modal').readOnly = false;
                        }
                    }];
                    checkoutPicker.set('minDate', null);
                    checkoutPicker.set('maxDate', null);
                    document.getElementById('data_checkout_modal').readOnly = false;
                }
            }
        }
        // Integrar com o evento de mudança da oferta
        selectOferta.addEventListener('change', function() {
            bloquearDatasOferta();
        });
        // Integrar ao abrir modal
        document.addEventListener('DOMContentLoaded', function() {
            bloquearDatasOferta();
        });
        // --- Novo Hóspede (modal etapa única, PT-PT, flash message) ---
        function mostrarFlashMessage(msg) {
            const flash = document.getElementById('flashMessage');
            flash.textContent = msg;
            flash.style.display = 'block';
            flash.style.opacity = 1;
            setTimeout(() => {
                flash.style.opacity = 0;
                setTimeout(() => flash.style.display = 'none', 400);
            }, 3000);
        }
        document.getElementById('btnSalvarHospede').onclick = async function(ev) {
            ev.preventDefault();
            const nome = document.getElementById('nome_hospede_modal').value.trim();
            const email = document.getElementById('email_hospede_modal').value.trim();
            const pais = document.getElementById('pais_codigo_hospede_modal').value;
            const telefone = document.getElementById('telefone_hospede_modal').value.trim();
            const documento = document.getElementById('documento_hospede_modal').value.trim();
            const morada = document.getElementById('morada_hospede_modal').value.trim();
            const password = document.getElementById('password_hospede_modal').value;
            const aceitou = document.getElementById('aceitou_hospede_modal').checked ? 'Sim' : '';
            if (!nome || !email || !pais || !telefone || !documento || !password || !aceitou) {
                document.getElementById('hospedeMsgModal').innerHTML = 'Preencha todos os campos obrigatórios.';
                return;
            }
            document.getElementById('hospedeMsgModal').innerHTML = 'A guardar...';
            const formData = new FormData();
            formData.append('nome', nome);
            formData.append('email', email);
            formData.append('pais_codigo', pais);
            formData.append('telefone', telefone);
            formData.append('documento', documento);
            formData.append('morada', morada);
            formData.append('password', password);
            formData.append('aceitou', aceitou);
            formData.append('novo_hospede', '1');
            const resp = await fetch('adicionar_hospede.php', { method: 'POST', body: formData });
            const txt = await resp.text();
            if (txt.toLowerCase().includes('sucesso')) {
                fecharModalHospede();
                mostrarFlashMessage('Hóspede criado com sucesso!');
            } else {
                document.getElementById('hospedeMsgModal').innerHTML = txt.replace('sucesso', 'sucesso').replace('cadastrado', 'criado').replace('telefone', 'telemóvel').replace('senha', 'palavra-passe').replace('preencha', 'preencha').replace('erro', 'erro');
            }
        };
        document.getElementById('formNovoHospedeModal').onsubmit = function(ev) {
            ev.preventDefault();
            document.getElementById('btnSalvarHospede').click();
        };
        function abrirModalHospede(e) {
            e.preventDefault();
            document.getElementById('modalHospede').style.display = 'flex';
            document.getElementById('formNovoHospedeModal').reset();
            document.getElementById('hospedeMsgModal').innerHTML = '';
        }
        function fecharModalHospede() {
            document.getElementById('modalHospede').style.display = 'none';
            document.getElementById('formNovoHospedeModal').reset();
            document.getElementById('hospedeMsgModal').innerHTML = '';
        }
        // Máscara dinâmica de telemóvel
        function aplicarMascaraTelefoneModal(input, pais) {
            let v = input.value.replace(/\D/g, '');
            if (pais === '+351' || pais === '+34' || pais === '+33') {
                v = v.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
            } else if (pais === '+1') {
                v = v.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
            input.value = v.trim();
        }
        const telefoneInputModal = document.getElementById('telefone_hospede_modal');
        const paisInputModal = document.getElementById('pais_codigo_hospede_modal');
        if (telefoneInputModal && paisInputModal) {
            telefoneInputModal.addEventListener('input', function() {
                aplicarMascaraTelefoneModal(this, paisInputModal.value);
            });
            paisInputModal.addEventListener('change', function() {
                aplicarMascaraTelefoneModal(telefoneInputModal, this.value);
            });
        }
        </script>
    </body>
</html>