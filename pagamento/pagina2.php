<?php
// Ativar exibição de erros (para desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once '../conexao.php';

session_start(); 


    // Verificar se há oferta e bloquear serviços adicionais
$oferta_ativa = isset($_SESSION['codigo_oferta']) && !empty($_SESSION['codigo_oferta']);
$id_hospede = $_SESSION['id'];
$query_hospede = "SELECT H_nome, H_email, H_telefone, H_documento_ident FROM hospedes WHERE H_id_hospede = ?";
$stmt_hospede = $conexao->prepare($query_hospede);
$stmt_hospede->bind_param("i", $id_hospede);
$stmt_hospede->execute();
$resultado_hospede = $stmt_hospede->get_result();

if ($resultado_hospede->num_rows > 0) {
    $hospede = $resultado_hospede->fetch_assoc();
    
    // Define os valores padrão para o formulário
    $nome_padrao = $hospede['H_nome'];
    $email_padrao = $hospede['H_email'];
    $telefone_padrao = $hospede['H_telefone'];
    $documento_padrao = $hospede['H_documento_ident'];
} else {
    // Redireciona se não encontrar o hóspede
    header('Location: pagina1.php');
    exit();
}
// Configurações fixas
define('BACKGROUND_COLOR', '#f8f9fa');
define('TEXT_COLOR', '#333333');
define('LIGHT_COLOR', '#f8f8ff');

// Verificar dados essenciais da sessão
$required_session_vars = ['id', 'checkin', 'checkout', 'num_hospedes'];
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

try {
    $checkin = new DateTime($_SESSION['checkin']);
    $checkout = new DateTime($_SESSION['checkout']);
    if ($checkout <= $checkin) {
        throw new Exception("Datas inválidas");
    }
    $num_noites = $checkin->diff($checkout)->days;
} catch (Exception $e) {
    header('Location: pagina1.php');
    exit();
}

$id_hospede = $_SESSION['id'];

// Lista de países com códigos de telefone e regras de validação
$paises = [
    "PT" => ["nome" => "Portugal", "codigo" => "+351", "regex" => "/^\d{9}$/"],
    "ES" => ["nome" => "Espanha", "codigo" => "+34", "regex" => "/^\d{9}$/"],
    "FR" => ["nome" => "França", "codigo" => "+33", "regex" => "/^\d{9}$/"],
    "BR" => ["nome" => "Brasil", "codigo" => "+55", "regex" => "/^\d{10,11}$/"],
    "US" => ["nome" => "Estados Unidos", "codigo" => "+1", "regex" => "/^\d{10}$/"],
    "DE" => ["nome" => "Alemanha", "codigo" => "+49", "regex" => "/^\d{10,11}$/"],
    "IT" => ["nome" => "Itália", "codigo" => "+39", "regex" => "/^\d{9,10}$/"],
];

// Recalcula número de noites só por precaução
$checkin = new DateTime($_SESSION['checkin']);
$checkout = new DateTime($_SESSION['checkout']);
$num_noites = $checkin->diff($checkout)->days;

