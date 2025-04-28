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
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .payment-method {
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-method:hover {
            border-color: var(--primary-color);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: var(--primary-color-light);
        }

        .payment-method input[type="radio"] {
            margin: 0;
        }

        .payment-method i {
            font-size: 24px;
            color: var(--primary-color);
        }

        .payment-details {
            display: none;
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            background-color: #f8f8f8;
        }

        .payment-details.active {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }

        .bank-details {
            margin-top: 20px;
            padding: 20px;
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }

        .bank-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .bank-details td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
        }

        .bank-details td:first-child {
            font-weight: 600;
            color: var(--primary-color);
            width: 30%;
        }

        .upload-area {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            border-radius: var(--border-radius);
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: var(--primary-color);
        }

        .card-input-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .card-input-group {
                grid-template-columns: 1fr;
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
                <span>Check-in:</span>
                <span><?= (new DateTime($_SESSION['checkin']))->format('d/m/Y') ?></span>
            </div>
            <div class="resumo-item">
                <span>Check-out:</span>
                <span><?= (new DateTime($_SESSION['checkout']))->format('d/m/Y') ?></span>
            </div>
            <div class="resumo-item">
                <span>Noites:</span>
                <span><?= $num_noites ?></span>
            </div>
            <div class="resumo-item">
                <span>Hóspedes:</span>
                <span><?= $_SESSION['num_hospedes'] ?></span>
            </div>
            <div class="resumo-item">
                <span>Valor Total:</span>
                <span>€<?= $preco_total ?></span>
            </div>
        </div>

        <form action="pagina3.php" method="POST" id="pagamentoForm" class="fade-in" enctype="multipart/form-data">
            <h3><i class="fas fa-credit-card"></i> Método de Pagamento</h3>
            <div class="payment-methods">
                <div class="payment-method" onclick="selectPayment('Cartão')">
                    <input type="radio" name="pagamento" value="Cartão" id="cartao" required>
                    <label for="cartao">
                        <i class="fas fa-credit-card"></i>
                        Cartão de Crédito/Débito
                    </label>
                </div>

                <div class="payment-method" onclick="selectPayment('MB WAY')">
                    <input type="radio" name="pagamento" value="MB WAY" id="mbway" required>
                    <label for="mbway">
                        <i class="fas fa-mobile-alt"></i>
                        MB WAY
                    </label>
                </div>

                <div class="payment-method" onclick="selectPayment('Transferência')">
                    <input type="radio" name="pagamento" value="Transferência" id="transferencia" required>
                    <label for="transferencia">
                        <i class="fas fa-university"></i>
                        Transferência Bancária
                    </label>
                </div>

                <div class="payment-method" onclick="selectPayment('Dinheiro')">
                    <input type="radio" name="pagamento" value="Dinheiro" id="dinheiro" required>
                    <label for="dinheiro">
                        <i class="fas fa-money-bill-wave"></i>
                        Pagamento em Dinheiro
                    </label>
                </div>
            </div>

            <div id="cartao-details" class="payment-details">
                <div class="card-input-group">
                    <div class="form-group">
                        <label for="nome_cartao"><i class="fas fa-user"></i> Nome no Cartão</label>
                        <input type="text" id="nome_cartao" name="nome_cartao" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="numero_cartao"><i class="fas fa-credit-card"></i> Número do Cartão</label>
                        <input type="text" id="numero_cartao" name="numero_cartao" class="form-control" maxlength="19">
                    </div>

                    <div class="form-group">
                        <label for="validade"><i class="fas fa-calendar-alt"></i> Data de Validade</label>
                        <input type="text" id="validade" name="validade" class="form-control" placeholder="MM/AA" maxlength="5">
                    </div>

                    <div class="form-group">
                        <label for="cvc"><i class="fas fa-lock"></i> CVC</label>
                        <input type="text" id="cvc" name="cvc" class="form-control" maxlength="3">
                    </div>
                </div>
            </div>

            <div id="mbway-details" class="payment-details">
                <div class="form-group">
                    <label for="numero_mbway"><i class="fas fa-phone"></i> Número de Telefone MB WAY</label>
                    <input type="text" id="numero_mbway" name="numero_mbway" class="form-control" maxlength="9">
                </div>
            </div>

            <div id="transferencia-details" class="payment-details">
                <div class="bank-details">
                    <h4><i class="fas fa-university"></i> Dados Bancários</h4>
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
                    </table>
                </div>

                <div class="form-group">
                    <label for="comprovativo"><i class="fas fa-file-upload"></i> Comprovativo de Transferência</label>
                    <div class="upload-area" onclick="document.getElementById('comprovativo').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x"></i>
                        <p>Clique para selecionar ou arraste o comprovativo</p>
                    </div>
                    <input type="file" id="comprovativo" name="comprovativo" style="display: none" accept=".pdf,.jpg,.jpeg,.png">
                    <small id="file-name"></small>
                </div>
            </div>

            <div id="dinheiro-details" class="payment-details">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    O pagamento será feito em dinheiro no momento do check-in.
                </div>
            </div>

            <div class="form-actions">
                <a href="pagina2.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    Confirmar Pagamento <i class="fas fa-check"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function selectPayment(method) {
            // Remove seleção anterior
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            document.querySelectorAll('.payment-details').forEach(el => {
                el.classList.remove('active');
            });

            // Seleciona novo método
            document.querySelector(`input[value="${method}"]`).checked = true;
            document.querySelector(`input[value="${method}"]`).closest('.payment-method').classList.add('selected');
            
            // Mostra detalhes do método selecionado
            const detailsDiv = document.getElementById(`${method.toLowerCase()}-details`);
            if (detailsDiv) {
                detailsDiv.classList.add('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Formatar número do cartão
            document.getElementById('numero_cartao')?.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(.{4})/g, '$1 ').trim();
                e.target.value = value;
            });

            // Formatar data de validade
            document.getElementById('validade')?.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 2) {
                    value = value.substring(0,2) + '/' + value.substring(2);
                }
                e.target.value = value;
            });

            // Manipular upload de arquivo
            document.getElementById('comprovativo')?.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name;
                if (fileName) {
                    document.getElementById('file-name').textContent = fileName;
                }
            });
            
            // Restaurar método de pagamento selecionado após erro de validação
            <?php if (isset($_POST['pagamento'])): ?>
                selectPayment('<?= htmlspecialchars($_POST['pagamento']) ?>');
            <?php endif; ?>
        });

        // Validação do formulário
        document.getElementById('pagamentoForm').addEventListener('submit', function(e) {
            const metodoPagamento = document.querySelector('input[name="pagamento"]:checked')?.value;
            let valid = true;
            let message = '';

            switch (metodoPagamento) {
                case 'Cartão':
                    const numero = document.getElementById('numero_cartao').value.replace(/\s/g, '');
                    const validade = document.getElementById('validade').value;
                    const cvc = document.getElementById('cvc').value;

                    if (!document.getElementById('nome_cartao').value) {
                        message = 'Nome no cartão é obrigatório.';
                        valid = false;
                    } else if (numero.length !== 16) {
                        message = 'Número do cartão inválido.';
                        valid = false;
                    } else if (!/^\d{2}\/\d{2}$/.test(validade)) {
                        message = 'Data de validade inválida.';
                        valid = false;
                    } else if (!/^\d{3}$/.test(cvc)) {
                        message = 'CVC inválido.';
                        valid = false;
                    }
                    break;

                case 'MB WAY':
                    const mbway = document.getElementById('numero_mbway').value;
                    if (!/^\d{9}$/.test(mbway)) {
                        message = 'Número MB WAY inválido.';
                        valid = false;
                    }
                    break;

                case 'Transferência':
                    const comprovativo = document.getElementById('comprovativo').files[0];
                    if (!comprovativo) {
                        message = 'Por favor, envie o comprovativo de transferência.';
                        valid = false;
                    }
                    break;
            }

            if (!valid) {
                e.preventDefault();
                const errorDiv = document.querySelector('.error-message');
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                errorDiv.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
</body>
</html>