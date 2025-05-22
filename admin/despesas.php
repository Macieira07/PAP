<?php
// Incluir arquivo de conexão
require_once '../conexao.php';

// Inicializar a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para obter o saldo atual
function obterSaldoAtual() {
    global $conexao;
    
    // Obter total de receitas
    $sql_receitas = "SELECT COALESCE(SUM(m.valor), 0) as total 
                     FROM movimentacoes m 
                     WHERE m.tipo = 'receita'";
    $resultado_receitas = $conexao->query($sql_receitas);
    $total_receitas = $resultado_receitas->fetch_assoc()['total'];
    
    // Obter total de despesas
    $sql_despesas = "SELECT COALESCE(SUM(m.valor), 0) as total 
                    FROM movimentacoes m 
                    WHERE m.tipo = 'despesa'";
    $resultado_despesas = $conexao->query($sql_despesas);
    $total_despesas = $resultado_despesas->fetch_assoc()['total'];
    
    // Calcular saldo (receitas - despesas)
    $saldo = $total_receitas - $total_despesas;
    
    return $saldo;
}

// Função para registrar uma nova movimentação
function registrarMovimentacao($tipo, $descricao, $valor, $origem, $origem_id = null) {
    global $conexao;
    
    $stmt = $conexao->prepare("INSERT INTO movimentacoes (tipo, descricao, valor, origem, origem_id) 
                               VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsi", $tipo, $descricao, $valor, $origem, $origem_id);
    
    $resultado = $stmt->execute();
    $stmt->close();
    
    return $resultado;
}

// Função para marcar um serviço como pago
function pagarServico($id_servico) {
    global $conexao;
    
    // Iniciar transação
    $conexao->begin_transaction();
    
    try {
        // Obter dados do serviço
        $stmt = $conexao->prepare("SELECT S_preco, S_nome FROM servicos WHERE S_id_servico = ?");
        $stmt->bind_param("i", $id_servico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $servico = $resultado->fetch_assoc();
        $stmt->close();
        
        // Verificar saldo disponível
        $saldo_atual = obterSaldoAtual();
        if ($saldo_atual < $servico['S_preco']) {
            throw new Exception("Saldo insuficiente para pagar este serviço. Saldo atual: €" . number_format($saldo_atual, 2, ',', '.'));
        }
        
        // Registrar movimentação
        $descricao = "Pagamento de serviço: " . $servico['S_nome'];
        registrarMovimentacao('despesa', $descricao, $servico['S_preco'], 'servico', $id_servico);
        
        // Confirmar transação
        $conexao->commit();
        return true;
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conexao->rollback();
        throw $e;
    }
}

// Função para marcar uma manutenção como paga
function pagarManutencao($id_manutencao) {
    global $conexao;
    
    // Iniciar transação
    $conexao->begin_transaction();
    
    try {
        // Obter dados da manutenção
        $stmt = $conexao->prepare("SELECT M_custo, M_pago, M_tipo FROM manutencao WHERE M_id_manutencao = ?");
        $stmt->bind_param("i", $id_manutencao);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $manutencao = $resultado->fetch_assoc();
        $stmt->close();
        
        // Verificar se a manutenção já foi paga
        if ($manutencao['M_pago'] == 1) {
            throw new Exception("Esta manutenção já foi paga.");
        }
        
        // Verificar saldo disponível
        $saldo_atual = obterSaldoAtual();
        if ($saldo_atual < $manutencao['M_custo']) {
            throw new Exception("Saldo insuficiente para pagar esta manutenção. Saldo atual: €" . number_format($saldo_atual, 2, ',', '.'));
        }
        
        // Atualizar status da manutenção para paga
        $stmt = $conexao->prepare("UPDATE manutencao SET M_pago = 1 WHERE M_id_manutencao = ?");
        $stmt->bind_param("i", $id_manutencao);
        $stmt->execute();
        $stmt->close();
        
        // Registrar movimentação
        $descricao = "Pagamento de manutenção #" . $id_manutencao . ": " . $manutencao['M_tipo'];
        registrarMovimentacao('despesa', $descricao, $manutencao['M_custo'], 'manutencao', $id_manutencao);
        
        // Confirmar transação
        $conexao->commit();
        return true;
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conexao->rollback();
        throw $e;
    }
}

// Função para registrar recebimento de reserva
function registrarRecebimentoReserva($id_reserva) {
    global $conexao;
    
    // Iniciar transação
    $conexao->begin_transaction();
    
    try {
        // Obter dados da reserva
        $stmt = $conexao->prepare("SELECT R_preco_total, R_valor_pago FROM reservas WHERE R_id_reserva = ?");
        $stmt->bind_param("i", $id_reserva);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $reserva = $resultado->fetch_assoc();
        $stmt->close();
        
        // Calcular valor a receber
        $valor_a_receber = $reserva['R_preco_total'] - $reserva['R_valor_pago'];
        
        if ($valor_a_receber <= 0) {
            throw new Exception("Esta reserva já está totalmente paga.");
        }
        
        // Atualizar valor pago da reserva
        $novo_valor_pago = $reserva['R_preco_total'];
        $stmt = $conexao->prepare("UPDATE reservas SET R_valor_pago = ?, R_comprovativo_entregue = 1 WHERE R_id_reserva = ?");
        $stmt->bind_param("di", $novo_valor_pago, $id_reserva);
        $stmt->execute();
        $stmt->close();
        
        // Registrar movimentação
        $descricao = "Recebimento da reserva #" . $id_reserva;
        registrarMovimentacao('receita', $descricao, $valor_a_receber, 'reserva', $id_reserva);
        
        // Confirmar transação
        $conexao->commit();
        return true;
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conexao->rollback();
        throw $e;
    }
}

// Função para registrar uma nova receita
function registrarReceita($descricao, $valor, $data, $tipo, $observacoes, $metodo_pagamento) {
    global $conexao;
    
    // Iniciar transação
    $conexao->begin_transaction();
    
    try {
        // Inserir na tabela de receitas
        $stmt = $conexao->prepare("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_observacoes, R_metodo_pagamento) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssss", $descricao, $valor, $data, $tipo, $observacoes, $metodo_pagamento);
        $stmt->execute();
        $id_receita = $conexao->insert_id;
        $stmt->close();
        
        // Registrar movimentação
        registrarMovimentacao('receita', $descricao, $valor, 'receita', $id_receita);
        
        // Confirmar transação
        $conexao->commit();
        return true;
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conexao->rollback();
        throw $e;
    }
}

// Função para registrar um novo serviço
function registrarServico($nome, $descricao, $preco) {
    global $conexao;
    
    $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco) 
                              VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $nome, $descricao, $preco);
    
    $resultado = $stmt->execute();
    $stmt->close();
    
    return $resultado;
}