// Processar formulário
$mensagem_erro = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_completo = isset($_POST['nome_completo']) ? trim(htmlspecialchars($_POST['nome_completo'])) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $pais_regiao = isset($_POST['pais_regiao']) ? trim($_POST['pais_regiao']) : '';
    $confirmacao_digital = isset($_POST['confirmacao']) ? 1 : 0;
    $cancelamento = isset($_POST['cancelamento']) ? 1 : 0;
    $descricao_decoracao = isset($_POST['descricao_decoracao']) ? trim(htmlspecialchars($_POST['descricao_decoracao'])) : '';

    // Validação dos campos
    $erros = [];
    if (empty($nome_completo) || strlen($nome_completo) < 2) {
        $erros[] = "Nome completo inválido.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    }
    if (!preg_match('/^\d{9}$/', $documento)) {
        $erros[] = "Documento deve ter exatamente 9 dígitos.";
    }
    if (empty($pais_regiao)) {
        $erros[] = "Selecione um país/região.";
    }
    if (!empty($pais_regiao)) {
        $regex = $paises[$pais_regiao]['regex'];
        if (!preg_match($regex, $telefone)) {
            $erros[] = "Número de telefone inválido para o país selecionado.";
        }
    }
    if (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos']) && empty($descricao_decoracao)) {
        $erros[] = "Por favor, descreva o tema desejado para a decoração.";
    }

    if (empty($erros)) {
        // Armazena os dados na sessão
        $_SESSION['nome_completo'] = $nome_completo;
        $_SESSION['email'] = $email;
        $_SESSION['documento'] = $documento;
        $_SESSION['telefone'] = $telefone;
        $_SESSION['pais_regiao'] = $pais_regiao;
        $_SESSION['confirmacao_digital'] = $confirmacao_digital;
        $_SESSION['cancelamento'] = $cancelamento;

        if (isset($_POST['servicos'])) {
            $_SESSION['servicos'] = $_POST['servicos'];
            if (in_array('decoracao', $_POST['servicos'])) {
                $_SESSION['descricao_decoracao'] = $descricao_decoracao;
            }
        } else {
            $_SESSION['servicos'] = [];
        }

        // Atualizar nome do hóspede no BD
        $sql = "UPDATE hospedes SET H_nome = ? WHERE H_id_hospede = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("si", $nome_completo, $id_hospede);
        $stmt->execute();
        $stmt->close();

        // Redirecionar para próxima página
        header('Location: pagina3.php');
        exit();
    } else {
        $mensagem_erro = implode("<br>", $erros);
    }
}

// Calcular preço base e total
$preco_base = 120 * $num_noites;
$preco_total = $preco_base;
if (isset($_SESSION['servicos'])) {
    foreach ($_SESSION['servicos'] as $servico) {
        switch ($servico) {
            case 'limpeza':
                $preco_total += 15 * $num_noites;
                break;
            case 'cesto':
                $preco_total += 10;
                break;
            case 'decoracao':
                $preco_total += 130;
                break;
        }
    }
}


$page_title = 'Faça sua Reserva';
require_once 'header.php';


