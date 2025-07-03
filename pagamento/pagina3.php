<?php
session_start();
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../conexao.php';
require_once 'i18n.php';
$page_title = I18n::get('payment');

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}
if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr','es'])) {
    I18n::setLanguage($_GET['lang']);
    // Recarrega a página para aplicar as mudanças
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

// Configurações
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

// Verificar dados essenciais da sessão
$required_session_vars = ['checkin', 'checkout', 'num_hospedes', 'nome', 'email', 'pais_regiao'];
foreach ($required_session_vars as $var) {
    if (!isset($_SESSION[$var])) {
        header('Location: pagina1.php');
        exit();
    }
}

// Conectar à base de dados
if ($conexao->connect_error) {
    die('<div class="error-container" style="padding: 20px; color: red;">'.I18n::get('error_message').'</div>');
}

// Cálculo do número de noites
$checkin = new DateTime($_SESSION['checkin']);
$checkout = new DateTime($_SESSION['checkout']);
$num_noites = $checkin->diff($checkout)->days;

// Cálculo do preço total
$preco_total = 120 * $num_noites; // Preço base por noite
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

$mensagem_erro = '';

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
    
    // Validação
    $erros = [];
    if (empty($metodo_pagamento)) {
        $erros[] = I18n::get('select_payment_method');
    }
    
    if ($metodo_pagamento === 'Cartão') {
        if (empty($dados_pagamento['nome_cartao'])) {
            $erros[] = I18n::get('card_name_required');
        }
        if (!preg_match('/^\d{16}$/', str_replace(' ', '', $dados_pagamento['numero_cartao']))) {
            $erros[] = I18n::get('invalid_card_number');
        }
        if (empty($dados_pagamento['validade'])) {
            $erros[] = I18n::get('card_expiry_required');
        }
        if (!preg_match('/^\d{3}$/', $dados_pagamento['cvc'])) {
            $erros[] = I18n::get('invalid_cvc');
        }
    } elseif ($metodo_pagamento === 'MB WAY') {
        if (!preg_match('/^\d{9}$/', $dados_pagamento['numero_mbway'])) {
            $erros[] = I18n::get('invalid_mbway');
        }
    } elseif ($metodo_pagamento === 'Transferência') {
        if (empty($_FILES['comprovativo']['name'])) {
            $erros[] = I18n::get('upload_transfer_proof');
        } else {
            $extensao = strtolower(pathinfo($_FILES['comprovativo']['name'], PATHINFO_EXTENSION));
            $extensoes_validas = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($extensao, $extensoes_validas)) {
                $erros[] = I18n::get('invalid_file_format');
            } elseif ($_FILES['comprovativo']['size'] > 5 * 1024 * 1024) { // 5MB
                $erros[] = I18n::get('file_too_large');
            }
        }
    }
    
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
                $erros[] = I18n::get('upload_error');
            }
        }
        
        if (empty($erros)) {
            // Seleciona uma casa disponível
            try {
                $query = "SELECT C_id_casa FROM casas 
                          WHERE C_id_casa NOT IN (
                              SELECT R_id_casa FROM reservas 
                              WHERE (
                                  (R_data_checkin < ? AND R_data_checkout > ?) 
                                  OR (R_data_checkin < ? AND R_data_checkout >= ?)
                              )
                              AND R_estado NOT IN ('cancelada', 'concluída')
                          ) 
                          AND C_estado = 'disponível'
                          LIMIT 1";
                $stmt = $conexao->prepare($query);
                $stmt->bind_param("ssss", $_SESSION['checkout'], $_SESSION['checkin'], $_SESSION['checkout'], $_SESSION['checkin']);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if ($resultado->num_rows > 0) {
                    $casa = $resultado->fetch_assoc();
                    $id_casa = $casa['C_id_casa'];
                        
                    // Define o estado da reserva
                    $status_reserva = 'confirmada';
                    
                    // Insere a reserva - APENAS UMA VEZ
                    $query = "INSERT INTO reservas (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, 
                              R_num_hospedes, R_preco_total, R_estado, R_metodo_pagamento, R_dados_pagamento)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conexao->prepare($query);
                        
                    $dados_pagamento_json = json_encode($dados_pagamento);
                    $stmt->bind_param("iissidsss", $_SESSION['id'], $id_casa, $_SESSION['checkin'], 
                                     $_SESSION['checkout'], $_SESSION['num_hospedes'], $preco_total, 
                                     $status_reserva, $metodo_pagamento, $dados_pagamento_json);
                    
                    if ($stmt->execute()) {
                        $reserva_id = $conexao->insert_id;
                        $_SESSION['reserva_id'] = $reserva_id;
                        $_SESSION['preco_total'] = $preco_total;
                        $_SESSION['metodo_pagamento'] = $metodo_pagamento;
                            
                        // Redireciona para confirmação
                        header('Location: confirmacao.php');
                        exit();
                    } else {
                        $erros[] = I18n::get('reservation_error') . $stmt->error;
                    }
                } else {
                    $erros[] = I18n::get('no_available_houses');
                }
            } catch (Exception $e) {
                $erros[] = I18n::get('processing_error') . $e->getMessage();
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

require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="<?= I18n::getCurrentLanguage() ?>">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= I18n::get('payment') ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="../index/chatbot.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/logotipo1.png">
</head>
<body>
    <div class="container">
        <h1 class="fade-in"><?= I18n::get('payment') ?></h1>
        <div class="progress-steps">
            <div class="progress-step completed">
                <span><?= I18n::get('dates') ?></span>
            </div>
            <div class="progress-step completed">
                <span><?= I18n::get('personal_info') ?></span>
            </div>
            <div class="progress-step active">
                <span><?= I18n::get('payment') ?></span>
            </div>
            <div class="progress-step">
                <span><?= I18n::get('confirmation') ?></span>
            </div>
        </div>
        <?php if (!empty($mensagem_erro)): ?>
            <div class="error-message" style="display: block;">
                <i class="fas fa-exclamation-circle"></i> <?= $mensagem_erro ?>
            </div>
        <?php endif; ?>
        <div class="resumo-reserva">
            <h3><i class="fas fa-calendar-check"></i> <?= I18n::get('reservation_summary') ?></h3>
            <div class="resumo-item">
                <span><?= I18n::get('dates') ?>:</span>
                <span><?= $checkin->format(I18n::get('date_format')) ?> - <?= $checkout->format(I18n::get('date_format')) ?></span>
            </div>
            <div class="resumo-item">
                <span><?= I18n::get('nights') ?>:</span>
                <span><?= $num_noites ?></span>
            </div>
            <div class="resumo-item">
                <span><?= I18n::get('guests') ?>:</span>
                <span><?= $_SESSION['num_hospedes'] ?> <?= $_SESSION['num_hospedes'] == 1 ? I18n::get('person') : I18n::get('people') ?></span>
            </div>
            <?php if (!empty($_SESSION['servicos'])): ?>
                <div class="resumo-item">
                    <span><?= I18n::get('services') ?>:</span>
                    <span>
                        <?php 
                        $servicos_nomes = [
                            'decoracao' => I18n::get('theme_decoration'),
                            'limpeza' => I18n::get('daily_cleaning'),
                            'cesto' => I18n::get('welcome_basket'),
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
            <?= I18n::get('total_price') ?>: <?= I18n::get('currency') ?><?= number_format($preco_total, 2, ',', '.') ?>
        </div>
        <form action="pagina3.php" method="POST" id="pagamentoForm" class="fade-in" enctype="multipart/form-data">
            <h3><i class="fas fa-credit-card"></i> <?= I18n::get('payment_method') ?></h3>
            
            <div class="metodos-pagamento">
                <div class="metodo-option">
                    <input type="radio" id="cartao_radio" name="pagamento" value="Cartão" required 
                           <?= (isset($_POST['pagamento']) && $_POST['pagamento'] === 'Cartão') ? 'checked' : '' ?>>
                    <label for="cartao_radio">
                        <i class="fas fa-credit-card"></i>
                        <span><?= I18n::get('credit_card') ?></span>
                    </label>
                </div>
                <div class="metodo-option">
                    <input type="radio" id="mbway_radio" name="pagamento" value="MB WAY" 
                           <?= (isset($_POST['pagamento'])) && $_POST['pagamento'] === 'MB WAY' ? 'checked' : '' ?>>
                    <label for="mbway_radio">
                        <i class="fas fa-mobile-alt"></i>
                        <span><?= I18n::get('mbway') ?></span>
                    </label>
                </div>
                
                <div class="metodo-option">
                    <input type="radio" id="transferencia_radio" name="pagamento" value="Transferência" 
                           <?= (isset($_POST['pagamento'])) && $_POST['pagamento'] === 'Transferência' ? 'checked' : '' ?>>
                    <label for="transferencia_radio">
                        <i class="fas fa-university"></i>
                        <span><?= I18n::get('bank_transfer') ?></span>
                    </label>
                </div>
                
                <div class="metodo-option">
                    <input type="radio" id="dinheiro_radio" name="pagamento" value="Dinheiro" 
                           <?= (isset($_POST['pagamento'])) && $_POST['pagamento'] === 'Dinheiro' ? 'checked' : '' ?>>
                    <label for="dinheiro_radio">
                        <i class="fas fa-money-bill-wave"></i>
                        <span><?= I18n::get('cash') ?></span>
                    </label>
                </div>
            </div>
            
            <div id="dados-cartao" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento'])) && $_POST['pagamento'] === 'Cartão' ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-credit-card"></i> <?= I18n::get('card_details') ?></h4>
                
                <div class="form-group">
                    <label for="nome_cartao"><?= I18n::get('card_name') ?></label>
                    <input type="text" id="nome_cartao" name="nome_cartao" class="form-control" 
                           value="<?= isset($_POST['nome_cartao']) ? htmlspecialchars($_POST['nome_cartao']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="numero_cartao"><?= I18n::get('card_number') ?></label>
                    <input type="text" id="numero_cartao" name="numero_cartao" class="form-control" 
                           value="<?= isset($_POST['numero_cartao']) ? htmlspecialchars($_POST['numero_cartao']) : '' ?>"
                           placeholder="1234 5678 9012 3456" maxlength="19">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="validade"><?= I18n::get('expiry_date') ?></label>
                        <input type="month" id="validade" name="validade" class="form-control" 
                               value="<?= isset($_POST['validade']) ? htmlspecialchars($_POST['validade']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cvc"><?= I18n::get('cvc_code') ?></label>
                        <input type="text" id="cvc" name="cvc" class="form-control" 
                               value="<?= isset($_POST['cvc']) ? htmlspecialchars($_POST['cvc']) : '' ?>"
                               placeholder="123" maxlength="3">
                    </div>
                </div>
            </div>
            
            <div id="dados-mbway" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento'])) && $_POST['pagamento'] === 'MB WAY' ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-mobile-alt"></i> <?= I18n::get('mbway_details') ?></h4>
                
                <div class="form-group">
                    <label for="numero_mbway"><?= I18n::get('phone_number') ?></label>
                    <input type="text" id="numero_mbway" name="numero_mbway" class="form-control" 
                           value="<?= isset($_POST['numero_mbway']) ? htmlspecialchars($_POST['numero_mbway']) : (isset($_SESSION['telefone']) ? $_SESSION['telefone'] : '') ?>"
                           placeholder="912345678" maxlength="9">
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?= I18n::get('mbway_notification') ?>
                </div>
            </div>
            
            <div id="dados-transferencia" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento']) && $_POST['pagamento']) === 'Transferência' ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-university"></i> <?= I18n::get('bank_transfer_details') ?></h4>
                
                <div class="info-bancaria">
                    <h4><?= I18n::get('bank_information') ?> (<?= $pais ?>)</h4>
                    <table>
                        <tr>
                            <td><?= I18n::get('bank') ?>:</td>
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
                            <td><?= I18n::get('account_holder') ?>:</td>
                            <td><?= $info_bancaria['titular'] ?></td>
                        </tr>
                        <tr>
                            <td><?= I18n::get('amount') ?>:</td>
                            <td><?= I18n::get('currency') ?><?= number_format($preco_total, 2, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="form-group">
                    <label><?= I18n::get('transfer_proof') ?></label>
                    <div class="arquivo-upload">
                        <input type="file" id="comprovativo" name="comprovativo" accept=".pdf,.jpg,.jpeg,.png">
                        <small><?= I18n::get('accepted_formats') ?></small>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?= I18n::get('transfer_confirmation_note') ?>
                </div>
            </div>
            
            <div id="dados-dinheiro" class="dados-pagamento" 
                 style="<?= (isset($_POST['pagamento']) && $_POST['pagamento']) === 'Dinheiro' ? 'display: block;' : '' ?>">
                <h4><i class="fas fa-money-bill-wave"></i> <?= I18n::get('cash_payment') ?></h4>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?= I18n::get('cash_payment_note') ?>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="pagina2.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <?= I18n::get('back') ?>
                </a>
                <button type="submit" class="btn btn-primary pulse">
                    <i class="fas fa-check"></i> <?= I18n::get('confirm_reservation') ?>
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
            
            // Form validation
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
                    validateCartao(e);
                } else if (metodoPagamento.value === 'MB WAY') {
                    validateMBWay(e);
                } else if (metodoPagamento.value === 'Transferência') {
                    validateTransferencia(e);
                }
            });
            
            function validateCartao(e) {
                const nomeCartao = document.getElementById('nome_cartao').value;
                const numeroCartao = document.getElementById('numero_cartao').value.replace(/\s/g, '');
                const validade = document.getElementById('validade').value;
                const cvc = document.getElementById('cvc').value;
                const errorElement = document.querySelector('.error-message');
                
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
            }
            
            function validateMBWay(e) {
                const numeroMbway = document.getElementById('numero_mbway').value;
                const errorElement = document.querySelector('.error-message');
                
                if (!/^\d{9}$/.test(numeroMbway)) {
                    e.preventDefault();
                    errorElement.style.display = 'block';
                    errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Número MB WAY inválido. Deve conter 9 dígitos.';
                    return false;
                }
            }
            
            function validateTransferencia(e) {
                const comprovativo = document.getElementById('comprovativo').files[0];
                const errorElement = document.querySelector('.error-message');
                
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
                    errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Formato de ficheiro inválido. Use PDF, JPG ou PNG.';
                    return false;
                }
                
                if (comprovativo.size > tamanhoMaximo) {
                    e.preventDefault();
                    errorElement.style.display = 'block';
                    errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Ficheiro muito grande. Tamanho máximo: 5MB.';
                    return false;
                }
            }
        });
    </script>
    <?php require_once 'footer.php'; ?>
</body>
</html>