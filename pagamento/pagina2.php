<?php
session_start(); 
require_once 'i18n.php';
require_once '../conexao.php';
$page_title = I18n::get('personal_information');
if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr','es'])) {
    I18n::setLanguage($_GET['lang']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}
// Verificar oferta e bloquear serviços adicionais
$oferta_ativa = isset($_SESSION['codigo_oferta']) && !empty($_SESSION['codigo_oferta']);
$id_hospede = $_SESSION['id'];
$query_hospede = "SELECT H_nome, H_email, H_telefone, H_documento_ident, H_pais FROM hospedes WHERE H_id_hospede = ?";
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
    $pais_padrao = $hospede['H_pais'] ?? 'PT';
} else {
    header('Location: pagina1.php');
    exit();
}
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
$paises = [
    "PT" => ["nome" => "Portugal", "codigo" => "+351", "regex" => "/^[1-9]\\d{8}$/", "exemplo" => "912345678"],
    "ES" => ["nome" => "Espanha", "codigo" => "+34", "regex" => "/^[1-9]\\d{8}$/", "exemplo" => "612345678"],
    "FR" => ["nome" => "França", "codigo" => "+33", "regex" => "/^[1-9]\\d{8}$/", "exemplo" => "612345678"],
    "BR" => ["nome" => "Brasil", "codigo" => "+55", "regex" => "/^[1-9]\\d{9,10}$/", "exemplo" => "11999999999"],
    "US" => ["nome" => "Estados Unidos", "codigo" => "+1", "regex" => "/^[2-9]\\d{9}$/", "exemplo" => "2015550123"],
    "DE" => ["nome" => "Alemanha", "codigo" => "+49", "regex" => "/^[1-9]\\d{9,10}$/", "exemplo" => "15123456789"],
    "IT" => ["nome" => "Itália", "codigo" => "+39", "regex" => "/^[1-9]\\d{8,9}$/", "exemplo" => "3123456789"],
    "GB" => ["nome" => "Reino Unido", "codigo" => "+44", "regex" => "/^[1-9]\\d{9}$/", "exemplo" => "7123456789"],
];
$documentos = [
    'PT' => ['regex' => '/^[1-9]\d{8}$/', 'exemplo' => '123456789', 'descricao' => '9 dígitos (sem letras)'],
    'ES' => ['regex' => '/^[0-9]{8}[A-Za-z]$/', 'exemplo' => '12345678Z', 'descricao' => '8 dígitos + 1 letra'],
    'FR' => ['regex' => '/^[0-9]{13}$/', 'exemplo' => '1234567890123', 'descricao' => '13 dígitos'],
    'BR' => ['regex' => '/^[0-9]{11}$/', 'exemplo' => '12345678901', 'descricao' => '11 dígitos (CPF)'],
    'US' => ['regex' => '/^[0-9]{9}$/', 'exemplo' => '123456789', 'descricao' => '9 dígitos (SSN)'],
    'GB' => ['regex' => '/^[A-Z]{2}[0-9]{6}[A-Z]$/', 'exemplo' => 'QQ123456C', 'descricao' => '2 letras + 6 dígitos + 1 letra'],
    'DE' => ['regex' => '/^[0-9]{11}$/', 'exemplo' => '12345678901', 'descricao' => '11 dígitos'],
    'IT' => ['regex' => '/^[A-Za-z0-9]{16}$/', 'exemplo' => 'RSSMRA85M01H501Z', 'descricao' => '16 caracteres alfanuméricos'],
    'DEFAULT' => ['regex' => '/^.{4,20}$/', 'exemplo' => 'ABC12345', 'descricao' => '4-20 caracteres'],
];
$checkin = new DateTime($_SESSION['checkin']);
$checkout = new DateTime($_SESSION['checkout']);
$num_noites = $checkin->diff($checkout)->days;
$mensagem_erro = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_completo = isset($_POST['nome_completo']) ? trim(htmlspecialchars($_POST['nome_completo'])) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $codigo_pais = isset($_POST['codigo_pais']) ? trim($_POST['codigo_pais']) : '';
    $telefone_completo = $codigo_pais . ' ' . $telefone;
    $pais_regiao = isset($_POST['pais_regiao']) ? trim($_POST['pais_regiao']) : '';
    $confirmacao_digital = isset($_POST['confirmacao']) ? 1 : 0;
    $cancelamento = isset($_POST['cancelamento']) ? 1 : 0;
    $descricao_decoracao = isset($_POST['descricao_decoracao']) ? trim(htmlspecialchars($_POST['descricao_decoracao'])) : '';
    $erros = [];
    if (empty($nome_completo) || strlen($nome_completo) < 2) {
        $erros[] = I18n::get('invalid_full_name');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = I18n::get('invalid_email');
    }
    $doc_regex = $documentos[$pais_regiao]['regex'] ?? $documentos['DEFAULT']['regex'];
    if (!preg_match($doc_regex, $documento)) {
        $doc_exemplo = $documentos[$pais_regiao]['exemplo'] ?? $documentos['DEFAULT']['exemplo'];
        $doc_descricao = $documentos[$pais_regiao]['descricao'] ?? $documentos['DEFAULT']['descricao'];
        $erros[] = I18n::get('invalid_document') . " " . I18n::get('expected_format') . ": <b>$doc_exemplo</b> ($doc_descricao)";
    }
    if (empty($pais_regiao)) {
        $erros[] = I18n::get('select_country_error');
    }
    if (!empty($pais_regiao) && isset($paises[$pais_regiao])) {
        $regex = $paises[$pais_regiao]['regex'];
        if (!preg_match($regex, $telefone)) {
            $tel_exemplo = $paises[$pais_regiao]['exemplo'];
            $erros[] = I18n::get('invalid_phone') . " " . I18n::get('expected_format') . ": <b>{$paises[$pais_regiao]['codigo']} $tel_exemplo</b>";
        }
    } else {
        $erros[] = I18n::get('invalid_phone_format');
    }
    if (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos']) && empty($descricao_decoracao)) {
        $erros[] = I18n::get('please_describe_theme');
    }
    if (empty($erros)) {
        $_SESSION['nome_completo'] = $nome_completo;
        $_SESSION['email'] = $email;
        $_SESSION['documento'] = $documento;
        $_SESSION['telefone'] = $telefone_completo;
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
        $sql = "UPDATE hospedes SET H_nome = ?, H_telefone = ?, H_pais = ?, H_documento_ident = ? WHERE H_id_hospede = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssssi", $nome_completo, $telefone_completo, $pais_regiao, $documento, $id_hospede);
        $stmt->execute();
        $stmt->close();
        header('Location: pagina3.php');
        exit();
    } else {
        $mensagem_erro = implode("<br>", $erros);
    }
}
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
$pais_regiao_selecionado = '';
if (isset($_POST['pais_regiao'])) {
    $pais_regiao_selecionado = $_POST['pais_regiao'];
} elseif (isset($hospede['H_pais']) && array_key_exists($hospede['H_pais'], $paises)) {
    $pais_regiao_selecionado = $hospede['H_pais'];
} else {
    $pais_regiao_selecionado = 'PT';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css ">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="../includes/chatbot.css">
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
</head>
<body>
    <div class="container">
        <h1 class="fade-in"><?= I18n::get('personal_information') ?></h1>
        <div class="progress-steps">
            <div class="progress-step completed"><span><?= I18n::get('dates') ?></span></div>
            <div class="progress-step active"><span><?= I18n::get('personal_data') ?></span></div>
            <div class="progress-step"><span><?= I18n::get('payment') ?></span></div>
            <div class="progress-step"><span><?= I18n::get('confirmation') ?></span></div>
        </div>
        <?php if (!empty($mensagem_erro)): ?>
            <div class="error-message" style="display: block;"><i class="fas fa-exclamation-circle"></i> <?= $mensagem_erro ?></div>
        <?php endif; ?>
        <div class="resumo-reserva">
            <h3><i class="fas fa-calendar-check"></i> <?= I18n::get('reservation_summary') ?></h3>
            <div class="resumo-item"><span><?= I18n::get('dates') ?>:</span><span><?= $checkin->format(I18n::get('date_format')) ?> - <?= $checkout->format(I18n::get('date_format')) ?></span></div>
            <div class="resumo-item"><span><?= I18n::get('nights') ?>:</span><span><?= $num_noites ?></span></div>
            <div class="resumo-item"><span><?= I18n::get('guests') ?>:</span><span><?= $_SESSION['num_hospedes'] ?> <?= $_SESSION['num_hospedes'] == 1 ? I18n::get('person') : I18n::get('people') ?></span></div>
        </div>
        <form action="pagina2.php" method="POST" id="dadosPessoaisForm" class="fade-in">
            <h3><i class="fas fa-user-circle"></i> <?= I18n::get('personal_data') ?></h3>
            <div class="form-group">
                <label for="nome_completo"><i class="fas fa-user"></i> <?= I18n::get('full_name') ?></label>
                <input type="text" id="nome_completo" name="nome_completo" class="form-control" value="<?= isset($_POST['nome_completo']) ? htmlspecialchars($_POST['nome_completo']) : htmlspecialchars($nome_padrao) ?>" required minlength="2" placeholder="<?= I18n::get('required_field') ?>">
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> <?= I18n::get('email') ?></label>
                <input type="email" id="email" name="email" class="form-control" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($email_padrao) ?>" required placeholder="<?= I18n::get('required_field') ?>">
                <div id="erro-email" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="pais_regiao"><i class="fas fa-globe"></i> <?= I18n::get('country_region') ?></label>
                <select id="pais_regiao" name="pais_regiao" class="form-control" required>
                    <option value=""><?= I18n::get('select_country') ?></option>
                    <?php foreach ($paises as $codigo => $dados): ?>
                        <option value="<?= $codigo ?>" <?= ($pais_regiao_selecionado == $codigo) ? 'selected' : '' ?>><?= $dados['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="documento"><i class="fas fa-id-card"></i> <?= I18n::get('civil_identification') ?></label>
                <input type="text" id="documento" name="documento" class="form-control" value="<?= isset($_POST['documento']) ? htmlspecialchars($_POST['documento']) : htmlspecialchars($documento_padrao) ?>" required maxlength="20" placeholder="<?= isset($documentos[$pais_regiao_selecionado]['exemplo']) ? $documentos[$pais_regiao_selecionado]['exemplo'] : I18n::get('required_field') ?>">
                <div id="formato-documento" class="formato-info"><?= isset($documentos[$pais_regiao_selecionado]['descricao']) ? 'Formato: ' . $documentos[$pais_regiao_selecionado]['descricao'] : '' ?></div>
                <div id="erro-documento" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="telefone"><i class="fas fa-phone"></i> <?= I18n::get('phone') ?></label>
                <div class="input-group">
                    <select id="codigo_pais" class="form-control" style="flex: 1;" name="codigo_pais">
                        <?php foreach ($paises as $codigo => $dados): ?>
                            <option value="<?= $dados['codigo'] ?>" data-pais="<?= $codigo ?>" <?= ($pais_regiao_selecionado == $codigo) ? 'selected' : '' ?>><?= $dados['codigo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="telefone" name="telefone" class="form-control" style="flex: 3;" value="<?= isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : htmlspecialchars($telefone_padrao ?? '') ?>" required maxlength="20" placeholder="<?= isset($paises[$pais_regiao_selecionado]['exemplo']) ? $paises[$pais_regiao_selecionado]['exemplo'] : I18n::get('required_field') ?>">
                </div>
                <div id="formato-telefone" class="formato-info">Formato: <?= $paises[$pais_regiao_selecionado]['codigo'] ?? '+351' ?> <?= $paises[$pais_regiao_selecionado]['exemplo'] ?? '912345678' ?></div>
                <div id="erro-telefone" class="error-message"></div>
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="confirmacao" name="confirmacao" value="1" <?= (isset($_POST['confirmacao']) && $_POST['confirmacao'] == 1) ? 'checked' : '' ?>><i class="fas fa-check-circle"></i> Entendo que vou receber uma confirmação digital</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="cancelamento" name="cancelamento" value="1" <?= (isset($_POST['cancelamento']) && $_POST['cancelamento'] == 1) ? 'checked' : '' ?> required><i class="fas fa-info-circle"></i> <?= I18n::get('cancellation_policy') ?></label>
            </div>
            <h3><i class="fas fa-concierge-bell"></i> <?= I18n::get('additional_services') ?></h3>
            <?php if ($oferta_ativa): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> <?= I18n::get('promo_code_active') ?></div>
            <?php else: ?>
                <div class="servico-option">
                    <input type="checkbox" id="decoracao" name="servicos[]" value="decoracao" <?= (isset($_POST['servicos']) && in_array('decoracao', $_POST['servicos'])) ? 'checked' : '' ?> onchange="atualizarPreco()">
                    <label for="decoracao"><?= I18n::get('theme_decoration') ?><div class="servico-detalhes">€130 (<?= I18n::get('single_price') ?>)</div></label>
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
                        <label for="descricao-decoracao" style="margin-top: 10px;"><i class="fas fa-pencil-alt"></i> <?= I18n::get('decoration_details') ?></label>
                        <textarea id="descricao-decoracao" name="descricao_decoracao" class="form-control" rows="3" placeholder="<?= I18n::get('example_decoration') ?>"><?= isset($_POST['descricao_decoracao']) ? htmlspecialchars($_POST['descricao_decoracao']) : '' ?></textarea>
                    </div>
                </div>
                <div class="servico-option">
                    <input type="checkbox" id="limpeza" name="servicos[]" value="limpeza" <?= (isset($_POST['servicos']) && in_array('limpeza', $_POST['servicos'])) ? 'checked' : '' ?> onchange="atualizarPreco()">
                    <label for="limpeza"><?= I18n::get('daily_cleaning') ?><div class="servico-detalhes">€15 (<?= I18n::get('price_per_night') ?>)</div></label>
                </div>
                <div class="servico-option">
                    <input type="checkbox" id="cesto" name="servicos[]" value="cesto" <?= (isset($_POST['servicos']) && in_array('cesto', $_POST['servicos'])) ? 'checked' : '' ?> onchange="atualizarPreco()">
                    <label for="cesto"><?= I18n::get('welcome_basket') ?><div class="servico-detalhes">€10 (<?= I18n::get('single_price') ?>)</div></label>
                </div>
            <?php endif; ?>
            <div class="preco-total"><?= I18n::get('total_price') ?>: €<span id="preco-total"><?= $preco_total ?></span></div>
            <div class="form-actions">
                <a href="pagina1.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <?= I18n::get('back') ?></a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-credit-card"></i> <?= I18n::get('go_to_payment') ?></button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paisRegiaoSelect = document.getElementById('pais_regiao');
            const codigoPaisSelect = document.getElementById('codigo_pais');
            const telefoneInput = document.getElementById('telefone');
            const documentoInput = document.getElementById('documento');
            const formatoDocumentoDiv = document.getElementById('formato-documento');
            const formatoTelefoneDiv = document.getElementById('formato-telefone');

            const paises = {
                <?php foreach ($paises as $codigo => $dados): ?>
                "<?= $codigo ?>": {
                    codigo: "<?= $dados['codigo'] ?>",
                    exemplo: "<?= $dados['exemplo'] ?? '' ?>",
                    regex: "<?= str_replace(['/', '^', '$'], '', $dados['regex']) ?>"
                },
                <?php endforeach; ?>
            };

            const documentos = {
                <?php foreach ($documentos as $codigo => $dados): ?>
                "<?= $codigo ?>": {
                    exemplo: "<?= $dados['exemplo'] ?>",
                    descricao: "<?= $dados['descricao'] ?>",
                    regex: "<?= str_replace(['/', '^', '$'], '', $dados['regex']) ?>"
                },
                <?php endforeach; ?>
            };

            function atualizarCamposPorPais(pais) {
                if (!pais) return;
                if (paises[pais] && codigoPaisSelect) {
                    for (let i = 0; i < codigoPaisSelect.options.length; i++) {
                        if (codigoPaisSelect.options[i].value === paises[pais].codigo) {
                            codigoPaisSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
                if (telefoneInput && paises[pais]) {
                    telefoneInput.placeholder = paises[pais].exemplo || '<?= I18n::get('required_field') ?>';
                    if (formatoTelefoneDiv) {
                        formatoTelefoneDiv.textContent = 'Formato: ' + paises[pais].codigo + ' ' + (paises[pais].exemplo || '');
                    }
                }
                if (documentoInput) {
                    const docInfo = documentos[pais] || documentos['DEFAULT'];
                    documentoInput.placeholder = docInfo.exemplo || '<?= I18n::get('required_field') ?>';
                    if (formatoDocumentoDiv) {
                        formatoDocumentoDiv.textContent = 'Formato: ' + docInfo.descricao;
                    }
                }
            }

            if (paisRegiaoSelect) {
                paisRegiaoSelect.addEventListener('change', function () {
                    atualizarCamposPorPais(this.value);
                });
                atualizarCamposPorPais(paisRegiaoSelect.value);
            }

            if (codigoPaisSelect) {
                codigoPaisSelect.addEventListener('change', function () {
                    const codigo = this.value;
                    for (const [pais, dados] of Object.entries(paises)) {
                        if (dados.codigo === codigo) {
                            if (paisRegiaoSelect) {
                                paisRegiaoSelect.value = pais;
                                atualizarCamposPorPais(pais);
                            }
                            break;
                        }
                    }
                });
            }

            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', validarEmail);
            }
            function validarEmail() {
                const email = emailInput.value;
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

            function validarDocumento() {
                const pais = paisRegiaoSelect ? paisRegiaoSelect.value : 'DEFAULT';
                const docInfo = documentos[pais] || documentos['DEFAULT'];
                const documento = documentoInput.value;
                const erroDoc = document.getElementById('erro-documento');
                if (!documento) {
                    erroDoc.style.display = 'none';
                    erroDoc.innerHTML = '';
                    return true;
                }
                try {
                    const regex = new RegExp('^' + docInfo.regex + '$');
                    if (!regex.test(documento)) {
                        erroDoc.style.display = 'block';
                        erroDoc.innerHTML = '<i class="fas fa-exclamation-circle"></i> Documento inválido. Formato esperado: ' + docInfo.descricao;
                        return false;
                    } else {
                        erroDoc.style.display = 'none';
                        erroDoc.innerHTML = '';
                        return true;
                    }
                } catch (e) {
                    erroDoc.style.display = 'none';
                    erroDoc.innerHTML = '';
                    return true;
                }
            }
            if (documentoInput) {
                documentoInput.addEventListener('input', validarDocumento);
                documentoInput.addEventListener('blur', validarDocumento);
            }

            function validarTelefone() {
                const pais = paisRegiaoSelect ? paisRegiaoSelect.value : 'PT';
                const telefone = telefoneInput.value;
                const erroTel = document.getElementById('erro-telefone');
                if (!telefone) {
                    erroTel.style.display = 'none';
                    erroTel.innerHTML = '';
                    return true;
                }
                if (paises[pais]) {
                    try {
                        const regex = new RegExp('^' + paises[pais].regex + '$');
                        if (!regex.test(telefone)) {
                            erroTel.style.display = 'block';
                            erroTel.innerHTML = '<i class="fas fa-exclamation-circle"></i> Telefone inválido. Formato esperado: ' + paises[pais].codigo + ' ' + (paises[pais].exemplo || '');
                            return false;
                        } else {
                            erroTel.style.display = 'none';
                            erroTel.innerHTML = '';
                            return true;
                        }
                    } catch (e) {
                        erroTel.style.display = 'none';
                        erroTel.innerHTML = '';
                        return true;
                    }
                }
                erroTel.style.display = 'none';
                erroTel.innerHTML = '';
                return true;
            }
            if (telefoneInput) {
                telefoneInput.addEventListener('input', validarTelefone);
                telefoneInput.addEventListener('blur', validarTelefone);
            }

            const decoracaoCheckbox = document.getElementById('decoracao');
            const descricaoContainer = document.getElementById('descricao-decoracao-container');
            if (decoracaoCheckbox && descricaoContainer) {
                decoracaoCheckbox.addEventListener('change', function () {
                    descricaoContainer.style.display = this.checked ? 'block' : 'none';
                    atualizarPreco();
                });
                if (decoracaoCheckbox.checked) {
                    descricaoContainer.style.display = 'block';
                }
            }

            function atualizarPreco() {
                const precoBase = 120 * <?= $num_noites ?>;
                let precoTotal = precoBase;
                const servicosCheckboxes = document.querySelectorAll('input[name="servicos[]"]:checked');
                servicosCheckboxes.forEach(servico => {
                    switch (servico.value) {
                        case 'limpeza': precoTotal += 15 * <?= $num_noites ?>; break;
                        case 'decoracao': precoTotal += 130; break;
                        case 'cesto': precoTotal += 10; break;
                    }
                });
                const precoTotalElement = document.getElementById('preco-total');
                if (precoTotalElement) {
                    precoTotalElement.textContent = precoTotal;
                }
            }

            const servicosCheckboxes = document.querySelectorAll('input[name="servicos[]"]');
            if (servicosCheckboxes) {
                servicosCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', atualizarPreco);
                });
            }
            atualizarPreco();

            const form = document.getElementById('dadosPessoaisForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let valido = true;
                    if (!validarDocumento()) {
                        documentoInput.focus();
                        valido = false;
                    }
                    if (!validarTelefone()) {
                        telefoneInput.focus();
                        valido = false;
                    }
                    if (!valido) {
                        e.preventDefault();
                    }
                });
            }

            function esconderErroGeral() {
                const erroGeral = document.querySelector('.error-message');
                if (erroGeral) erroGeral.style.display = 'none';
            }
            if (documentoInput) documentoInput.addEventListener('input', esconderErroGeral);
            if (telefoneInput) telefoneInput.addEventListener('input', esconderErroGeral);
        });

        function sendMessage() {
            const input = document.getElementById('chatbotInput');
            const messages = document.getElementById('chatbotMessages');
            if (input && input.value.trim() !== '' && messages) {
                const userMessage = document.createElement('div');
                userMessage.className = 'chatbot-message user-message';
                userMessage.textContent = input.value;
                messages.appendChild(userMessage);
                setTimeout(() => {
                    const botMessage = document.createElement('div');
                    botMessage.className = 'chatbot-message bot-message';
                    botMessage.textContent = 'Obrigado pela sua mensagem. Como posso ajudar?';
                    messages.appendChild(botMessage);
                    messages.scrollTop = messages.scrollHeight;
                }, 500);
                input.value = '';
                messages.scrollTop = messages.scrollHeight;
            }
        }
    </script>
    <?php require_once 'footer.php'; ?>
</body>
</html>