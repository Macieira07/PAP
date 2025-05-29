<?php
session_start();
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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
require_once '../conexao.php';

if ($conexao->connect_error) {
    die('<div class="error-container" style="padding: 20px; color: red;">Falha na ligação à base de dados. Por favor, tente novamente mais tarde.</div>');
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
                $erros[] = "Formato de ficheiro inválido. Use PDF, JPG ou PNG.";
            } elseif ($_FILES['comprovativo']['size'] > 5 * 1024 * 1024) { // 5MB
                $erros[] = "Ficheiro muito grande. Tamanho máximo: 5MB.";
            }
        }
    }
    // Sem validação específica para pagamento em dinheiro
    
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
        try {
            $query = "SELECT C_id_casa FROM casas 
                      WHERE C_id_casa NOT IN (
                          SELECT R_id_casa FROM reservas 
                          WHERE (R_data_checkin < ? AND R_data_checkout > ?) 
                          OR (R_data_checkin < ? AND R_data_checkout >= ?)
                          AND R_estado NOT IN ('cancelada', 'concluída')
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
                    
                // Define o estado da reserva - apenas UMA inserção
                $status_reserva = ($metodo_pagamento === 'Transferência' || $metodo_pagamento === 'Dinheiro') 
                                  ? 'pendente' : 'confirmada';
                
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
                    $erros[] = "Erro ao processar reserva. Por favor, tente novamente. Erro: " . $stmt->error;
                }
            } else {
                $erros[] = "Nenhuma casa disponível para as datas selecionadas.";
            }
        } catch (Exception $e) {
            $erros[] = "Ocorreu um erro ao processar a sua reserva: " . $e->getMessage();
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
$page_title = 'Faça sua Reserva'; // Altere para cada página
require_once 'header.php'; 
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="../index/chatbot.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/logotipo1.png">
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
                            'decoracao' => 'Decoração Temática',
                            'limpeza' => 'Limpeza Diária',
                            'cesto' => 'Cesto de Boas-Vindas',
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
                           placeholder="1234 5678 9012 3456" maxlength="19">
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
                    <label for="numero_mbway">Número de Telemóvel</label>                    <input type="text" id="numero_mbway" name="numero_mbway" class="form-control" 
                           value="<?= isset($_POST['numero_mbway']) ? htmlspecialchars($_POST['numero_mbway']) : (isset($_SESSION['telefone']) ? $_SESSION['telefone'] : '') ?>"
                           placeholder="912345678" maxlength="9">
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
                        <input type="file" id="comprovativo" name="comprovativo" accept=".pdf,.jpg,.jpeg,.png">
                        <small>Formatos aceites: PDF, JPG, PNG (max. 5MB)</small>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> A sua reserva será confirmada após a validação do comprovativo. Envie o comprovativo com o número da reserva como referência.
                </div>
            </div>
            
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
        
<div class="chatbot-container">
    <div class="chatbot-button" id="chatbotButton">
        <i class="fa-solid fa-comment-dots"></i>
    </div>
    <div class="chatbot-box" id="chatbotBox">
        <div class="chatbot-header">
            <div class="chatbot-title">
                <img src="assets/logos/logotipo1.png" alt="Quinta Flores" class="chatbot-logo">
                <span>Assistente Virtual da Quinta Flores</span>
            </div>
            <button class="chatbot-close" id="chatbotClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="message bot-message">
                <img src="assets/logos/logotipo1.png" alt="Bot" class="message-avatar">
                <div class="message-content">
                    <p>Olá! Bem-vindo à Quinta Flores. Como posso ajudá-lo hoje?</p>
                </div>
            </div>
        </div>
        <div class="chatbot-input-container">
            <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Digite sua mensagem...">
            <button id="chatbotSend" class="chatbot-send">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
        <div class="chatbot-suggestions">
            <button class="suggestion-button">
                <i class="fa-solid fa-calendar-check"></i> Reservas
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-bed"></i> Acomodações
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-bell-concierge"></i> Serviços
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-map-location-dot"></i> Localização
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-person-hiking"></i> Atividades
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-euro-sign"></i> Preços
            </button>
        </div>
        <div class="chatbot-footer">
            <span>Quinta Flores - ChatBot</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatbotButton = document.getElementById('chatbotButton');
        const chatbotBox = document.getElementById('chatbotBox');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatbotInput = document.getElementById('chatbotInput');
        const chatbotSend = document.getElementById('chatbotSend');
        const chatbotMessages = document.getElementById('chatbotMessages');
        const suggestionButtons = document.querySelectorAll('.suggestion-button');

        // Mostrar chatbot box
        chatbotButton.addEventListener('click', function() {
            chatbotBox.style.display = 'flex';
            chatbotButton.style.display = 'none';
        });

        // Fechar chatbot box
        chatbotClose.addEventListener('click', function() {
            chatbotBox.style.display = 'none';
            chatbotButton.style.display = 'flex';
        });

        // Enviar mensagem ao pressionar Enter
        chatbotInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Enviar mensagem ao clicar no botão
        chatbotSend.addEventListener('click', sendMessage);

        // Botões de sugestão
        suggestionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const text = this.textContent.trim();
                chatbotInput.value = text;
                sendMessage();
            });
        });

        // Respostas do chatbot
        const responses = {
            saudacao: "Bem-vindo à Quinta Flores. Em que podemos ser úteis?",
            agradecimento: "Obrigado pelo seu contacto. Ficamos ao dispor para qualquer questão relacionada com a Quinta Flores ou com a região de Ponte de Lima. Desejamos-lhe uma excelente estadia connosco.",
            despedida: "Agradecemos o seu contacto. Esperamos ter o prazer de o receber brevemente na Quinta Flores. Votos de um excelente dia.",
            reservas: {
                geral: "Para efetuar uma reserva na Quinta Flores, dispõe das seguintes opções:\n\n• Utilize o botão 'Reservar Agora' disponível no topo da página\n• Contacte-nos através do número: +351 912 418 976\n• Ou visite-nos presencialmente mediante agendamento.",
                cancelamento: "A nossa política de cancelamento é flexível:\n\n• Cancelamentos até 7 dias antes da data de chegada – reembolso total\n• Cancelamentos entre 3 e 7 dias – taxa de 30%\n• Cancelamentos com menos de 3 dias – taxa de 50% do valor total da reserva.",
                alteracao: "As alterações à reserva estão sujeitas a disponibilidade. Recomendamos que entre em contacto connosco com a maior antecedência possível para verificarmos as alternativas disponíveis.",
                disponibilidade: "Para consultar a disponibilidade para datas específicas, utilize o formulário na página inicial ou contacte-nos diretamente.",
                antecedencia: "Durante a época alta (junho a setembro) e em períodos festivos, recomendamos que efetue a sua reserva com 1 a 2 meses de antecedência."
            },
            acomodacoes: {
                casaprincipal: "A Casa Principal é nossa maior acomodação com 3 quartos com 5 camas de casal, 3 casas de banho, sala de estar espaçosa, cozinha completa e varanda com vista para os jardins.",
                geral: "Oferecemos acomodações confortáveis e bem equipadas. A Casa Principal comporta até 10 pessoas com todos os confortos necessários para uma estadia perfeita."
            },
            precos: {
                geral: "A Quinta Flores apresenta um valor fixo de 120€ por noite, com capacidade máxima até 10 pessoas. Para eventos ou ocasiões especiais com número superior de participantes, solicitamos que entre em contacto connosco previamente."
            },
            servicos: {
                geral: "A Quinta Flores disponibiliza diversos serviços pensados para proporcionar uma estadia confortável e memorável:\n\n• Receção disponível das 08h00 às 22h00\n• Piscina exterior com zona de solário\n• Estacionamento privativo gratuito\n• Jardins e zonas de lazer",
                piscina: "A nossa piscina exterior encontra-se acessível diariamente. Dispõe de zona de solário com espreguiçadeiras e toalhas disponibilizadas gratuitamente aos hóspedes.",
                wifi: "Disponibilizamos Wi-Fi gratuito de alta velocidade em toda a propriedade, incluindo nas zonas exteriores. A palavra-passe será fornecida no momento do check-in.",
                limpeza: "O serviço de limpeza é sempre feito antes e depois da estadia. Caso pretenda limpeza diária, poderá ser solicitado por um valor adicional de 15€ por dia.",
                recepcao: "A receção está disponível entre as 08h00 e as 22h00. Para chegadas fora deste horário, temos ao dispor um sistema de check-in automatizado, mediante pedido prévio."
            },
            localizacao: {
                geral: "A Quinta Flores está situada a cerca de 3 km do centro histórico de Ponte de Lima, oferecendo um ambiente calmo e campestre com fácil acesso às principais atrações da região.",
                como_chegar: "Como chegar à Quinta Flores:\n\n• De carro: pela A3, tome a saída para Ponte de Lima e siga em direção a Arcozelo. Após aproximadamente 2,5 km, encontrará sinalização com a nossa identificação à direita.",
                arredores: "Nas proximidades da Quinta Flores poderá explorar vinícolas de Vinho Verde, percursos pedestres, atividades no Rio Lima e restaurantes típicos da gastronomia minhota.",
                estacionamento: "Disponibilizamos estacionamento privado e gratuito dentro da propriedade, com capacidade para todos os nossos hóspedes."
            },
            atividades: {
                geral: "A região do Minho oferece inúmeras atividades: passeios de bicicleta, degustação de vinhos, caminhadas, passeios a cavalo, canoagem no Rio Lima e visitas culturais. Se tiver interesse pode ver no nosso site mais atividades que pode fazer perto da Quinta Flores.",
                cicloturismo: "Dispomos ainda de várias rotas para descobrir as paisagens únicas da região.",
                gastronomia: "O Minho é famoso por sua gastronomia. Recomendamos restaurantes autênticos nas proximidades.",
                criancas: "Para famílias com crianças, recomendamos: caça ao tesouro em nossos jardins, visita ao parque aventura, piqueniques à beira-rio e passeios de barco no Rio Lima."
            },
            fallback: "Sou um assistente virtual da Quinta Flores. Peço desculpa, mas não consegui compreender corretamente a sua pergunta. Poderia reformulá-la ou especificar melhor, por favor?"
        };

        // Função principal para enviar mensagem
        function sendMessage() {
            const message = chatbotInput.value.trim();
            if (message === '') return;

            // Adicionar mensagem do usuário
            addMessage(message, 'user');
            chatbotInput.value = '';

            // Simular digitação do bot
            showTypingIndicator();

            // Processar resposta com um pequeno delay
            setTimeout(() => {
                removeTypingIndicator();
                const response = getResponse(message);
                addMessage(response, 'bot');
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }, 1000 + Math.random() * 1000);
        }

        // Função para adicionar mensagem à conversa
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            
            let avatar;
            if (sender === 'bot') {
                avatar = document.createElement('img');
                avatar.src = 'assets/logos/logotipo1.png';
                avatar.alt = 'Bot';
                avatar.className = 'message-avatar';
            } else {
                avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.style.backgroundColor = '#8CB58E';
                avatar.style.display = 'flex';
                avatar.style.justifyContent = 'center';
                avatar.style.alignItems = 'center';
                avatar.style.color = 'white';
                avatar.style.fontWeight = 'bold';
                avatar.textContent = 'EU';
            }

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = formatMessageText(text);

            if (sender === 'user') {
                messageDiv.appendChild(contentDiv);
                messageDiv.appendChild(avatar);
            } else {
                messageDiv.appendChild(avatar);
                messageDiv.appendChild(contentDiv);
            }

            chatbotMessages.appendChild(messageDiv);
        }

        // Função para formatar o texto da mensagem
        function formatMessageText(text) {
            return text.replace(/\n/g, '<br>');
        }

        // Função para mostrar indicador de digitação
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot-message typing-message';
            
            const avatar = document.createElement('img');
            avatar.src = 'assets/logos/logotipo1.png';
            avatar.alt = 'Bot';
            avatar.className = 'message-avatar';
            
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator';
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('span');
                typingIndicator.appendChild(dot);
            }
            
            typingDiv.appendChild(avatar);
            typingDiv.appendChild(typingIndicator);
            chatbotMessages.appendChild(typingDiv);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }

        // Função para remover indicador de digitação
        function removeTypingIndicator() {
            const typingMessage = document.querySelector('.typing-message');
            if (typingMessage) {
                typingMessage.remove();
            }
        }

        // Função para determinar a resposta adequada (versão corrigida)
        function getResponse(message) {
            const lowercaseMessage = message.toLowerCase();
            
            // 1. Verificar despedidas (mais específico)
            if (/(adeus|tchau|até logo|até mais|até breve|goodbye|bye|até à próxima|ate a proxima)/i.test(lowercaseMessage)) {
                return responses.despedida;
            }
            
            // 2. Verificar agradecimentos explícitos
            if (/(obrigado|obrigada|agradecido|agradecida|thanks|thank you|grato|grata|muito obrigado|muito obrigada)\b/i.test(lowercaseMessage)) {
                return responses.agradecimento;
            }
            
            // 3. Verificar saudações
            if (/(olá|ola|oi|bom dia|boa tarde|boa noite|hello|hi|hey|saudações|saudacoes)\b/i.test(lowercaseMessage)) {
                return responses.saudacao;
            }
            
            // 4. Verificar reservas
            if (/(reserva|reservar|booking|alugar|disponibilidade|marcar|fazer reserva|agendar|quero reservar)\b/i.test(lowercaseMessage)) {
                if (/(cancelar|cancelamento|anular|desmarcar|cancelada|cancelar reserva)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.cancelamento;
                } else if (/(alterar|alteração|mudar|modificar|trocar|alterar reserva)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.alteracao;
                } else if (/(disponível|disponibilidade|tem vaga|vagas|datas livres|datas disponíveis)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.disponibilidade;
                } else if (/(antecedência|antecedencia|com antecedência|quando reservar|prazo para reservar|tempo antes)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.antecedencia;
                } else {
                    return responses.reservas.geral;
                }
            }
            
            // 5. Verificar acomodações
            if (/(acomodação|acomodacoes|quarto|quartos|casa|alojamento|hospedagem|suite|suíte)\b/i.test(lowercaseMessage)) {
                if (/(casa principal|principal|casa mãe|principal casa)\b/i.test(lowercaseMessage)) {
                    return responses.acomodacoes.casaprincipal;
                } else {
                    return responses.acomodacoes.geral;
                }
            }
            
            // 6. Verificar preços
            if (/(preço|preco|preços|precos|valor|valores|custo|quanto custa|tarifa|taxa|preço por noite)\b/i.test(lowercaseMessage)) {
                return responses.precos.geral;
            }
            
            // 7. Verificar serviços
            if (/(serviço|servico|facilidade|comodidade|serviços|comodidades|infraestrutura)\b/i.test(lowercaseMessage)) {
                if (/(piscina|nadar|piscinas|área de lazer aquática)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.piscina;
                } else if (/(wifi|internet|wi-fi|rede|conexão|conexao)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.wifi;
                } else if (/(limpeza|arrumação|arrumacao|faxina|serviço de limpeza)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.limpeza;
                } else if (/(recepção|recepcao|atendimento|balcão|front desk)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.recepcao;
                } else {
                    return responses.servicos.geral;
                }
            }
            
            // 8. Verificar localização
            if (/(localização|localizacao|endereço|endereco|onde fica|como chegar|morada|situação|direção|direcao)\b/i.test(lowercaseMessage)) {
                if (/(como chegar|chegar|direções|direcoes|rota|caminho|instruções|instrucoes|acesso)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.como_chegar;
                } else if (/(arredores|proximidade|perto|próximo|proximo|vizinhança|vizinhanca|área|região|regiao)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.arredores;
                } else if (/(estacionamento|parque|carro|vaga|garagem|parking)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.estacionamento;
                } else {
                    return responses.localizacao.geral;
                }
            }
            
            // 9. Verificar atividades
            if (/(atividade|atividades|fazer|lazer|passeio|passeios|entretenimento|diversão|diversao|programa)\b/i.test(lowercaseMessage)) {
                if (/(bicicleta|bike|cicloturismo|bicicletas|ciclismo|andar de bicicleta)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.cicloturismo;
                } else if (/(comida|gastronomia|comer|restaurante|culinária|culinaria|prato|refeição|refeicao)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.gastronomia;
                } else if (/(criança|criancas|família|familia|kids|crianças|famílias|familias|filhos|filha|filho)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.criancas;
                } else {
                    return responses.atividades.geral;
                }
            }
            
            // 10. Se nenhuma das condições acima for atendida
            return responses.fallback;
        }
    });
</script>   
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