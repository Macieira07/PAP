<?php
require '../../conexao.php';
session_start();

// Inicializa sessão do hóspede se não existir
if (!isset($_SESSION['novo_hospede'])) {
    $_SESSION['novo_hospede'] = [];
}

// Inicializa passo se não existir
if (!isset($_SESSION['passo'])) {
    $_SESSION['passo'] = 1;
}

$erro = '';
$sucesso = '';

// Define passo inicial
$passo = $_SESSION['passo'];

// Se vier POST, processa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Confirma passo do POST ou mantém sessão
    $passo = isset($_POST['passo']) ? (int)$_POST['passo'] : $passo;

    switch ($passo) {

        // Passo 1 - Dados Básicos
        case 1:
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($nome) || empty($email)) {
                $erro = 'Preencha o nome e o email.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = 'Email inválido.';
            } else {
                $_SESSION['novo_hospede']['nome'] = $nome;
                $_SESSION['novo_hospede']['email'] = $email;
                $_SESSION['passo'] = 2;
            }
            break;

        // Passo 2 - Contacto
        case 2:
            $telefone = trim($_POST['telefone'] ?? '');
            $documento = trim($_POST['documento'] ?? '');

            if (empty($documento)) {
                $erro = 'Preencha o documento de identificação.';
            } else {
                $_SESSION['novo_hospede']['telefone'] = $telefone;
                $_SESSION['novo_hospede']['documento'] = $documento;
                $_SESSION['passo'] = 3;
            }
            break;

        // Passo 3 - Morada e Outros
        case 3:
            $morada = trim($_POST['morada'] ?? '');
            $pais = trim($_POST['pais'] ?? '');
            $verificado = isset($_POST['verificado']) ? 1 : 0;
            $aceitou = isset($_POST['aceitou']) ? 1 : 0;
            $senha = $_POST['senha'] ?: substr(md5(uniqid()), 0, 8);

            $_SESSION['novo_hospede']['morada'] = $morada;
            $_SESSION['novo_hospede']['pais'] = $pais;
            $_SESSION['novo_hospede']['verificado'] = $verificado;
            $_SESSION['novo_hospede']['aceitou'] = $aceitou;
            $_SESSION['novo_hospede']['senha'] = $senha;

            // Verificar duplicado
            $email = $_SESSION['novo_hospede']['email'];
            $stmt = $conexao->prepare("SELECT H_id_hospede FROM hospedes WHERE H_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $erro = 'Já existe um hóspede com este email.';
                $_SESSION['passo'] = 1; // Volta ao início
            } else {
                // Inserir novo hóspede
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("INSERT INTO hospedes 
                    (H_nome, H_email, H_senha, H_telefone, H_documento_ident, H_morada, H_pais, H_verificado_email, H_aceitou_termos_uso) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "sssssssii",
                    $_SESSION['novo_hospede']['nome'],
                    $email,
                    $senha_hash,
                    $_SESSION['novo_hospede']['telefone'],
                    $_SESSION['novo_hospede']['documento'],
                    $morada,
                    $pais,
                    $verificado,
                    $aceitou
                );

                if ($stmt->execute()) {
                    $sucesso = 'Hóspede adicionado com sucesso!';
                    unset($_SESSION['novo_hospede']);
                    $_SESSION['passo'] = 1;
                } else {
                    $erro = 'Erro ao adicionar hóspede: ' . $stmt->error;
                    $_SESSION['passo'] = 3;
                }
            }
            break;
    }

    // Actualiza passo
    $passo = $_SESSION['passo'];
}
?>

<h2>Adicionar Novo Hóspede - Passo <?= htmlspecialchars($passo) ?></h2>

<?php if ($erro): ?>
    <div style="color: red;"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div style="color: green;"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<form method="post">

    <?php if ($passo == 1): ?>
        <label>Nome *</label>
        <input type="text" name="nome" required><br>

        <label>Email *</label>
        <input type="email" name="email" required><br>

    <?php elseif ($passo == 2): ?>
        <label>Telefone</label>
        <input type="text" name="telefone"><br>

        <label>Documento de Identificação *</label>
        <input type="text" name="documento" required><br>

    <?php elseif ($passo == 3): ?>
        <label>Morada</label>
        <input type="text" name="morada"><br>

        <label>País</label>
        <input type="text" name="pais"><br>

        <label>Senha</label>
        <input type="text" name="senha" placeholder="Deixe em branco para gerar"><br>

        <label><input type="checkbox" name="verificado"> Email Verificado</label><br>
        <label><input type="checkbox" name="aceitou"> Aceitou os Termos</label><br>
    <?php endif; ?>

    <input type="hidden" name="passo" value="<?= htmlspecialchars($passo) ?>">
    <button type="submit">Próximo</button>

</form>