// Função para registrar nova manutenção
function registrarManutencao($id_casa, $tipo, $descricao, $data_inicio, $data_fim, $custo) {
    global $conexao;
    
    $stmt = $conexao->prepare("INSERT INTO manutencao 
        (M_tipo, M_data_inicio, M_data_fim, M_descricao, M_custo, M_id_casa, M_pago) 
        VALUES (?, ?, ?, ?, ?, ?, 0)");
    
    $stmt->bind_param("ssssdi", $tipo, $data_inicio, $data_fim, $descricao, $custo, $id_casa);
    
    $resultado = $stmt->execute();
    $stmt->close();
    
    return $resultado;
}

// Processar formulários
$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar o pagamento de serviço
    if (isset($_POST['pagar_servico'])) {
        try {
            $id_servico = $_POST['id_servico'];
            if (pagarServico($id_servico)) {
                $mensagem = "Serviço pago com sucesso!";
                $tipo_mensagem = "success";
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao pagar serviço: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar o pagamento de manutenção
    if (isset($_POST['pagar_manutencao'])) {
        try {
            $id_manutencao = $_POST['id_manutencao'];
            if (pagarManutencao($id_manutencao)) {
                $mensagem = "Manutenção paga com sucesso!";
                $tipo_mensagem = "success";
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao pagar manutenção: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar recebimento de reserva
    if (isset($_POST['receber_reserva'])) {
        try {
            $id_reserva = $_POST['id_reserva'];
            if (registrarRecebimentoReserva($id_reserva)) {
                $mensagem = "Pagamento da reserva registrado com sucesso!";
                $tipo_mensagem = "success";
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao registrar pagamento da reserva: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar nova receita
    if (isset($_POST['nova_receita'])) {
        try {
            $descricao = $_POST['descricao'];
            $valor = $_POST['valor'];
            $data = $_POST['data'];
            $tipo = $_POST['tipo'];
            $observacoes = $_POST['observacoes'];
            $metodo_pagamento = $_POST['metodo_pagamento'];
            
            if (registrarReceita($descricao, $valor, $data, $tipo, $observacoes, $metodo_pagamento)) {
                $mensagem = "Receita registrada com sucesso!";
                $tipo_mensagem = "success";
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao registrar receita: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar novo serviço
    if (isset($_POST['novo_servico'])) {
        try {
            $nome = $_POST['nome'];
            $descricao = $_POST['descricao'];
            $preco = $_POST['preco'];
            
            if (registrarServico($nome, $descricao, $preco)) {
                $mensagem = "Serviço registrado com sucesso!";
                $tipo_mensagem = "success";
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao registrar serviço: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Processar nova manutenção
    if (isset($_POST['nova_manutencao'])) {
        try {
            $id_casa = $_POST['casa'];
            $tipo = $_POST['tipo'];
            $descricao = $_POST['descricao'];
            $data_inicio = $_POST['data_inicio'];
            $data_fim = $_POST['data_fim'];
            $custo = $_POST['custo'];
            
            if (registrarManutencao($id_casa, $tipo, $descricao, $data_inicio, $data_fim, $custo)) {
                $mensagem = "Manutenção registrada com sucesso!";
                $tipo_mensagem = "success";
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao registrar manutenção: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Obter dados para exibição
$saldo_atual = obterSaldoAtual();

// Obter todos os serviços
$sql_servicos = "SELECT * FROM servicos ORDER BY S_nome ASC";
$resultado_servicos = $conexao->query($sql_servicos);
$servicos = $resultado_servicos->fetch_all(MYSQLI_ASSOC);

// Obter todas as manutenções
$sql_manutencoes = "SELECT m.*, c.C_nome as nome_casa 
                   FROM manutencao m
                   LEFT JOIN casas c ON m.M_id_casa = c.C_id_casa
                   ORDER BY m.M_data_inicio DESC";
$resultado_manutencoes = $conexao->query($sql_manutencoes);
$manutencoes = $resultado_manutencoes->fetch_all(MYSQLI_ASSOC);

// Obter reservas pendentes de pagamento
$sql_reservas = "SELECT r.*, c.C_nome as nome_casa, h.H_nome as nome_hospede
                FROM reservas r
                LEFT JOIN casas c ON r.R_id_casa = c.C_id_casa
                LEFT JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
                WHERE r.R_valor_pago < r.R_preco_total
                ORDER BY r.R_data_checkin DESC";
$resultado_reservas = $conexao->query($sql_reservas);
$reservas = $resultado_reservas->fetch_all(MYSQLI_ASSOC);

// Obter movimentações recentes
$sql_movimentacoes = "SELECT * FROM movimentacoes ORDER BY data DESC LIMIT 10";
$resultado_movimentacoes = $conexao->query($sql_movimentacoes);
$movimentacoes = $resultado_movimentacoes->fetch_all(MYSQLI_ASSOC);

// Calcular resumo financeiro
$sql_resumo = "SELECT 
                SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as total_receitas,
                SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as total_despesas,
                SUM(CASE WHEN origem = 'reserva' AND tipo = 'receita' THEN valor ELSE 0 END) as receitas_reservas,
                SUM(CASE WHEN origem = 'receita' AND tipo = 'receita' THEN valor ELSE 0 END) as receitas_extras,
                SUM(CASE WHEN origem = 'manutencao' AND tipo = 'despesa' THEN valor ELSE 0 END) as despesas_manutencao,
                SUM(CASE WHEN origem = 'servico' AND tipo = 'despesa' THEN valor ELSE 0 END) as despesas_servicos,
                SUM(CASE WHEN origem = 'despesa' AND tipo = 'despesa' THEN valor ELSE 0 END) as despesas_gerais
               FROM movimentacoes";
$resultado_resumo = $conexao->query($sql_resumo);
$resumo = $resultado_resumo->fetch_assoc();

// Obter dados para o gráfico (últimos 6 meses)
$meses_labels = [];
$dados_receitas = [];
$dados_despesas = [];

for ($i = 5; $i >= 0; $i--) {
    $mes = date('m', strtotime("-$i month"));
    $ano = date('Y', strtotime("-$i month"));
    $nome_mes = date('M', strtotime("-$i month"));
    
    $meses_labels[] = $nome_mes;
    
    // Receitas do mês
    $sql_receitas_mes = "SELECT COALESCE(SUM(valor), 0) as total FROM movimentacoes 
                        WHERE tipo = 'receita' AND MONTH(data) = $mes AND YEAR(data) = $ano";
    $res_receitas_mes = $conexao->query($sql_receitas_mes);
    $dados_receitas[] = $res_receitas_mes->fetch_assoc()['total'];
    
    // Despesas do mês
    $sql_despesas_mes = "SELECT COALESCE(SUM(valor), 0) as total FROM movimentacoes 
                        WHERE tipo = 'despesa' AND MONTH(data) = $mes AND YEAR(data) = $ano";
    $res_despesas_mes = $conexao->query($sql_despesas_mes);
    $dados_despesas[] = $res_despesas_mes->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão Financeira - Quinta Flores</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        <?php include 'despesas.css'; ?>
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Quinta Flores</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-chart-line me-2"></i>
                                Dashboard Financeiro
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservas.php">
                                <i class="fas fa-calendar-check me-2"></i>
                                Reservas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manutencao.php">
                                <i class="fas fa-tools me-2"></i>
                                Manutenções
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="servicos.php">
                                <i class="fas fa-concierge-bell me-2"></i>
                                Serviços
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="receitas.php">
                                <i class="fas fa-euro-sign me-2"></i>
                                Receitas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="fas fa-house-user me-2"></i>
                                Página Inicial
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-heading mt-4 text-white">
                        Relatórios
                    </div>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-fw fa-chart-line me-2"></i>
                                Financeiro Mensal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-fw fa-chart-pie me-2"></i>
                                Análise por Categoria
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-fw fa-file-excel me-2"></i>
                                Exportar Dados
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestão Financeira</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar me-1"></i> <?php echo date('F Y'); ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> Exportar
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="window.location.reload();">
                            <i class="fas fa-sync-alt me-1"></i> Atualizar
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                        <i class="fas <?php echo $tipo_mensagem == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Cards de Resumo -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-dashboard card-dashboard-primary h-100">
                            <div class="card-body">
                                <div class="indicator">
                                    <div class="indicator-icon indicator-primary">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="indicator-text">
                                        <div class="indicator-title">Saldo Atual</div>
                                        <div class="indicator-value <?php echo $saldo_atual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">
                                            €<?php echo number_format($saldo_atual, 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-dashboard card-dashboard-success h-100">
                            <div class="card-body">
                                <div class="indicator">
                                    <div class="indicator-icon indicator-success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="indicator-text">
                                        <div class="indicator-title">Total Receitas</div>
                                        <div class="indicator-value saldo-positivo">
                                            €<?php echo number_format($resumo['total_receitas'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-dashboard card-dashboard-danger h-100">
                            <div class="card-body">
                                <div class="indicator">
                                    <div class="indicator-icon indicator-danger">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    <div class="indicator-text">
                                        <div class="indicator-title">Total Despesas</div>
                                        <div class="indicator-value saldo-negativo">
                                            €<?php echo number_format($resumo['total_despesas'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-dashboard card-dashboard-warning h-100">
                            <div class="card-body">
                                <div class="indicator">
                                    <div class="indicator-icon indicator-warning">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="indicator-text">
                                        <div class="indicator-title">Reservas Pendentes</div>
                                        <div class="indicator-value">
                                            <?php echo count($reservas); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos e Detalhes -->
                <div class="row mb-4">
                    <!-- Gráfico de Receitas x Despesas -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-area me-1"></i> Receitas vs Despesas (Últimos 6 Meses)
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="receitasDespesasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumo Financeiro -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-1"></i> Resumo Financeiro
                            </div>
                            <div class="card-body">
                                <div class="resumo-financeiro">
                                    <h6 class="text-success mb-3">Receitas</h6>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Reservas:</span>
                                            <span class="text-success">€<?php echo number_format($resumo['receitas_reservas'], 2, ',', '.'); ?></span>
                                        </div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?php echo $resumo['total_receitas'] > 0 ? ($resumo['receitas_reservas'] / $resumo['total_receitas'] * 100) : 0; ?>%" 
                                                 aria-valuenow="<?php echo $resumo['total_receitas'] > 0 ? ($resumo['receitas_reservas'] / $resumo['total_receitas'] * 100) : 0; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Outras Receitas:</span>
                                            <span class="text-success">€<?php echo number_format($resumo['receitas_extras'], 2, ',', '.'); ?></span>
                                        </div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?php echo $resumo['total_receitas'] > 0 ? ($resumo['receitas_extras'] / $resumo['total_receitas'] * 100) : 0; ?>%" 
                                                 aria-valuenow="<?php echo $resumo['total_receitas'] > 0 ? ($resumo['receitas_extras'] / $resumo['total_receitas'] * 100) : 0; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <h6 class="text-danger mb-3 mt-4">Despesas</h6>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Manutenções:</span>
                                            <span class="text-danger">€<?php echo number_format($resumo['despesas_manutencao'], 2, ',', '.'); ?></span>
                                        </div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                 style="width: <?php echo $resumo['total_despesas'] > 0 ? ($resumo['despesas_manutencao'] / $resumo['total_despesas'] * 100) : 0; ?>%" 
                                                 aria-valuenow="<?php echo $resumo['total_despesas'] > 0 ? ($resumo['despesas_manutencao'] / $resumo['total_despesas'] * 100) : 0; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Serviços:</span>
                                            <span class="text-danger">€<?php echo number_format($resumo['despesas_servicos'], 2, ',', '.'); ?></span>
                                        </div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                 style="width: <?php echo $resumo['total_despesas'] > 0 ? ($resumo['despesas_servicos'] / $resumo['total_despesas'] * 100) : 0; ?>%" 
                                                 aria-valuenow="<?php echo $resumo['total_despesas'] > 0 ? ($resumo['despesas_servicos'] / $resumo['total_despesas'] * 100) : 0; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Despesas Gerais:</span>
                                            <span class="text-danger">€<?php echo number_format($resumo['despesas_gerais'], 2, ',', '.'); ?></span>
                                        </div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                 style="width: <?php echo $resumo['total_despesas'] > 0 ? ($resumo['despesas_gerais'] / $resumo['total_despesas'] * 100) : 0; ?>%" 
                                                 aria-valuenow="<?php echo $resumo['total_despesas'] > 0 ? ($resumo['despesas_gerais'] / $resumo['total_despesas'] * 100) : 0; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="border-top mt-4 pt-3">
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Resultado Líquido:</span>
                                            <span class="<?php echo $saldo_atual >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                €<?php echo number_format($saldo_atual, 2, ',', '.'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Abas de Conteúdo -->
                <div class="card mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="financasTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="movimentacoes-tab" data-bs-toggle="tab" data-bs-target="#movimentacoes" type="button" role="tab" aria-controls="movimentacoes" aria-selected="true">
                                    <i class="fas fa-exchange-alt me-1"></i> Movimentações
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button" role="tab" aria-controls="reservas" aria-selected="false">
                                    <i class="fas fa-calendar-alt me-1"></i> Reservas Pendentes
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="servicos-tab" data-bs-toggle="tab" data-bs-target="#servicos" type="button" role="tab" aria-controls="servicos" aria-selected="false">
                                    <i class="fas fa-concierge-bell me-1"></i> Serviços
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="manutencoes-tab" data-bs-toggle="tab" data-bs-target="#manutencoes" type="button" role="tab" aria-controls="manutencoes" aria-selected="false">
                                    <i class="fas fa-tools me-1"></i> Manutenções
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="financasTabContent">
                            <!-- Tab Movimentações Recentes -->
                            <div class="tab-pane fade show active" id="movimentacoes" role="tabpanel" aria-labelledby="movimentacoes-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tabelaMovimentacoes">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Descrição</th>
                                                <th>Tipo</th>
                                                <th>Origem</th>
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($movimentacoes as $movimentacao): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($movimentacao['data'])); ?></td>
                                                <td><?php echo $movimentacao['descricao']; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $movimentacao['tipo'] == 'receita' ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo ucfirst($movimentacao['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo ucfirst($movimentacao['origem']); ?></td>
                                                <td class="<?php echo $movimentacao['tipo'] == 'receita' ? 'text-success' : 'text-danger'; ?>">
                                                    <?php 
                                                    echo $movimentacao['tipo'] == 'receita' ? '+' : '-'; 
                                                    echo '€' . number_format($movimentacao['valor'], 2, ',', '.'); 
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab Reservas Pendentes -->
                            <div class="tab-pane fade" id="reservas" role="tabpanel" aria-labelledby="reservas-tab">
                                <div class="d-flex justify-content-between mb-3">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaReceitaModal">
                                        <i class="fas fa-plus me-1"></i> Nova Receita
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tabelaReservasPendentes">
                                        <thead>
                                            <tr>
                                                <th>Check-in</th>
                                                <th>Check-out</th>
                                                <th>Hóspede</th>
                                                <th>Casa</th>
                                                <th>Valor Total</th>
                                                <th>Pago</th>
                                                <th>Pendente</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservas as $reserva): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($reserva['R_data_checkin'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($reserva['R_data_checkout'])); ?></td>
                                                <td><?php echo $reserva['nome_hospede']; ?></td>
                                                <td><?php echo $reserva['nome_casa']; ?></td>
                                                <td>€<?php echo number_format($reserva['R_preco_total'], 2, ',', '.'); ?></td>
                                                <td>€<?php echo number_format($reserva['R_valor_pago'], 2, ',', '.'); ?></td>
                                                <td class="text-danger">€<?php echo number_format($reserva['R_preco_total'] - $reserva['R_valor_pago'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="id_reserva" value="<?php echo $reserva['R_id_reserva']; ?>">
                                                        <button type="submit" name="receber_reserva" class="btn btn-success btn-sm" onclick="return confirm('Confirma o recebimento do valor pendente desta reserva?')">
                                                            <i class="fas fa-check me-1"></i> Receber
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab Serviços -->
                            <div class="tab-pane fade" id="servicos" role="tabpanel" aria-labelledby="servicos-tab">
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoServicoModal">
                                        <i class="fas fa-plus me-1"></i> Novo Serviço
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tabelaServicos">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Descrição</th>
                                                <th>Preço</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($servicos as $servico): ?>
                                            <tr>
                                                <td><?php echo $servico['S_nome']; ?></td>
                                                <td><?php echo $servico['S_descricao']; ?></td>
                                                <td class="text-danger">€<?php echo number_format($servico['S_preco'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="id_servico" value="<?php echo $servico['S_id_servico']; ?>">
                                                        <button type="submit" name="pagar_servico" class="btn btn-success btn-sm" onclick="return confirm('Confirma o pagamento deste serviço?')">
                                                            <i class="fas fa-check me-1"></i> Pagar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab Manutenções -->
                            <div class="tab-pane fade" id="manutencoes" role="tabpanel" aria-labelledby="manutencoes-tab">
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaManutencaoModal">
                                        <i class="fas fa-plus me-1"></i> Nova Manutenção
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tabelaManutencoes">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Casa</th>
                                                <th>Tipo</th>
                                                <th>Descrição</th>
                                                <th>Custo</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($manutencoes as $manutencao): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($manutencao['M_data_inicio'])); ?></td>
                                                <td><?php echo $manutencao['nome_casa']; ?></td>
                                                <td><?php echo $manutencao['M_tipo']; ?></td>
                                                <td><?php echo substr($manutencao['M_descricao'], 0, 50) . (strlen($manutencao['M_descricao']) > 50 ? '...' : ''); ?></td>
                                                <td class="text-danger">€<?php echo number_format($manutencao['M_custo'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $manutencao['M_pago'] == 1 ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo $manutencao['M_pago'] == 1 ? 'Pago' : 'Pendente'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($manutencao['M_pago'] == 0): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="id_manutencao" value="<?php echo $manutencao['M_id_manutencao']; ?>">
                                                        <button type="submit" name="pagar_manutencao" class="btn btn-success btn-sm" onclick="return confirm('Confirma o pagamento desta manutenção?')">
                                                            <i class="fas fa-check me-1"></i> Pagar
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Nova Receita -->
    <div class="modal fade" id="novaReceitaModal" tabindex="-1" aria-labelledby="novaReceitaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="novaReceitaModalLabel">Registrar Nova Receita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" required>
                        </div>
                        <div class="mb-3">
                            <label for="valor" class="form-label">Valor (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="valor" name="valor" required>
                        </div>
                        <div class="mb-3">
                            <label for="data" class="form-label">Data</label>
                            <input type="date" class="form-control" id="data" name="data" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Receita</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="Serviço">Serviço</option>
                                <option value="Reserva">Reserva</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="metodo_pagamento" class="form-label">Método de Pagamento</label>
                            <select class="form-select" id="metodo_pagamento" name="metodo_pagamento" required>
                                <option value="Transferência">Transferência Bancária</option>
                                <option value="Cartão">Cartão de Crédito</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="MB WAY">MB WAY</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="nova_receita" class="btn btn-success">Registrar Receita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Novo Serviço -->
    <div class="modal fade" id="novoServicoModal" tabindex="-1" aria-labelledby="novoServicoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="novoServicoModalLabel">Novo Serviço</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Serviço</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="preco" class="form-label">Preço (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="preco" name="preco" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="novo_servico" class="btn btn-primary">Registrar Serviço</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Nova Manutenção -->
    <div class="modal fade" id="novaManutencaoModal" tabindex="-1" aria-labelledby="novaManutencaoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="novaManutencaoModalLabel">Nova Manutenção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="casa" class="form-label">Casa</label>
                            <select class="form-select" id="casa" name="casa" required>
                                <?php
                                $sql_casas = "SELECT C_id_casa, C_nome FROM casas";
                                $resultado_casas = $conexao->query($sql_casas);
                                while ($casa = $resultado_casas->fetch_assoc()) {
                                    echo "<option value='{$casa['C_id_casa']}'>{$casa['C_nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Manutenção</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="Canalizações (canos, torneiras, autoclismo)">Canalizações (canos, torneiras, autoclismo)</option>
                                <option value="Instalações elétricas (lâmpadas, tomadas, quadro elétrico)">Instalações elétricas (lâmpadas, tomadas, quadro elétrico)</option>
                                <option value="Pintura">Pintura</option>
                                <option value="Jardinagem">Jardinagem</option>
                                <option value="Limpeza">Limpeza</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="custo" class="form-label">Custo (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="custo" name="custo" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="nova_manutencao" class="btn btn-warning">Registrar Manutenção</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Inicializar DataTables
        $(document).ready(function() {
            $('#tabelaMovimentacoes').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                },
                order: [[0, 'desc']]
            });
            
            $('#tabelaServicos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                },
                order: [[0, 'asc']]
            });
            
            $('#tabelaReservasPendentes').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                },
                order: [[0, 'asc']]
            });
            
            $('#tabelaManutencoes').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                },
                order: [[0, 'desc']]
            });
        });
        
        // Gráfico de Receitas vs Despesas
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('receitasDespesasChart').getContext('2d');
            const receitasDespesasChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($meses_labels); ?>,
                    datasets: [
                        {
                            label: 'Receitas',
                            data: <?php echo json_encode($dados_receitas); ?>,
                            backgroundColor: 'rgba(28, 200, 138, 0.8)',
                            borderColor: 'rgba(28, 200, 138, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Despesas',
                            data: <?php echo json_encode($dados_despesas); ?>,
                            backgroundColor: 'rgba(231, 74, 59, 0.8)',
                            borderColor: 'rgba(231, 74, 59, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€' + value;
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': €' + context.raw;
                                }
                            }
                        }
                    }
                }
            });
        });
        
        // Configurar data de hoje como padrão para os campos de data
        document.getElementById('data_inicio').valueAsDate = new Date();
    </script>
</body>
</html>