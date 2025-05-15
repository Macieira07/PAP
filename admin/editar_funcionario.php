<?php
require '../conexao.php';

// Verificar se o ID foi passado pela URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do funcionário não fornecido.");
}

$id = $_GET['id'];

// Variáveis para os dados do formulário
$nome = $email = $cargo = $telefone = $senha = "";
$erro = "";

// Verificar se o formulário de edição do funcionário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['atualizar_ferias'])) {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Verificar se o e-mail já existe para outro funcionário
    $sql_check_email = "SELECT COUNT(*) FROM funcionarios WHERE F_email = ? AND F_id_funcionario != ?";
    $stmt_check = $conexao->prepare($sql_check_email);
    $stmt_check->bind_param("si", $email, $id);
    $stmt_check->execute();
    $stmt_check->bind_result($email_count);
    $stmt_check->fetch();
    $stmt_check->close();

    // Se o e-mail já estiver registado para outro funcionário, mostrar um erro
    if ($email_count > 0) {
        $erro = "Este e-mail já está registado para outro funcionário. Tente outro.";
    } else {
        // Se a senha foi informada, validar e atualizar
        if (!empty($senha)) {
            // Validação da senha (mínimo de 8 caracteres, pelo menos uma letra maiúscula)
            if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha)) {
                $erro = "A senha deve ter pelo menos 8 caracteres e uma letra maiúscula!";
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("UPDATE funcionarios SET F_nome=?, F_email=?, F_senha=?, F_cargo=?, F_telefone=? WHERE F_id_funcionario=?");
                $stmt->bind_param("sssssi", $nome, $email, $senha_hash, $cargo, $telefone, $id);
                $stmt->execute();
            }
        } else {
            // Atualizar sem mudar a senha
            $stmt = $conexao->prepare("UPDATE funcionarios SET F_nome=?, F_email=?, F_cargo=?, F_telefone=? WHERE F_id_funcionario=?");
            $stmt->bind_param("ssssi", $nome, $email, $cargo, $telefone, $id);
            $stmt->execute();
        }
    }
}

// Buscar o funcionário atual
$stmt = $conexao->prepare("SELECT * FROM funcionarios WHERE F_id_funcionario=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$f = $resultado->fetch_assoc();

// Buscar férias/ausências do funcionário
$stmt_ferias = $conexao->prepare("SELECT * FROM ferias_ausencias WHERE F_id_funcionario = ? ORDER BY data_inicio DESC LIMIT 1");
$stmt_ferias->bind_param("i", $id);
$stmt_ferias->execute();
$resultado_ferias = $stmt_ferias->get_result();

// Verificar se existem registros de férias/ausências
if ($resultado_ferias->num_rows > 0) {
    $ferias = $resultado_ferias->fetch_assoc();
    $tipo_ausencia = $ferias['tipo_ausencia'];
    $data_inicio_ausencia = $ferias['data_inicio'];
    $data_fim_ausencia = $ferias['data_fim'];
} else {
    $tipo_ausencia = "Nenhuma";
    $data_inicio_ausencia = "";
    $data_fim_ausencia = "";
}

// Verificar se o formulário de férias foi enviado
if (isset($_POST['atualizar_ferias'])) {
    $tipo_ausencia = $_POST['tipo_ausencia'];
    $data_inicio_ausencia = $_POST['data_inicio_ausencia'];
    $data_fim_ausencia = $_POST['data_fim_ausencia'];

    // Atualizar as férias/ausência
    $stmt_ferias_update = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, tipo_ausencia, data_inicio, data_fim) VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE tipo_ausencia=?, data_inicio=?, data_fim=?");
    $stmt_ferias_update->bind_param("issssss", $id, $tipo_ausencia, $data_inicio_ausencia, $data_fim_ausencia, $tipo_ausencia, $data_inicio_ausencia, $data_fim_ausencia);
    $stmt_ferias_update->execute();
    header("Location: funcionarios.php"); // Redireciona após a atualização
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Editar Funcionário</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=37174&format=png&color=000000" alt="Ícone Funcionários" style="height: 50px;">
        <h1>Editar Funcionário</h1>
    </div>

    <!-- Formulário de Edição de Funcionário -->
    <form method="post">
        Nome: <input type="text" name="nome" value="<?= $f['F_nome'] ?>" required><br><br>
        Email: <input type="email" name="email" value="<?= $f['F_email'] ?>" required><br><br>
        Senha: <input type="password" name="senha"><br><br> <!-- A senha agora é opcional para edição -->
        Cargo: 
        <select name="cargo" required>
            <option value="gerente" <?= $f['F_cargo'] == 'gerente' ? 'selected' : '' ?>>Gerente</option>
            <option value="administrador" <?= $f['F_cargo'] == 'administrador' ? 'selected' : '' ?>>Administrador</option>
            <option value="recepcionista" <?= $f['F_cargo'] == 'recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
            <option value="governanta" <?= $f['F_cargo'] == 'governanta' ? 'selected' : '' ?>>Governanta</option>
            <option value="contabilista" <?= $f['F_cargo'] == 'contabilista' ? 'selected' : '' ?>>Contabilista</option>
        </select><br><br>
        Telefone: <input type="text" name="telefone" value="<?= $f['F_telefone'] ?>"><br><br>

        <button type="submit">Atualizar Funcionário</button>
    </form>

    <form method="post">
        <!-- Exibindo férias ou ausências -->
        <p><strong>Férias/Ausência:</strong> <?= $tipo_ausencia ?></p>
        <p><strong>Data Início: </strong> <input type="date" name="data_inicio_ausencia" value="<?= $data_inicio_ausencia ?>"></p>
        <p><strong>Data Fim: </strong> <input type="date" name="data_fim_ausencia" value="<?= $data_fim_ausencia ?>"></p>
        <p><strong>Tipo de Ausência:</strong>
            <select name="tipo_ausencia">
                <option value="Férias" <?= $tipo_ausencia == 'Férias' ? 'selected' : '' ?>>Férias</option>
                <option value="Falta" <?= $tipo_ausencia == 'Falta' ? 'selected' : '' ?>>Falta</option>
            </select>
        </p>

        <button type="submit" name="atualizar_ferias">Atualizar Férias/Ausências</button>
    </form>
    <a href="funcionarios.php">← Voltar</a>

    <?php if (isset($erro)) { echo "<p style='color: red;'>$erro</p>"; } ?>
</body>
</html>
