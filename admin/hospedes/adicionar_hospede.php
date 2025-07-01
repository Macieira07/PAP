<?php
session_start();
require '../../conexao.php'; 
require_once '../email.php';

function gerarCodigo() {
    return rand(100000, 999999);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function ocultarEmail($email) {
    [$user, $dominio] = explode('@', $email);
    return substr($user, 0, 2) . str_repeat('*', max(0, strlen($user)-2)) . '@' . $dominio;
}

function validarNIF($nif) {
    if (!preg_match('/^[0-9]{9}$/', $nif)) return false;
    $total = 0;
    for ($i = 0; $i < 8; $i++) {
        $total += intval($nif[$i]) * (9 - $i);
    }
    $resto = $total % 11;
    $dig = ($resto < 2) ? 0 : 11 - $resto;
    return intval($nif[8]) === $dig;
}

// Inicializa variáveis para mensagens
$erro = '';
$erroEmail = '';
$erroCodigo = '';
$erroTelefone = '';
$erroNIF = '';
$erroTermos = '';
$sucesso = '';
$sucessoEmail = '';

$dadosForm = [
    'nome'=>'', 'email'=>'', 'telefone'=>'', 'pais_codigo'=>'+351',
    'documento'=>'', 'morada'=>'', 'aceitou'=>'Não'
];
foreach($dadosForm as $c => $v) {
    if(isset($_POST[$c])) {
        $dadosForm[$c] = htmlspecialchars(trim($_POST[$c]));
    }
}

// Inicializa tempo_ultimo_codigo na sessão se não existir
if(!isset($_SESSION['tempo_ultimo_codigo'])) {
    $_SESSION['tempo_ultimo_codigo'] = 0;
}

$cooldownSegundos = 60; // cooldown de 60 segundos

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['enviar_codigo'])) {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $t = time();

    if(!validarEmail($email)) {
        $erroEmail = 'Email inválido.';
    } elseif ($t - $_SESSION['tempo_ultimo_codigo'] < $cooldownSegundos) {
        $erroEmail = 'Aguarde ' . ($cooldownSegundos - ($t - $_SESSION['tempo_ultimo_codigo'])) . ' segundos para novo código.';
    } else {
        $_SESSION['codigo_gerado']   = gerarCodigo();
        $_SESSION['email_gerado']    = strtolower($email);
        $_SESSION['tempo_ultimo_codigo'] = $t;

        $res = enviarEmailCodigo($email, $nome, $_SESSION['codigo_gerado']);
        if($res === true) {
            $sucessoEmail = "Código enviado para: " . ocultarEmail($email);
        } else {
            $erroEmail = "Erro ao enviar email: $res";
        }
    }
}

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['adicionar_hospede'])) {
    $codigoForm = trim($_POST['codigo'] ?? '');
    $emailForm = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $emailSess  = $_SESSION['email_gerado'] ?? '';
    $codSess    = $_SESSION['codigo_gerado'] ?? '';

    if(!preg_match('/^\d{6}$/', $codigoForm)) {
        $erroCodigo = "Código inválido. Deve ter 6 dígitos numéricos.";
    } elseif($codigoForm !== (string)$codSess || $emailForm !== $emailSess) {
        $erroCodigo = "Código ou email incorretos.";
    } else {
        $telefone = trim($_POST['telefone'] ?? '');
        $paisCodigo = $_POST['pais_codigo'] ?? '';

        $telDigits = preg_replace('/\D/', '', $telefone);

        // Remove prefixo internacional se estiver duplicado no telefone
        if ($paisCodigo === '+351' && strpos($telDigits, '351') === 0) {
            $telDigits = substr($telDigits, 3);
        }
        if ($paisCodigo === '+34' && strpos($telDigits, '34') === 0) {
            $telDigits = substr($telDigits, 2);
        }
        if ($paisCodigo === '+33' && strpos($telDigits, '33') === 0) {
            $telDigits = substr($telDigits, 2);
        }
        if ($paisCodigo === '+1' && strpos($telDigits, '1') === 0) {
            $telDigits = substr($telDigits, 1);
        }

        $telefoneValido = false;
        switch ($paisCodigo) {
            case '+351': // Portugal: 9 dígitos, começa com 9
                $telefoneValido = preg_match('/^9\d{8}$/', $telDigits);
                break;
            case '+34': // Espanha: 9 dígitos, começa com 6 ou 7
                $telefoneValido = preg_match('/^[67]\d{8}$/', $telDigits);
                break;
            case '+33': // França: 9 dígitos, começa com 6 ou 7
                $telefoneValido = preg_match('/^[67]\d{8}$/', $telDigits);
                break;
            case '+1': // EUA: 10 dígitos
                $telefoneValido = preg_match('/^\d{10}$/', $telDigits);
                break;
            default:
                $telefoneValido = false;
        }

        if(!$telefoneValido) {
            $erroTelefone = "Telefone inválido para o país selecionado.";
        } elseif (!validarNIF($_POST['documento'])) {
            $erroNIF = "NIF inválido.";
        } elseif (!isset($_POST['aceitou'])) {
            $erroTermos = "É necessário aceitar os Termos.";
        } else {
            // Inserir no banco
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : $dadosForm['nome'];
            $email = $emailForm;
            $senhaTemp = password_hash($_SESSION['codigo_gerado'], PASSWORD_DEFAULT);
            $telefoneCompleto = $paisCodigo . $telDigits;
            $documento = $_POST['documento'];
            $morada = $dadosForm['morada'] ?: null;
            $verificado_email = 1;
            $aceitou_termos_uso = 1;
            $token_verificacao = bin2hex(random_bytes(16));
            $token_expira = date('Y-m-d H:i:s', strtotime('+1 day'));
            $notas = null;
            $avaliacao = null;
            $valor_notas = 0.00;
            $ultimo_login = null;

            $stmtCheck = $conexao->prepare("SELECT COUNT(*) FROM hospedes WHERE H_email = ?");
            $stmtCheck->bind_param("s", $email);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if($count > 0) {
                $erroEmail = "Este email já está cadastrado.";
            } else {
                $stmt = $conexao->prepare("INSERT INTO hospedes 
                (H_nome, H_email, H_senha, H_telefone, H_documento_ident, H_morada, H_verificado_email, H_aceitou_termos_uso, H_token_verificacao, H_token_expira, H_notas, H_avaliacao, H_valor_notas, H_ultimo_login)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if(!$stmt) {
                    $erro = "Erro na preparação da query: " . $conexao->error;
                } else {
                    $stmt->bind_param(
                        "ssssssisisssis",
                        $nome, $email, $senhaTemp, $telefoneCompleto, $documento, $morada,
                        $verificado_email, $aceitou_termos_uso, $token_verificacao, $token_expira,
                        $notas, $avaliacao, $valor_notas, $ultimo_login
                    );
                    if($stmt->execute()) {
                        $sucesso = "Hóspede adicionado com sucesso!";
                        // Limpa sessão após sucesso
                        unset($_SESSION['codigo_gerado'], $_SESSION['email_gerado'], $_SESSION['tempo_ultimo_codigo']);
                        $dadosForm = ['nome'=>'', 'email'=>'', 'telefone'=>'', 'pais_codigo'=>'+351', 'documento'=>'', 'morada'=>'', 'aceitou'=>'Não'];
                    } else {
                        $erro = "Erro ao adicionar hóspede: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

if(isset($_POST['cancelar_codigo'])) {
    unset($_SESSION['codigo_gerado'], $_SESSION['email_gerado'], $_SESSION['tempo_ultimo_codigo']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$tempoAtual = time();
$tempoDesdeUltimoCodigo = $tempoAtual - ($_SESSION['tempo_ultimo_codigo'] ?? 0);

$tempoRestanteCooldown = max(0, $cooldownSegundos - $tempoDesdeUltimoCodigo);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Adicionar Hóspede</title>
<link rel="stylesheet" href="../global.css">
<style>
.progress-bar {
  width: 100%;
  background: #ddd;
  border-radius: 4px;
  margin: 10px 0;
  position: relative;
  height: 25px;
  overflow: hidden;
}
.progress-bar-inner {
  height: 100%;
  width: 0%;
  background: #4caf50;
  transition: width 0.5s ease;
  text-align: center;
  color: white;
  font-weight: bold;
  line-height: 25px;
}
.message {
  margin: 10px 0;
  padding: 8px;
  border-radius: 4px;
}
.error {
  background: #f8d7da;
  color: #721c24;
}
.success {
  background: #d4edda;
  color: #155724;
}
input:invalid, select:invalid {
    border-color: #f44336;
}
input:valid, select:valid {
    border-color: #4CAF50;
}
input, select {
    transition: border 0.2s;
}
input + .icon-erro, select + .icon-erro {
    display: none;
    color: #f44336;
    margin-left: 6px;
    font-size: 18px;
    vertical-align: middle;
}
input:invalid + .icon-erro, select:invalid + .icon-erro {
    display: inline;
}
</style>
<script>
// Máscara dinâmica de telefone
function aplicarMascaraTelefone(input, pais) {
    let v = input.value.replace(/\D/g, '');
    if (pais === '+351') {
        v = v.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
    } else if (pais === '+34' || pais === '+33') {
        v = v.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
    } else if (pais === '+1') {
        v = v.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    }
    input.value = v.trim();
}

// Validação em tempo real
function validarEmail(email) {
    return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email);
}
function validarNIF(nif) {
    if (!/^\d{9}$/.test(nif)) return false;
    let total = 0;
    for (let i = 0; i < 8; i++) total += parseInt(nif[i]) * (9 - i);
    let resto = total % 11;
    let dig = (resto < 2) ? 0 : 11 - resto;
    return parseInt(nif[8]) === dig;
}
function validarTelefone(tel, pais) {
    tel = tel.replace(/\D/g, '');
    if (pais === '+351') return /^9\d{8}$/.test(tel);
    if (pais === '+34' || pais === '+33') return /^[67]\d{8}$/.test(tel);
    if (pais === '+1') return /^\d{10}$/.test(tel);
    return false;
}

function setFieldStatus(input, valido) {
    input.style.borderColor = valido ? '#4CAF50' : '#f44336';
    input.nextElementSibling && (input.nextElementSibling.style.display = valido ? 'none' : 'inline');
}

window.onload = function() {
    <?php if($tempoRestanteCooldown > 0): ?>
        startCooldown(<?php echo $tempoRestanteCooldown; ?>);
    <?php endif; ?>
    updateProgress(1);
    setTimeout(() => {
        const msgs = document.querySelectorAll('.message');
        msgs.forEach(m => m.style.display = 'none');
    }, 5000);

    // Validação em tempo real
    const emailInput = document.getElementById('email');
    const telefoneInput = document.getElementById('telefone');
    const paisInput = document.querySelector('select[name="pais_codigo"]');
    const nifInput = document.getElementById('documento');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            setFieldStatus(this, validarEmail(this.value));
        });
        emailInput.addEventListener('blur', function() {
            if (validarEmail(this.value)) {
                fetch('../verificar_email.php?email=' + encodeURIComponent(this.value))
                    .then(r => r.text())
                    .then(resp => {
                        if (resp === 'bloqueado') {
                            setFieldStatus(emailInput, false);
                            showToast('Este email está bloqueado!', 'error');
                        } else if (resp === 'existe') {
                            setFieldStatus(emailInput, false);
                            showToast('Este email já está cadastrado!', 'error');
                        }
                    });
            }
        });
    }
    if (telefoneInput && paisInput) {
        telefoneInput.addEventListener('input', function() {
            aplicarMascaraTelefone(this, paisInput.value);
            setFieldStatus(this, validarTelefone(this.value, paisInput.value));
        });
        paisInput.addEventListener('change', function() {
            aplicarMascaraTelefone(telefoneInput, this.value);
            setFieldStatus(telefoneInput, validarTelefone(telefoneInput.value, this.value));
        });
    }
    if (nifInput) {
        nifInput.addEventListener('input', function() {
            setFieldStatus(this, validarNIF(this.value));
        });
    }

    // Mostrar/ocultar código
    const codigoInput = document.getElementById('codigo');
    if (codigoInput) {
        let btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '👁';
        btn.style.marginLeft = '8px';
        btn.onclick = function(e) {
            e.preventDefault();
            codigoInput.type = codigoInput.type === 'password' ? 'text' : 'password';
        };
        codigoInput.parentNode.insertBefore(btn, codigoInput.nextSibling);
        codigoInput.type = 'password';
    }

    // Modal de termos
    const termosCheckbox = document.querySelector('input[name="aceitou"]');
    if (termosCheckbox) {
        termosCheckbox.addEventListener('click', function(e) {
            if (!document.getElementById('modal-termos')) {
                let modal = document.createElement('div');
                modal.id = 'modal-termos';
                modal.style.position = 'fixed';
                modal.style.top = '0';
                modal.style.left = '0';
                modal.style.width = '100vw';
                modal.style.height = '100vh';
                modal.style.background = 'rgba(0,0,0,0.4)';
                modal.style.display = 'flex';
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
                modal.innerHTML = `<div style="background:#fff;padding:32px 28px;max-width:500px;border-radius:10px;position:relative;"><span style='position:absolute;top:10px;right:18px;font-size:28px;cursor:pointer;' onclick='document.getElementById(\'modal-termos\').remove()'>&times;</span><h2>Termos e Condições</h2><div style='max-height:300px;overflow:auto;font-size:15px;line-height:1.5;'>Ao se cadastrar, você concorda com os termos de uso e política de privacidade da Quinta Flores. Seus dados serão utilizados apenas para fins de reserva e comunicação. Para mais detalhes, consulte nosso site.</div><button onclick='document.getElementById(\'modal-termos\').remove()' style='margin-top:18px;padding:8px 18px;border-radius:6px;background:#2e5090;color:#fff;border:none;'>Fechar</button></div>`;
                document.body.appendChild(modal);
            }
        });
    }
}

// Barra de progresso animada
function updateProgress(step) {
    const totalSteps = 3;
    let percent = (step / totalSteps) * 100;
    const inner = document.getElementById('progressInner');
    inner.style.transition = 'width 0.5s cubic-bezier(.4,2,.6,1)';
    inner.style.width = percent + '%';
    inner.textContent = 'Passo ' + step + ' de ' + totalSteps;
}

// Toast animado
function showToast(msg, type='success') {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.style.position = 'fixed';
        toast.style.top = '30px';
        toast.style.right = '30px';
        toast.style.zIndex = 9999;
        toast.style.padding = '16px 28px';
        toast.style.borderRadius = '8px';
        toast.style.fontWeight = 'bold';
        toast.style.fontSize = '16px';
        toast.style.boxShadow = '0 2px 12px rgba(0,0,0,0.15)';
        toast.style.transition = 'all 0.4s';
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.background = type === 'success' ? '#4CAF50' : '#f44336';
    toast.style.color = '#fff';
    toast.style.display = 'block';
    toast.style.opacity = 1;
    setTimeout(() => {
        toast.style.opacity = 0;
        setTimeout(() => toast.style.display = 'none', 400);
    }, 2000);
}
</script>
</head>
<body>
<div class="container">
  <h1>Adicionar Hóspede</h1>

  <div class="progress-bar">
    <div class="progress-bar-inner" id="progressInner">Passo 1 de 3</div>
  </div>

  <!-- Formulário para enviar código -->
  <form method="post" action="" id="formCodigo">
    <label for="nome">Nome:</label><br>
    <input type="text" id="nome" name="nome" required value="<?php echo $dadosForm['nome']; ?>"><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required value="<?php echo $dadosForm['email']; ?>"><br><br>

    <?php if ($erroEmail): ?>
        <div class="message error"><?php echo $erroEmail; ?></div>
    <?php endif; ?>

    <?php if ($sucessoEmail): ?>
        <div class="message success"><?php echo $sucessoEmail; ?></div>
    <?php endif; ?>

    <button type="submit" name="enviar_codigo" id="enviar_codigo_btn">Enviar Código</button>
    <?php if (isset($_SESSION['codigo_gerado'])): ?>
      <button type="submit" name="cancelar_codigo" form="formCodigo">Cancelar Código</button>
    <?php endif; ?>
  </form>

  <hr>

  <!-- Formulário para adicionar hóspede após código -->
  <?php if (isset($_SESSION['codigo_gerado'])): ?>
  <form method="post" action="" id="formHospede" onsubmit="updateProgress(3);">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['email_gerado']); ?>">
    <input type="hidden" name="nome" value="<?php echo htmlspecialchars($dadosForm['nome']); ?>">
    <label for="codigo">Código de Verificação:</label><br>
    <input type="text" id="codigo" name="codigo" pattern="\d{6}" title="6 dígitos numéricos" required><br>
    <?php if($erroCodigo): ?><div class="message error"><?php echo $erroCodigo; ?></div><?php endif; ?>

    <label for="telefone">Telefone:</label><br>
    <select name="pais_codigo" required onchange="updateProgress(2);">
      <option value="+351" <?php if($dadosForm['pais_codigo']==='+351') echo 'selected'; ?>>Portugal (+351)</option>
      <option value="+34" <?php if($dadosForm['pais_codigo']==='+34') echo 'selected'; ?>>Espanha (+34)</option>
      <option value="+33" <?php if($dadosForm['pais_codigo']==='+33') echo 'selected'; ?>>França (+33)</option>
      <option value="+1"  <?php if($dadosForm['pais_codigo']==='+1')  echo 'selected'; ?>>EUA (+1)</option>
    </select>
    <input type="text" id="telefone" name="telefone" placeholder="Número" required value="<?php echo $dadosForm['telefone']; ?>"><br>
    <?php if($erroTelefone): ?><div class="message error"><?php echo $erroTelefone; ?></div><?php endif; ?>

    <label for="documento">NIF:</label><br>
    <input type="text" id="documento" name="documento" pattern="\d{9}" title="9 dígitos numéricos" required value="<?php echo $dadosForm['documento']; ?>"><br>
    <?php if($erroNIF): ?><div class="message error"><?php echo $erroNIF; ?></div><?php endif; ?>

    <label for="morada">Morada (opcional):</label><br>
    <input type="text" id="morada" name="morada" value="<?php echo $dadosForm['morada']; ?>"><br><br>

    <label><input type="checkbox" name="aceitou" value="Sim" required> Aceito os Termos e Condições</label><br>
    <?php if($erroTermos): ?><div class="message error"><?php echo $erroTermos; ?></div><?php endif; ?>

    <br>
    <button type="submit" name="adicionar_hospede">Adicionar Hóspede</button>
  </form>
  <?php endif; ?>

  <?php if($erro): ?>
    <div class="message error"><?php echo $erro; ?></div>
  <?php endif; ?>
  <?php if($sucesso): ?>
    <div class="message success"><?php echo $sucesso; ?></div>
  <?php endif; ?>
</div>
<a href="hospedes.php">← Voltar</a>
</body>
</html>
