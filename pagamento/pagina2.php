<?php
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// Configurações
define('SITE_NAME', 'Quinta das Flores');
define('PRIMARY_COLOR', '#6A0DAD');
define('SECONDARY_COLOR', '#A56EFF');
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

// Calcula número de noites
$checkin = new DateTime($_SESSION['checkin']);
$checkout = new DateTime($_SESSION['checkout']);
$num_noites = $checkin->diff($checkout)->days;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = isset($_POST['nome']) ? trim(htmlspecialchars($_POST['nome'])) : '';
    $apelido = isset($_POST['apelido']) ? trim(htmlspecialchars($_POST['apelido'])) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $pais_regiao = isset($_POST['pais_regiao']) ? trim($_POST['pais_regiao']) : '';
    $confirmacao_digital = isset($_POST['confirmacao']) ? 1 : 0;
    $cancelamento = isset($_POST['cancelamento']) ? 1 : 0;
    $descricao_decoracao = isset($_POST['descricao_decoracao']) ? trim(htmlspecialchars($_POST['descricao_decoracao'])) : '';

    // Armazenar serviços adicionais na sessão
    if (isset($_POST['servicos'])) {
        $_SESSION['servicos'] = $_POST['servicos'];
        // Armazena também a descrição da decoração se o serviço foi selecionado
        if (in_array('decoracao', $_POST['servicos'])) {
            $_SESSION['descricao_decoracao'] = $descricao_decoracao;
        }
    } else {
        $_SESSION['servicos'] = [];
    }

    // Validação dos campos
    $erros = [];
    if (empty($nome) || strlen($nome) < 2) {
        $erros[] = "Nome inválido.";
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
    // Validação da descrição da decoração se o serviço foi selecionado
    if (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos']) && empty($descricao_decoracao)) {
        $erros[] = "Por favor, descreva o tema desejado para a decoração.";
    }

    if (empty($erros)) {
        // Armazena os dados na sessão
        $_SESSION['nome'] = $nome;
        $_SESSION['apelido'] = $apelido;
        $_SESSION['email'] = $email;
        $_SESSION['documento'] = $documento;
        $_SESSION['telefone'] = $telefone;
        $_SESSION['pais_regiao'] = $pais_regiao;
        $_SESSION['confirmacao_digital'] = $confirmacao_digital;
        $_SESSION['cancelamento'] = $cancelamento;

        // Redireciona para a próxima página
        header('Location: pagina3.php');
        exit();
    } else {
        $mensagem_erro = implode("<br>", $erros);
    }
}

// Calcula preço base
$preco_base = 120 * $num_noites;
$preco_total = $preco_base;

// Calcula serviços adicionais se existirem
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
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações Pessoais - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
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
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group select {
            flex: 1;
        }
        
        .input-group input {
            flex: 3;
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
        
        .servicos-container {
            margin: 20px 0;
        }
        
        .servico-option {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }
        
        .servico-option:hover {
            background-color: #f0f0f0;
        }
        
        .servico-option input {
            margin-right: 10px;
        }
        
        .servico-detalhes {
            margin-left: 30px;
            font-size: 0.9em;
            color: #666;
        }
        
        .preco-total {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--primary-color);
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: right;
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
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .input-group {
                flex-direction: column;
            }
            
            .input-group select,
            .input-group input {
                width: 100%;
            }
        }
    </style>
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
                <label for="nome"><i class="fas fa-user"></i> Nome</label>
                <input type="text" id="nome" name="nome" class="form-control" 
                       value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" 
                       required minlength="2">
            </div>
            
            <div class="form-group">
                <label for="apelido"><i class="fas fa-user"></i> Apelido</label>
                <input type="text" id="apelido" name="apelido" class="form-control" 
                       value="<?= isset($_POST['apelido']) ? htmlspecialchars($_POST['apelido']) : '' ?>" 
                       required minlength="2">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                       required>
                <div id="erro-email" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="documento"><i class="fas fa-id-card"></i> Identificação Civil (9 dígitos)</label>
                <input type="text" id="documento" name="documento" class="form-control" 
                       value="<?= isset($_POST['documento']) ? htmlspecialchars($_POST['documento']) : '' ?>" 
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
            <div class="servicos-container">
                <div class="servico-option">
                    <input type="checkbox" id="pequeno-almoco" name="servicos[]" value="pequeno-almoco" 
                           <?= (isset($_POST['servicos']) && in_array('pequeno-almoco', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="pequeno-almoco">
                        Pequeno-Almoço
                        <div class="servico-detalhes">€15 por noite</div>
                    </label>
                </div>
                
                <div class="servico-option">
                    <input type="checkbox" id="decoracao" name="servicos[]" value="decoracao" 
                           <?= (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="decoracao">
                        Decoração Temática
                        <div class="servico-detalhes">€130 (valor único)</div>
                    </label>
                    <div id="descricao-decoracao-container" style="display: none; margin-top: 10px;">
                        <label for="descricao-decoracao"><i class="fas fa-pencil-alt"></i> Descreva o tema desejado (ex: Natal, Lua de Mel):</label>
                        <textarea id="descricao-decoracao" name="descricao_decoracao" class="form-control" rows="2"><?= isset($_POST['descricao_decoracao']) ? htmlspecialchars($_POST['descricao_decoracao']) : '' ?></textarea>
                    </div>
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
                
                <div class="servico-option">
                    <input type="checkbox" id="jantar" name="servicos[]" value="jantar" 
                           <?= (isset($_POST['servicos']) && in_array('jantar', $_POST['servicos'])) ? 'checked' : '' ?>
                           onchange="atualizarPreco()">
                    <label for="jantar">
                        Jantar
                        <div class="servico-detalhes">€15 por noite</div>
                    </label>
                </div>
            </div>
            
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
</body>
</html>