<?php
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// Configurações (as mesmas que nas outras páginas)
define('SITE_NAME', 'Quinta das Flores');
define('PRIMARY_COLOR', '#6A0DAD');
define('SECONDARY_COLOR', '#A56EFF');
define('BACKGROUND_COLOR', '#f8f9fa');
define('TEXT_COLOR', '#333333');
define('LIGHT_COLOR', '#f8f8ff');

// Dados de transferência bancária por país
$dados_bancarios = [
    'PT' => [
        'banco' => 'Banco Português',
        'iban' => 'PT50 0000 0000 0000 0000 0000 0',
        'swift' => 'BCOMPTPL',
        'titular' => 'Quinta das Flores Lda'
    ],
    'ES' => [
        'banco' => 'Banco Español',
        'iban' => 'ES80 0000 0000 0000 0000 0000',
        'swift' => 'BSCHESMM',
        'titular' => 'Quinta das Flores SL'
    ],
    'FR' => [
        'banco' => 'Banque Française',
        'iban' => 'FR76 0000 0000 0000 0000 0000 000',
        'swift' => 'BNPAFRPP',
        'titular' => 'Quinta des Fleurs SARL'
    ],
    'BR' => [
        'banco' => 'Banco Brasileiro',
        'iban' => 'BR15 0000 0000 0000 0000 0000 0000 0',
        'swift' => 'BRASBRRJ',
        'titular' => 'Quinta das Flores Ltda'
    ],
    'US' => [
        'banco' => 'American Bank',
        'iban' => 'US20 0000 0000 0000 0000 0000',
        'swift' => 'BOFAUS3N',
        'titular' => 'Quinta das Flores Inc'
    ],
    'DE' => [
        'banco' => 'Deutsche Bank',
        'iban' => 'DE89 0000 0000 0000 0000 00',
        'swift' => 'DEUTDEFF',
        'titular' => 'Quinta der Blumen GmbH'
    ],
    'IT' => [
        'banco' => 'Banca Italiana',
        'iban' => 'IT60 0000 0000 0000 0000 0000 000',
        'swift' => 'UNCRITMM',
        'titular' => 'Quinta dei Fiori SRL'
    ]
];

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar dados essenciais da sessão
$required_session_vars = ['checkin', 'checkout', 'num_hospedes', 'nome', 'email', 'pais_regiao'];
foreach ($required_session_vars as $var) {
    if (!isset($_SESSION[$var])) {
        header('Location: pagina1.php');
        exit();
    }
}

require_once '../conexao.php';

if ($conexao->connect_error) {
    die('<div class="error-container" style="padding: 20px; color: red;">Falha na conexão com o banco de dados. Por favor, tente novamente mais tarde.</div>');
}

// Calcula número de noites
$checkin = new DateTime($_SESSION['checkin']);
$checkout = new DateTime($_SESSION['checkout']);
$num_noites = $checkin->diff($checkout)->days;

