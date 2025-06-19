<?php
session_start(); 
require_once 'i18n.php';
require_once '../conexao.php';
$page_title = I18n::get('personal_information');

if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr','es'])) {
    I18n::setLanguage($_GET['lang']);
    // Recarrega a página para aplicar as mudanças
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

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

// Verificar dados essenciais da sessão
$required_session_vars = ['id', 'checkin', 'checkout', 'num_hospedes'];
foreach ($required_session_vars as $var) {
    if (!isset($_SESSION[$var])) {
        header('Location: pagina1.php');
        exit();
    }
}

if ($conexao->connect_error) {
    die('<div class="error-container" style="padding: 20px; color: red;">'.I18n::get('database_connection_error').'</div>');
}

try {
    $checkin = new DateTime($_SESSION['checkin']);
    $checkout = new DateTime($_SESSION['checkout']);
    if ($checkout <= $checkin) {
        throw new Exception(I18n::get('invalid_dates'));
    }
    $num_noites = $checkin->diff($checkout)->days;
} catch (Exception $e) {
    header('Location: pagina1.php');
    exit();
}

$id_hospede = $_SESSION['id'];

// Lista de países com códigos de telefone e regras de validação
$paises = [
    "PT" => ["nome" => I18n::get('portugal'), "codigo" => "+351", "regex" => "/^\d{9}$/"],
    "ES" => ["nome" => I18n::get('spain'), "codigo" => "+34", "regex" => "/^\d{9}$/"],
    "FR" => ["nome" => I18n::get('france'), "codigo" => "+33", "regex" => "/^\d{9}$/"],
    "BR" => ["nome" => I18n::get('brazil'), "codigo" => "+55", "regex" => "/^\d{10,11}$/"],
    "US" => ["nome" => I18n::get('usa'), "codigo" => "+1", "regex" => "/^\d{10}$/"],
    "DE" => ["nome" => I18n::get('germany'), "codigo" => "+49", "regex" => "/^\d{10,11}$/"],
    "IT" => ["nome" => I18n::get('italy'), "codigo" => "+39", "regex" => "/^\d{9,10}$/"],
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
        $erros[] = I18n::get('invalid_full_name');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = I18n::get('invalid_email');
    }
    if (!preg_match('/^\d{9}$/', $documento)) {
        $erros[] = I18n::get('invalid_document');
    }
    if (empty($pais_regiao)) {
        $erros[] = I18n::get('select_country_error');
    }
    if (!empty($pais_regiao)) {
        $regex = $paises[$pais_regiao]['regex'];
        if (!preg_match($regex, $telefone)) {
            $erros[] = I18n::get('invalid_phone');
        }
    }
    if (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos']) && empty($descricao_decoracao)) {
        $erros[] = I18n::get('please_describe_theme');
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

require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="<?= I18n::getCurrentLanguage() ?>">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= I18n::get('personal_information') ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="../includes/chatbot.css">
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
</head>
<body>
    <div class="container">
        <h1 class="fade-in"><?= I18n::get('personal_information') ?></h1>
        
        <div class="progress-steps">
            <div class="progress-step completed">
                <span><?= I18n::get('dates') ?></span>
            </div>
            <div class="progress-step active">
                <span><?= I18n::get('personal_data') ?></span>
            </div>
            <div class="progress-step">
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
        </div>
        
        <form action="pagina2.php" method="POST" id="dadosPessoaisForm" class="fade-in">
            <h3><i class="fas fa-user-circle"></i> <?= I18n::get('personal_data') ?></h3>
            <div class="form-group">
                <label for="nome_completo"><i class="fas fa-user"></i> <?= I18n::get('full_name') ?></label>
                <input type="text" id="nome_completo" name="nome_completo" class="form-control" 
                value="<?= isset($_POST['nome_completo']) ? htmlspecialchars($_POST['nome_completo']) : htmlspecialchars($nome_padrao) ?>" 
                required minlength="2" placeholder="<?= I18n::get('required_field') ?>">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> <?= I18n::get('email') ?></label>
                <input type="email" id="email" name="email" class="form-control" 
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($email_padrao) ?>" 
                required placeholder="<?= I18n::get('required_field') ?>">
                <div id="erro-email" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="documento"><i class="fas fa-id-card"></i> <?= I18n::get('civil_identification') ?></label>
                <input type="text" id="documento" name="documento" class="form-control" 
                value="<?= isset($_POST['documento']) ? htmlspecialchars($_POST['documento']) : htmlspecialchars($documento_padrao) ?>" 
                required pattern="\d{9}" maxlength="9" placeholder="<?= I18n::get('required_field') ?>">
            </div>
            
            <div class="form-group">
                <label for="pais_regiao"><i class="fas fa-globe"></i> <?= I18n::get('country_region') ?></label>
                <select id="pais_regiao" name="pais_regiao" class="form-control" required>
                    <option value=""><?= I18n::get('select_country') ?></option>
                    <?php foreach ($paises as $codigo => $dados): ?>
                        <option value="<?= $codigo ?>" 
                            <?= (isset($_POST['pais_regiao']) && $_POST['pais_regiao'] == $codigo) ? 'selected' : '' ?>>
                            <?= $dados['nome'] ?> (<?= $dados['codigo'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="telefone"><i class="fas fa-phone"></i> <?= I18n::get('phone') ?></label>
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
                           value="<?= isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : htmlspecialchars($telefone_padrao ?? '') ?>"
                           required placeholder="<?= I18n::get('required_field') ?>">
                </div>
                <div id="erro-telefone" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="confirmacao" name="confirmacao" value="1" 
                           <?= (isset($_POST['confirmacao']) && $_POST['confirmacao'] == 1) ? 'checked' : '' ?>>
                    <i class="fas fa-check-circle"></i> <?= I18n::get('digital_confirmation') ?>
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="cancelamento" name="cancelamento" value="1" 
                           <?= (isset($_POST['cancelamento']) && $_POST['cancelamento'] == 1) ? 'checked' : '' ?> required>
                    <i class="fas fa-info-circle"></i> <?= I18n::get('cancellation_policy') ?>
                </label>
            </div>
            
            <h3><i class="fas fa-concierge-bell"></i> <?= I18n::get('additional_services') ?></h3>
            <?php if ($oferta_ativa): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?= I18n::get('promo_code_active') ?>
                </div>
            <?php else: ?>
                <div class="servico-option">
                    <input type="checkbox" id="decoracao" name="servicos[]" value="decoracao" 
                           <?= (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="decoracao">
                        <?= I18n::get('theme_decoration') ?>
                        <div class="servico-detalhes">€130 (<?= I18n::get('single_price') ?>)</div>
                    </label>

                    <div id="descricao-decoracao-container" style="display: none; margin-top: 10px;">
                        <label for="tema-decoracao"><i class="fas fa-star"></i> <?= I18n::get('theme_description') ?></label>
                        <select id="tema-decoracao" name="tema_decoracao" class="form-control">
                            <option value=""><?= I18n::get('select_theme') ?></option>
                            <option value="Romântico"><?= I18n::get('romantic') ?></option>
                            <option value="Aniversário"><?= I18n::get('anniversary') ?></option>
                            <option value="Natal"><?= I18n::get('christmas') ?></option>
                            <option value="Lua de Mel"><?= I18n::get('honeymoon') ?></option>
                            <option value="Outro"><?= I18n::get('other') ?></option>
                        </select>

                        <label for="descricao-decoracao" style="margin-top: 10px;">
                            <i class="fas fa-pencil-alt"></i> <?= I18n::get('decoration_details') ?>
                        </label>
                        <textarea id="descricao-decoracao" name="descricao_decoracao" class="form-control" rows="3" placeholder="<?= I18n::get('example_decoration') ?>"><?= isset($_POST['descricao_decoracao']) ? htmlspecialchars($_POST['descricao_decoracao']) : '' ?></textarea>
                    </div>
                </div>
                <div class="servico-option">
                    <input type="checkbox" id="limpeza" name="servicos[]" value="limpeza" 
                           <?= (isset($_POST['servicos']) && in_array('limpeza', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="limpeza">
                        <?= I18n::get('daily_cleaning') ?>
                        <div class="servico-detalhes">€15 (<?= I18n::get('price_per_night') ?>)</div>
                    </label>
                </div>
                
                <div class="servico-option">
                    <input type="checkbox" id="cesto" name="servicos[]" value="cesto" 
                           <?= (isset($_POST['servicos']) && in_array('cesto', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="cesto">
                        <?= I18n::get('welcome_basket') ?>
                        <div class="servico-detalhes">€10 (<?= I18n::get('single_price') ?>)</div>
                    </label>
                </div>
            <?php endif; ?>
            
            <div class="preco-total">
                <?= I18n::get('total_price') ?>: €<span id="preco-total"><?= $preco_total ?></span>
            </div>
            
            <div class="form-actions">
                <a href="pagina1.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <?= I18n::get('back') ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> <?= I18n::get('go_to_payment') ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuração do chatbot
            const chatbotButton = document.getElementById('chatbotButton');
            const chatbotBox = document.getElementById('chatbotBox');
            const chatbotClose = document.getElementById('chatbotClose');
            const chatbotInput = document.getElementById('chatbotInput');
            const chatbotSend = document.getElementById('chatbotSend');
            const chatbotMessages = document.getElementById('chatbotMessages');
            const suggestionButtons = document.querySelectorAll('.suggestion-button');

            // Mostrar chatbot box
            if (chatbotButton) {
                chatbotButton.addEventListener('click', function() {
                    chatbotBox.style.display = 'flex';
                    chatbotButton.style.display = 'none';
                });
            }

            // Fechar chatbot box
            if (chatbotClose) {
                chatbotClose.addEventListener('click', function() {
                    chatbotBox.style.display = 'none';
                    chatbotButton.style.display = 'flex';
                });
            }

            // Enviar mensagem ao pressionar Enter
            if (chatbotInput) {
                chatbotInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });
            }

            // Enviar mensagem ao clicar no botão
            if (chatbotSend) {
                chatbotSend.addEventListener('click', sendMessage);
            }

            // Botões de sugestão
            if (suggestionButtons) {
                suggestionButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const text = this.textContent.trim();
                        chatbotInput.value = text;
                        sendMessage();
                    });
                });
            }

            // Sincroniza os selects de país
            const paisRegiaoSelect = document.getElementById('pais_regiao');
            const codigoPaisSelect = document.getElementById('codigo_pais');
            
            if (paisRegiaoSelect && codigoPaisSelect) {
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
            }
            
            // Validação em tempo real
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', validarEmail);
            }
            
            const telefoneInput = document.getElementById('telefone');
            const paisRegiaoInput = document.getElementById('pais_regiao');
            if (telefoneInput && paisRegiaoInput) {
                telefoneInput.addEventListener('input', validarTelefone);
                paisRegiaoInput.addEventListener('change', validarTelefone);
            }
            
            // Mostra/oculta o campo de descrição da decoração ao carregar a página
            const decoracaoCheckbox = document.getElementById('decoracao');
            const descricaoContainer = document.getElementById('descricao-decoracao-container');
            
            if (decoracaoCheckbox && descricaoContainer) {
                decoracaoCheckbox.addEventListener('change', function() {
                    descricaoContainer.style.display = this.checked ? 'block' : 'none';
                    atualizarPreco();
                });
                
                // Verifica se já estava marcado ao carregar a página
                if (decoracaoCheckbox.checked) {
                    descricaoContainer.style.display = 'block';
                }
            }
            
            // Configura os event listeners para os checkboxes de serviços
            const servicosCheckboxes = document.querySelectorAll('input[name="servicos[]"]');
            if (servicosCheckboxes) {
                servicosCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', atualizarPreco);
                });
            }
            
            // Atualiza o preço total ao carregar a página
            atualizarPreco();
        });
        
        function validarEmail() {
            const email = document.getElementById('email').value;
            const erroEmail = document.getElementById('erro-email');
            
            if (!email) {
                if (erroEmail) erroEmail.style.display = 'none';
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                if (erroEmail) {
                    erroEmail.style.display = 'block';
                    erroEmail.innerHTML = '<i class="fas fa-exclamation-circle"></i> <?= I18n::get("invalid_email") ?>';
                }
            } else {
                if (erroEmail) erroEmail.style.display = 'none';
            }
        }
        
        function validarTelefone() {
            const telefone = document.getElementById('telefone').value;
            const pais = document.getElementById('pais_regiao').value;
            const erroTelefone = document.getElementById('erro-telefone');
            
            if (!pais || !telefone) {
                if (erroTelefone) erroTelefone.style.display = 'none';
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
                if (erroTelefone) {
                    erroTelefone.style.display = 'block';
                    erroTelefone.innerHTML = '<i class="fas fa-exclamation-circle"></i> <?= I18n::get("invalid_phone") ?>';
                }
            } else {
                if (erroTelefone) erroTelefone.style.display = 'none';
            }
        }
        
        function atualizarPreco() {
            const precoBase = 120 * <?= $num_noites ?>;
            let precoTotal = precoBase;
            
            // Verifica se os elementos existem antes de tentar acessá-los
            const servicosCheckboxes = document.querySelectorAll('input[name="servicos[]"]:checked');
            
            if (servicosCheckboxes) {
                servicosCheckboxes.forEach(servico => {
                    switch (servico.value) {
                        case 'limpeza':
                            precoTotal += 15 * <?= $num_noites ?>;
                            break;
                        case 'decoracao':
                            precoTotal += 130;
                            break;
                        case 'cesto':
                            precoTotal += 10;
                            break;
                    }
                });
            }
            
            const precoTotalElement = document.getElementById('preco-total');
            if (precoTotalElement) {
                precoTotalElement.textContent = precoTotal;
            }
        }
        
        function sendMessage() {
            // Implementação da função sendMessage para o chatbot
            // (mantida como estava no código original)
        }
    </script>
    <?php require_once 'footer.php'; ?>
</body>
</html>