?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações Pessoais - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="../index/chatbot.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/logotipo1.jpg">
</head>
<body>
    <div class="container">
        <h1 class="fade-in">Informações Pessoais</h1>
        
        <div class="progress-steps">
            <div class="progress-step completed">
                <span>Datas</span>
            </div>
            <div class="progress-step active">
                <span>Dados Pessoais</span>
            </div>
            <div class="progress-step">
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
        </div>
        
        <form action="pagina2.php" method="POST" id="dadosPessoaisForm" class="fade-in">
            <h3><i class="fas fa-user-circle"></i> Dados Pessoais</h3>
            <div class="form-group">
                <label for="nome_completo"><i class="fas fa-user"></i> Nome Completo</label>
                <input type="text" id="nome_completo" name="nome_completo" class="form-control" 
                value="<?= isset($_POST['nome_completo']) ? htmlspecialchars($_POST['nome_completo']) : htmlspecialchars($nome_padrao) ?>" 
                required minlength="2">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                <input type="email" id="email" name="email" class="form-control" 
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($email_padrao) ?>" 
                required>
                <div id="erro-email" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="documento"><i class="fas fa-id-card"></i> Identificação Civil (9 dígitos)</label>
                <input type="text" id="documento" name="documento" class="form-control" 
                value="<?= isset($_POST['documento']) ? htmlspecialchars($_POST['documento']) : htmlspecialchars($documento_padrao) ?>" 
                required pattern="\d{9}" maxlength="9">
            </div>
            
            <div class="form-group">
                <label for="pais_regiao"><i class="fas fa-globe"></i> País/Região</label>
                <select id="pais_regiao" name="pais_regiao" class="form-control" required>
                    <option value="">Selecione seu país...</option>
                    <?php foreach ($paises as $codigo => $dados): ?>
                        <option value="<?= $codigo ?>" 
                            <?= (isset($_POST['pais_regiao']) && $_POST['pais_regiao'] == $codigo) ? 'selected' : '' ?>>
                            <?= $dados['nome'] ?> (<?= $dados['codigo'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="telefone"><i class="fas fa-phone"></i> Telefone</label>
                <div class="input-group">
                    <select id="codigo_pais" class="form-control" style="flex: 1;">
                        <?php foreach ($paises as $codigo => $dados): ?>
                            <option value="<?= $dados['codigo'] ?>" 
                                data-pais="<?= $codigo ?>"
                                <?= (isset($_POST['pais_regiao']) && $_POST['pais_regiao'] == $codigo) ? 'selected' : '' ?>>
                                <?= $dados['codigo'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="telefone" name="telefone" class="form-control" style="flex: 3;" 
                           value="<?= isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : '' ?>" 
                           required>
                </div>
                <div id="erro-telefone" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="confirmacao" name="confirmacao" value="1" 
                           <?= (isset($_POST['confirmacao']) && $_POST['confirmacao'] == 1) ? 'checked' : '' ?>>
                    <i class="fas fa-check-circle"></i> Gostaria de receber uma confirmação digital?
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="cancelamento" name="cancelamento" value="1" 
                           <?= (isset($_POST['cancelamento']) && $_POST['cancelamento'] == 1) ? 'checked' : '' ?> required>
                    <i class="fas fa-info-circle"></i> Entendo que posso cancelar até 10 dias antes.
                </label>
            </div>
            
            <h3><i class="fas fa-concierge-bell"></i> Serviços Adicionais</h3>
            <?php if ($oferta_ativa): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Você está utilizando um código promocional. Serviços adicionais não estão disponíveis.
    </div>
            <?php else: ?>
                <div class="servico-option">
                    <input type="checkbox" id="decoracao" name="servicos[]" value="decoracao" 
                           <?= (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                           <label>
                    Decoração Temática
                    <div class="servico-detalhes">€130 (valor único)</div>
                </label>

                <div id="descricao-decoracao-container" style="display: none; margin-top: 10px;">
                    <label for="tema-decoracao"><i class="fas fa-star"></i> Tema desejado:</label>
                    <select id="tema-decoracao" name="tema_decoracao" class="form-control">
                        <option value="">Selecione um tema</option>
                        <option value="Romântico">Romântico</option>
                        <option value="Aniversário">Aniversário</option>
                        <option value="Natal">Natal</option>
                        <option value="Lua de Mel">Lua de Mel</option>
                        <option value="Outro">Outro</option>
                    </select>

                    <label for="descricao-decoracao" style="margin-top: 10px;">
                        <i class="fas fa-pencil-alt"></i> Detalhes adicionais (cores, objetos, quantidade de pessoas,  mensagens...):
                    </label>
                    <textarea id="descricao-decoracao" name="descricao_decoracao" class="form-control" rows="3" placeholder="Ex: Balões vermelhos, pétalas na cama, mensagem 'Feliz Aniversário João'..."><?= isset($_POST['descricao_decoracao']) ? htmlspecialchars($_POST['descricao_decoracao']) : '' ?></textarea>
                </div>

                <script>
                // Mostra/esconde o campo de descrição dependendo do checkbox
                document.getElementById('decoracao').addEventListener('change', function() {
                    document.getElementById('descricao-decoracao-container').style.display = this.checked ? 'block' : 'none';
                });
                </script>
                </div>
                <div class="servico-option">
                    <input type="checkbox" id="limpeza" name="servicos[]" value="limpeza" 
                           <?= (isset($_POST['servicos']) && in_array('limpeza', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="limpeza">
                        Limpeza Diária
                        <div class="servico-detalhes">€15 por noite</div>
                    </label>
                </div>
                
                <div class="servico-option">
                    <input type="checkbox" id="cesto" name="servicos[]" value="cesto" 
                           <?= (isset($_POST['servicos']) && in_array('cesto', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="cesto">
                        Cesto de Boas-Vindas
                        <div class="servico-detalhes">€10 (valor único)</div>
                    </label>
                </div>
                
            </div>
            <?php endif; ?>
            
            <div class="preco-total">
                Preço Total: €<span id="preco-total"><?= $preco_total ?></span>
            </div>
            
            <div class="form-actions">
                <a href="pagina1.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> Ir para Pagamento
                </button>
            </div>
        </form>
        
    </div>
    
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sincroniza os selects de país
            const paisRegiaoSelect = document.getElementById('pais_regiao');
            const codigoPaisSelect = document.getElementById('codigo_pais');
            
            paisRegiaoSelect.addEventListener('change', function() {
                const selectedPais = this.value;
                const option = codigoPaisSelect.querySelector(`option[data-pais="${selectedPais}"]`);
                if (option) {
                    codigoPaisSelect.value = option.value;
                }
            });
            
            codigoPaisSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const pais = selectedOption.getAttribute('data-pais');
                if (pais) {
                    paisRegiaoSelect.value = pais;
                }
            });
            
            // Validação em tempo real
            document.getElementById('email').addEventListener('blur', validarEmail);
            document.getElementById('telefone').addEventListener('input', validarTelefone);
            document.getElementById('pais_regiao').addEventListener('change', validarTelefone);
            
            // Mostra/oculta o campo de descrição da decoração ao carregar a página
            document.getElementById('decoracao').addEventListener('change', function() {
                document.getElementById('descricao-decoracao-container').style.display = 
                    this.checked ? 'block' : 'none';
            });
            
            // Verifica se já estava marcado ao carregar a página
            if (document.getElementById('decoracao').checked) {
                document.getElementById('descricao-decoracao-container').style.display = 'block';
            }
            
            // Atualiza o preço total ao carregar a página
            atualizarPreco();
        });
        
        function validarEmail() {
            const email = document.getElementById('email').value;
            const erroEmail = document.getElementById('erro-email');
            
            if (!email) {
                erroEmail.style.display = 'none';
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                erroEmail.style.display = 'block';
                erroEmail.innerHTML = '<i class="fas fa-exclamation-circle"></i> Por favor, insira um e-mail válido.';
            } else {
                erroEmail.style.display = 'none';
            }
        }
        
        function validarTelefone() {
            const telefone = document.getElementById('telefone').value;
            const pais = document.getElementById('pais_regiao').value;
            const erroTelefone = document.getElementById('erro-telefone');
            
            if (!pais || !telefone) {
                erroTelefone.style.display = 'none';
                return;
            }
            
            const regexMap = {
                'PT': /^\d{9}$/,
                'ES': /^\d{9}$/,
                'FR': /^\d{9}$/,
                'BR': /^\d{10,11}$/,
                'US': /^\d{10}$/,
                'DE': /^\d{10,11}$/,
                'IT': /^\d{9,10}$/
            };
            
            const regex = regexMap[pais];
            
            if (regex && !regex.test(telefone)) {
                erroTelefone.style.display = 'block';
                erroTelefone.innerHTML = '<i class="fas fa-exclamation-circle"></i> Número de telefone inválido para o país selecionado.';
            } else {
                erroTelefone.style.display = 'none';
            }
        }
        
        function atualizarPreco() {
            const precoBase = 120 * <?= $num_noites ?>;
            let precoTotal = precoBase;
            const servicos = document.querySelectorAll('input[name="servicos[]"]:checked');
            
            // Mostra/oculta o campo de descrição da decoração
            const decoracaoCheckbox = document.getElementById('decoracao');
            const descricaoContainer = document.getElementById('descricao-decoracao-container');
            if (decoracaoCheckbox.checked) {
                descricaoContainer.style.display = 'block';
            } else {
                descricaoContainer.style.display = 'none';
            }
            
            servicos.forEach(servico => {
                switch (servico.value) {
                    case 'pequeno-almoco':
                        precoTotal += 15 * <?= $num_noites ?>;
                        break;
                    case 'decoracao':
                        precoTotal += 130;
                        break;
                    case 'limpeza':
                        precoTotal += 15 * <?= $num_noites ?>;
                        break;
                    case 'cesto':
                        precoTotal += 10;
                        break;
                    case 'jantar':
                        precoTotal += 15 * <?= $num_noites ?>;
                        break;
                }
            });
            
            document.getElementById('preco-total').textContent = precoTotal;
        }
    </script>
    <?php require_once 'footer.php'; ?>
</body>
</html>