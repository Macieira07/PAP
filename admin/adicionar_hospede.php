<?php
session_start();
require '../conexao.php';
require_once 'email.php';

function gerarCodigo() {
    return rand(100000, 999999);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$erro = '';
$sucesso = '';

$dadosForm = [
    'nome' => '',
    'email' => '',
    'telefone' => '',
    'documento' => '',
    'morada' => '',
    'verificado' => 'Não',
    'aceitou' => 'Não'
];

// Carrega os dados previamente preenchidos, se existirem
foreach ($dadosForm as $campo => $valor) {
    if (isset($_POST[$campo])) {
        $dadosForm[$campo] = htmlspecialchars($_POST[$campo]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_codigo'])) {
    $email = $_POST['email'];
    $nome = $_POST['nome'];

    if (!validarEmail($email)) {
        $erro = "Email inválido.";
    } elseif (isset($_SESSION['codigo_gerado']) && $_SESSION['email_gerado'] === $email) {
        $erro = "Já foi enviado um código para este email.";
    } else {
        $codigoGerado = gerarCodigo();
        $_SESSION['codigo_gerado'] = $codigoGerado;
        $_SESSION['email_gerado'] = $email;

        $resultadoEmail = enviarEmailCodigo($email, $nome, $codigoGerado);

        if ($resultadoEmail === true) {
            $sucesso = "Código enviado para o email!";
        } else {
            $erro = "Erro ao enviar email: $resultadoEmail";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_hospede'])) {
    $codigoInserido = $_POST['codigo'];
    $codigoCerto = $_SESSION['codigo_gerado'] ?? '';
    $emailSession = $_SESSION['email_gerado'] ?? '';
    $emailForm = $_POST['email'];

    if ($codigoInserido != $codigoCerto || $emailSession != $emailForm) {
        $erro = "O código inserido está incorreto ou o email não corresponde.";
    } else {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($codigoInserido, PASSWORD_DEFAULT);
        $telefone = $_POST['telefone'];
        $documento = $_POST['documento'];
        $morada = $_POST['morada'];
        $verificado = ($_POST['verificado'] === 'Sim') ? 1 : 0;
        $aceitou = ($_POST['aceitou'] === 'Sim') ? 1 : 0;

        // Verificar se o e-mail já existe
        $verifica = $conexao->prepare("SELECT COUNT(*) FROM hospedes WHERE H_email=?");
        $verifica->bind_param("s", $email);
        $verifica->execute();
        $verifica->bind_result($existe);
        $verifica->fetch();
        $verifica->close();

        if ($existe > 0) {
            $erro = "Já existe um hóspede com esse e-mail.";
        } else {
            $token = bin2hex(random_bytes(16));
            $token_expira = date('Y-m-d H:i:s', strtotime('+1 day'));

            $stmt = $conexao->prepare("INSERT INTO hospedes 
                (H_nome, H_email, H_senha, H_telefone, H_documento_ident, H_morada, 
                H_verificado_email, H_aceitou_termos_uso, H_notas, H_token_verificacao, H_token_expira, H_valor_notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, '', '', ?, 0)");

            $stmt->bind_param("sssssssis", $nome, $email, $senha, $telefone, $documento, $morada,
                $verificado, $aceitou, $token_expira);

            if ($stmt->execute()) {
                $sucesso = "Hóspede adicionado com sucesso!";
                unset($_SESSION['codigo_gerado'], $_SESSION['email_gerado']);
                $dadosForm = [
                    'nome' => '',
                    'email' => '',
                    'telefone' => '',
                    'documento' => '',
                    'morada' => '',
                    'verificado' => 'Não',
                    'aceitou' => 'Não'
                ];
            } else {
                $erro = "Erro ao adicionar hóspede.";
            }
        }
    }
}
?>

<link rel="stylesheet" href="admin.css">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
<script src="https://kit.fontawesome.com/2e1dbb4c78.js" crossorigin="anonymous"></script>

<a href="hospedes.php" style="text-decoration:none; color:black;">
    <i class="fa-solid fa-arrow-left"></i> Voltar
</a>

<div style="display: flex; align-items: center; gap: 10px; margin-top:10px;">
    <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
    <h2>Adicionar um novo Hóspede</h2>
</div>

<?php if ($erro): ?>
    <div style="color: red; font-weight: bold;"><?= $erro ?></div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div style="color: green; font-weight: bold;"><?= $sucesso ?></div>
<?php endif; ?>

<form method="post">
    <label>
        <i class="fa-solid fa-user"></i> Nome Completo:
        <input type="text" name="nome" required value="<?= $dadosForm['nome'] ?>">
    </label><br><br>

    <label>
        <i class="fa-solid fa-envelope"></i> Email:
        <input type="email" name="email" required value="<?= $dadosForm['email'] ?>">
    </label><br><br>

    <?php if (!isset($_SESSION['codigo_gerado'])): ?>
        <button type="submit" name="enviar_codigo">Gerar e Enviar Código</button>
    <?php else: ?>
        <label>
            <i class="fa-solid fa-key"></i> Introduza o Código Recebido:
            <input type="text" name="codigo" required>
        </label><br><br>

        <label>
            <i class="fa-solid fa-phone"></i> Telefone:
            <input type="text" name="telefone" required value="<?= $dadosForm['telefone'] ?>">
        </label><br><br>

        <label>
            <i class="fa-solid fa-address-card"></i> Documento:
            <input type="text" name="documento" required value="<?= $dadosForm['documento'] ?>">
        </label><br><br>

        <label>
            <i class="fa-solid fa-location-dot"></i> Morada:
            <input type="text" name="morada" value="<?= $dadosForm['morada'] ?>">
        </label><br><br>

        <label>
            Verificou Email?
            <select name="verificado">
                <option value="Não" <?= $dadosForm['verificado'] === 'Não' ? 'selected' : '' ?>>Não</option>
                <option value="Sim" <?= $dadosForm['verificado'] === 'Sim' ? 'selected' : '' ?>>Sim</option>
            </select>
        </label><br><br>

        <label>
            Aceitou os Termos?
            <select name="aceitou">
                <option value="Não" <?= $dadosForm['aceitou'] === 'Não' ? 'selected' : '' ?>>Não</option>
                <option value="Sim" <?= $dadosForm['aceitou'] === 'Sim' ? 'selected' : '' ?>>Sim</option>
            </select>
        </label><br><br>

        <button type="submit" name="adicionar_hospede">Adicionar Hóspede</button>
    <?php endif; ?>
</form>