// Calcula preço total
$preco_total = 120 * $num_noites;
if (isset($_SESSION['servicos'])) {
    foreach ($_SESSION['servicos'] as $servico) {
        switch ($servico) {
            case 'pequeno-almoco':
                $preco_total += 15 * $num_noites;
                break;
            case 'decoracao':
                $preco_total += 130;
                break;
            case 'limpeza':
                $preco_total += 15 * $num_noites;
                break;
            case 'cesto':
                $preco_total += 10;
                break;
            case 'jantar':
                $preco_total += 15 * $num_noites;
                break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pagamento = $_POST['pagamento'] ?? '';
    $dados_pagamento = [];
    
    if ($metodo_pagamento === 'Cartão') {
        $dados_pagamento = [
            'nome_cartao' => $_POST['nome_cartao'] ?? '',
            'numero_cartao' => $_POST['numero_cartao'] ?? '',
            'validade' => $_POST['validade'] ?? '',
            'cvc' => $_POST['cvc'] ?? ''
        ];
    } elseif ($metodo_pagamento === 'MB WAY') {
        $dados_pagamento = [
            'numero_mbway' => $_POST['numero_mbway'] ?? ''
        ];
    } elseif ($metodo_pagamento === 'Transferência') {
        $dados_pagamento = [
            'comprovativo' => $_FILES['comprovativo']['name'] ?? ''
        ];
    } elseif ($metodo_pagamento === 'Dinheiro') {
        $dados_pagamento = [
            'pagamento_local' => 'sim'
        ];
    }
    
    // Validação básica
    $erros = [];
    if (empty($metodo_pagamento)) {
        $erros[] = "Selecione um método de pagamento.";
    }
    
    if ($metodo_pagamento === 'Cartão') {
        if (empty($dados_pagamento['nome_cartao'])) {
            $erros[] = "Nome do titular do cartão é obrigatório.";
        }
        if (!preg_match('/^\d{16}$/', str_replace(' ', '', $dados_pagamento['numero_cartao']))) {
            $erros[] = "Número do cartão inválido.";
        }
        if (empty($dados_pagamento['validade'])) {
            $erros[] = "Data de validade do cartão é obrigatória.";
        }
        if (!preg_match('/^\d{3}$/', $dados_pagamento['cvc'])) {
            $erros[] = "Código CVC inválido.";
        }
    } elseif ($metodo_pagamento === 'MB WAY') {
        if (!preg_match('/^\d{9}$/', $dados_pagamento['numero_mbway'])) {
            $erros[] = "Número MB WAY inválido.";
        }
    } elseif ($metodo_pagamento === 'Transferência') {
        if (empty($_FILES['comprovativo']['name'])) {
            $erros[] = "Por favor, envie o comprovativo de transferência.";
        } else {
            $extensao = strtolower(pathinfo($_FILES['comprovativo']['name'], PATHINFO_EXTENSION));
            $extensoes_validas = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($extensao, $extensoes_validas)) {
                $erros[] = "Formato de arquivo inválido. Use PDF, JPG ou PNG.";
            } elseif ($_FILES['comprovativo']['size'] > 5 * 1024 * 1024) { // 5MB
                $erros[] = "Arquivo muito grande. Tamanho máximo: 5MB.";
            }
        }
    }
    // Não há validação específica para pagamento em dinheiro
    
    if (empty($erros)) {
        // Processar upload do comprovativo se for transferência
        if ($metodo_pagamento === 'Transferência' && isset($_FILES['comprovativo'])) {
            $diretorio_upload = 'comprovativos/';
            if (!is_dir($diretorio_upload)) {
                mkdir($diretorio_upload, 0755, true);
            }
            
            $nome_arquivo = uniqid() . '_' . basename($_FILES['comprovativo']['name']);
            $caminho_completo = $diretorio_upload . $nome_arquivo;
            
            if (move_uploaded_file($_FILES['comprovativo']['tmp_name'], $caminho_completo)) {
                $dados_pagamento['comprovativo'] = $nome_arquivo;
            } else {
                $erros[] = "Erro ao enviar comprovativo. Por favor, tente novamente.";
            }
        }
        
        if (empty($erros)) {
            // Seleciona uma casa disponível
            $query = "SELECT C_id_casa FROM casas 
                      WHERE C_id_casa NOT IN (
                          SELECT R_id_casa FROM reservas 
                          WHERE (R_data_checkin < ? AND R_data_checkout > ?) 
                          OR (R_data_checkin < ? AND R_data_checkout >= ?)
                      ) 
                      AND C_estado = 'disponível'
                      LIMIT 1";
            $stmt = $conexao->prepare($query);
            $stmt->bind_param("ssss", $_SESSION['checkout'], $_SESSION['checkin'], $_SESSION['checkin'], $_SESSION['checkout']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $casa = $resultado->fetch_assoc();
                $id_casa = $casa['C_id_casa'];
                
                // Insere a reserva
                $query = "INSERT INTO reservas (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, R_num_hospedes, R_preco_total, R_estado, R_metodo_pagamento, R_dados_pagamento)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conexao->prepare($query);
                $status_reserva = 'Pendente'; // Estado inicial
                
                // Define o estado da reserva de acordo com o método de pagamento
                if ($metodo_pagamento === 'Transferência' || $metodo_pagamento === 'Dinheiro') {
                    $status_reserva = 'Pendente';
                } else {
                    $status_reserva = 'Confirmada';
                }
                
                $dados_pagamento_json = json_encode($dados_pagamento);
                $stmt->bind_param("iissidsss", $_SESSION['id'], $id_casa, $_SESSION['checkin'], $_SESSION['checkout'], $_SESSION['num_hospedes'], $preco_total, $status_reserva, $metodo_pagamento, $dados_pagamento_json);
                
                if ($stmt->execute()) {
                    $reserva_id = $conexao->insert_id;
                    $_SESSION['reserva_id'] = $reserva_id;
                    $_SESSION['preco_total'] = $preco_total;
                    $_SESSION['metodo_pagamento'] = $metodo_pagamento;
                    
                    // Redireciona para confirmação
                    header('Location: confirmacao.php');
                    exit();
                } else {
                    $erros[] = "Erro ao processar reserva. Por favor, tente novamente.";
                }
            } else {
                $erros[] = "Nenhuma casa disponível para as datas selecionadas.";
            }
        }
    }
    
    if (!empty($erros)) {
        $mensagem_erro = implode("<br>", $erros);
    }
}

// Obtém os dados bancários para o país selecionado
$pais = $_SESSION['pais_regiao'] ?? 'PT';
$info_bancaria = $dados_bancarios[$pais] ?? $dados_bancarios['PT'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="logotipos/logotipo2.png">
    <style>
        :root {
            --primary-color: <?= PRIMARY_COLOR ?>;
            --secondary-color: <?= SECONDARY_COLOR ?>;
            --background-color: <?= BACKGROUND_COLOR ?>;
            --text-color: <?= TEXT_COLOR ?>;
            --light-color: <?= LIGHT_COLOR ?>;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            counter-reset: step;
        }
        
        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .progress-step:before {
            content: counter(step);
            counter-increment: step;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border: 2px solid #ddd;
            display: block;
            text-align: center;
            margin: 0 auto 10px;
            border-radius: 50%;
            background-color: white;
            color: #999;
            font-weight: bold;
        }
        
        .progress-step.completed:before {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .progress-step.active:before {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .progress-step:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: #ddd;
            top: 15px;
            left: -50%;
            z-index: -1;
        }
        
        .progress-step:first-child:after {
            content: none;
        }
        
        .progress-step.completed:after,
        .progress-step.active:after {
            background-color: var(--primary-color);
        }
        
        .resumo-reserva {
            background-color: var(--light-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
        }
        
        .resumo-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .resumo-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .resumo-item span:first-child {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .metodos-pagamento {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .metodo-option {
            flex: 1;
            min-width: 150px;
        }
        
        .metodo-option input {
            display: none;
        }
        
        .metodo-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            height: 100%;
        }
        
        .metodo-option input:checked + label {
            border-color: var(--primary-color);
            background-color: rgba(106, 13, 173, 0.05);
        }
        
        .metodo-option i {
            font-size: 2em;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .dados-pagamento {
            display: none;
            animation: fadeIn 0.5s;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .error-message {
            color: #dc3545;
            margin-top: 10px;
            padding: 10px;
            background-color: #ffecec;
            border-radius: 5px;
            display: none;
        }
        
        .preco-total {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--primary-color);
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: right;
        }
        
        .info-bancaria {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-bancaria h4 {
            color: var(--primary-color);
            margin-top: 0;
        }
        
        .info-bancaria table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-bancaria table td {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-bancaria table td:first-child {
            font-weight: 600;
            width: 30%;
        }
        
        .arquivo-upload {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .arquivo-upload input[type="file"] {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .progress-steps {
                font-size: 14px;
            }
            
            .progress-step:before {
                width: 25px;
                height: 25px;
                line-height: 25px;
            }
            
            .progress-step:after {
                top: 12px;
            }
            
            .metodos-pagamento {
                flex-direction: column;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .info-bancaria table td:first-child {
                width: 40%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="fade-in">Pagamento</h1>
        
        <div class="progress-steps">
            <div class="progress-step completed">
                <span>Datas</span>
            </div>
            <div class="progress-step completed">
                <span>Dados Pessoais</span>
            </div>
            <div class="progress-step active">
                <span>Pagamento</span>
            </div>
            <div class="progress-step">
                <span>Confirmação</span>
            </div>
        </div>
        
        <?php if (!empty($mensagem_erro)): ?>
            <div class="error-message" style="display: block;">
                <i class="fas fa-exclamation-circle"></i> <?= $mensagem_erro ?>
            </div>
        <?php endif; ?>
        
        <div class="resumo-reserva">
            <h3><i class="fas fa-calendar-check"></i> Resumo da Reserva</h3>
            <div class="resumo-item">
                <span>Datas:</span>
                <span><?= $checkin->format('d/m/Y') ?> - <?= $checkout->format('d/m/Y') ?></span>
            </div>
            <div class="resumo-item">
                <span>Noites:</span>
                <span><?= $num_noites ?></span>
            </div>
            <div class="resumo-item">
                <span>Hóspedes:</span>
                <span><?= $_SESSION['num_hospedes'] ?> <?= $_SESSION['num_hospedes'] == 1 ? 'pessoa' : 'pessoas' ?></span>
            </div>
            <?php if (!empty($_SESSION['servicos'])): ?>
                <div class="resumo-item">
                    <span>Serviços:</span>
                    <span>
                        <?php 
                        $servicos_nomes = [
                            'pequeno-almoco' => 'Pequeno-Almoço',
                            'decoracao' => 'Decoração Temática',
                            'limpeza' => 'Limpeza Diária',
                            'cesto' => 'Cesto de Boas-Vindas',
                            'jantar' => 'Jantar'
                        ];
                        $servicos_selecionados = array_map(function($s) use ($servicos_nomes) {
                            return $servicos_nomes[$s] ?? $s;
                        }, $_SESSION['servicos']);
                        echo implode(', ', $servicos_selecionados);
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="preco-total">
            Total a Pagar: €<?= number_format($preco_total, 2, ',', '.') ?>
        </div>
        
        <form action="pagina3.php" method="POST" id="pagamentoForm" class="fade-in" enctype="multipart/form-data">
            <h3><i class="fas fa-credit-card"></i> Método de Pagamento</h3>
            
            <div class="metodos-pagamento">
                <div class="metodo-option">
                    <input type="radio" id="cartao_radio" name="pagamento" value="Cartão" required 
                           <?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'Cartão') ? 'checked' : '' ?>>
                    <label for="cartao_radio">
                        <i class="fas fa-credit-card"></i>
                        <span>Cartão de Crédito</span>
                    </label>
                </div>
                
                <div class="metodo-option">
                    <input type="radio" id="mbway_radio" name="pagamento" value="MB WAY" 
                           <?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'MB WAY') ? 'checked' : '' ?>>
                    <label for="mbway_radio">
                        <i class="fas fa-mobile-alt"></i>
                        <span>MB WAY</span>
                    </label>
                </div>
                
                <div class="metodo-option">
                    <input type="radio" id="transferencia_radio" name="pagamento" value="Transferência" 
                           <?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'Transferência') ? 'checked' : '' ?>>
                    <label for="transferencia_radio">
                        <i class="fas fa-university"></i>
                        <span>Transferência Bancária</span>
                    </label>
                </div>
                
                <!-- Nova opção de pagamento em dinheiro -->
                <div class="metodo-option">
                    <input type="radio" id="dinheiro_radio" name="pagamento" value="Dinheiro" 
                           <?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'Dinheiro') ? 'checked' : '' ?>>
                    <label for="dinheiro_radio">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Dinheiro</span>
                    </label>
                </div>
            </div>
            
            <div id="dados-cartao" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'Cartão') ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-credit-card"></i> Dados do Cartão</h4>
                
                <div class="form-group">
                    <label for="nome_cartao">Nome no Cartão</label>
                    <input type="text" id="nome_cartao" name="nome_cartao" class="form-control" 
                           value="<?= isset($_POST['nome_cartao']) ? htmlspecialchars($_POST['nome_cartao']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="numero_cartao">Número do Cartão</label>
                    <input type="text" id="numero_cartao" name="numero_cartao" class="form-control" 
                           value="<?= isset($_POST['numero_cartao']) ? htmlspecialchars($_POST['numero_cartao']) : '' ?>"
                           placeholder="1234 5678 9012 3456">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="validade">Validade</label>
                        <input type="month" id="validade" name="validade" class="form-control" 
                               value="<?= isset($_POST['validade']) ? htmlspecialchars($_POST['validade']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cvc">Código CVC</label>
                        <input type="text" id="cvc" name="cvc" class="form-control" 
                               value="<?= isset($_POST['cvc']) ? htmlspecialchars($_POST['cvc']) : '' ?>"
                               placeholder="123" maxlength="3">
                    </div>
                </div>
            </div>
            
            <div id="dados-mbway" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'MB WAY') ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-mobile-alt"></i> Dados MB WAY</h4>
                
                <div class="form-group">
                    <label for="numero_mbway">Número de Telemóvel</label>
                    <input type="text" id="numero_mbway" name="numero_mbway" class="form-control" 
                           value="<?= isset($_POST['numero_mbway']) ? htmlspecialchars($_POST['numero_mbway']) : (isset($_SESSION['telefone']) ? $_SESSION['telefone'] : '') ?>"
                           placeholder="912345678">
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Irá receber uma notificação no seu telemóvel para confirmar o pagamento.
                </div>
            </div>
            
            <div id="dados-transferencia" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'Transferência') ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-university"></i> Dados para Transferência Bancária</h4>
                
                <div class="info-bancaria">
                    <h4>Informações Bancárias (<?= $pais ?>)</h4>
                    <table>
                        <tr>
                            <td>Banco:</td>
                            <td><?= $info_bancaria['banco'] ?></td>
                        </tr>
                        <tr>
                            <td>IBAN:</td>
                            <td><?= $info_bancaria['iban'] ?></td>
                        </tr>
                        <tr>
                            <td>SWIFT/BIC:</td>
                            <td><?= $info_bancaria['swift'] ?></td>
                        </tr>
                        <tr>
                            <td>Titular:</td>
                            <td><?= $info_bancaria['titular'] ?></td>
                        </tr>
                        <tr>
                            <td>Valor:</td>
                            <td>€<?= number_format($preco_total, 2, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-group">
                    <label>Comprovativo de Transferência</label>
                    <div class="arquivo-upload">
                        <input type="file" id="comprovativo" name="comprovativo" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small>Formatos aceites: PDF, JPG, PNG (max. 5MB)</small>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Sua reserva será confirmada após a validação do comprovativo. Envie o comprovativo com o número da reserva como referência.
                </div>
            </div>
            
            <!-- Adicionar seção para pagamento em dinheiro -->
            <div id="dados-dinheiro" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'Dinheiro') ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-money-bill-wave"></i> Pagamento em Dinheiro</h4>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> O pagamento em dinheiro deve ser efetuado no momento do check-in. A sua reserva será mantida como pendente até a confirmação do pagamento.
                </div>
            </div>
            
            <div class="form-actions">
                <a href="pagina2.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <button type="submit" class="btn btn-primary pulse">
                    <i class="fas fa-check"></i> Confirmar Reserva
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar métodos de pagamento
            const cartaoRadio = document.getElementById('cartao_radio');
            const mbwayRadio = document.getElementById('mbway_radio');
            const transferenciaRadio = document.getElementById('transferencia_radio');
            const dinheiroRadio = document.getElementById('dinheiro_radio');
            const dadosCartao = document.getElementById('dados-cartao');
            const dadosMbway = document.getElementById('dados-mbway');
            const dadosTransferencia = document.getElementById('dados-transferencia');
            const dadosDinheiro = document.getElementById('dados-dinheiro');
            
            function toggleMetodoPagamento() {
                dadosCartao.style.display = 'none';
                dadosMbway.style.display = 'none';
                dadosTransferencia.style.display = 'none';
                dadosDinheiro.style.display = 'none';
                
                if (cartaoRadio.checked) {
                    dadosCartao.style.display = 'block';
                } else if (mbwayRadio.checked) {
                    dadosMbway.style.display = 'block';
                } else if (transferenciaRadio.checked) {
                    dadosTransferencia.style.display = 'block';
                } else if (dinheiroRadio.checked) {
                    dadosDinheiro.style.display = 'block';
                }
            }
            
            cartaoRadio.addEventListener('change', toggleMetodoPagamento);
            mbwayRadio.addEventListener('change', toggleMetodoPagamento);
            transferenciaRadio.addEventListener('change', toggleMetodoPagamento);
            dinheiroRadio.addEventListener('change', toggleMetodoPagamento);
            
            // Validação do formulário
            document.getElementById('pagamentoForm').addEventListener('submit', function(e) {
                const metodoPagamento = document.querySelector('input[name="pagamento"]:checked');
                const errorElement = document.querySelector('.error-message');
                
                if (!metodoPagamento) {
                    e.preventDefault();
                    errorElement.style.display = 'block';
                    errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Por favor, selecione um método de pagamento.';
                    return false;
                }
                
                if (metodoPagamento.value === 'Cartão') {
                    const nomeCartao = document.getElementById('nome_cartao').value;
                    const numeroCartao = document.getElementById('numero_cartao').value.replace(/\s/g, '');
                    const validade = document.getElementById('validade').value;
                    const cvc = document.getElementById('cvc').value;
                    
                    if (!nomeCartao || !numeroCartao || !validade || !cvc) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Por favor, preencha todos os campos do cartão.';
                        return false;
                    }
                    
                    if (!/^\d{16}$/.test(numeroCartao)) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Número do cartão inválido. Deve conter 16 dígitos.';
                        return false;
                    }
                    
                    if (!/^\d{3}$/.test(cvc)) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Código CVC inválido. Deve conter 3 dígitos.';
                        return false;
                    }
                } else if (metodoPagamento.value === 'MB WAY') {
                    const numeroMbway = document.getElementById('numero_mbway').value;
                    
                    if (!/^\d{9}$/.test(numeroMbway)) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Número MB WAY inválido. Deve conter 9 dígitos.';
                        return false;
                    }
                } else if (metodoPagamento.value === 'Transferência') {
                    const comprovativo = document.getElementById('comprovativo').files[0];
                    
                    if (!comprovativo) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Por favor, envie o comprovativo de transferência.';
                        return false;
                    }
                    
                    const extensoesValidas = ['pdf', 'jpg', 'jpeg', 'png'];
                    const extensao = comprovativo.name.split('.').pop().toLowerCase();
                    const tamanhoMaximo = 5 * 1024 * 1024; // 5MB
                    
                    if (!extensoesValidas.includes(extensao)) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Formato de arquivo inválido. Use PDF, JPG ou PNG.';
                        return false;
                    }
                    
                    if (comprovativo.size > tamanhoMaximo) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Arquivo muito grande. Tamanho máximo: 5MB.';
                        return false;
                    }
                }
                // Não é necessária validação para pagamento em dinheiro
                
                return true;
            });
            
            // Formatação do número do cartão
            document.getElementById('numero_cartao').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '');
                if (value.length > 16) value = value.substr(0, 16);
                
                let formatted = '';
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) formatted += ' ';
                    formatted += value[i];
                }
                
                e.target.value = formatted;
            });
        });
    </script>
</body>
</html>