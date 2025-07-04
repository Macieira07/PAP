<?php
require '../../conexao.php';

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
// Buscar o turno atual do funcionário
$stmt_turno = $conexao->prepare("SELECT * FROM turnos WHERE F_id_funcionario = ?");
$stmt_turno->bind_param("i", $id);
$stmt_turno->execute();
$resultado_turno = $stmt_turno->get_result();
$turno = $resultado_turno->fetch_assoc();
// Garantir que os valores de horário estejam definidos
$horario_inicio = $turno['T_inicio'] ?? '';
$horario_fim = $turno['T_fim'] ?? '';
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
if (isset($_GET['modal'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ... lógica de validação e atualização ...
        if (empty($erro)) {
            echo 'OK';
            exit;
        }
        // Se erro, mostra o formulário novamente
    }
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Editar Funcionário</h2>
        <?php if (!empty($erro)): ?>
            <div class="mensagem erro" style="margin-bottom:10px;"> <?= $erro ?> </div>
        <?php endif; ?>
        <form method="post" id="formFuncionario" data-id="<?= $f['F_id_funcionario'] ?>" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Passo 1 -->
            <div class="wizard-step" id="wizardStep1Funcionario">
                <label>Nome:<input type="text" name="nome" value="<?= htmlspecialchars($f['F_nome']) ?>" required></label>
                <label>Email:<input type="email" name="email" value="<?= htmlspecialchars($f['F_email']) ?>" required></label>
                <label>Senha:<input type="password" name="senha"></label>
                <label>Cargo:
                    <select name="cargo" required>
                        <option value="gerente" <?= $f['F_cargo'] == 'gerente' ? 'selected' : '' ?>>Gerente</option>
                        <option value="administrador" <?= $f['F_cargo'] == 'administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="recepcionista" <?= $f['F_cargo'] == 'recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
                        <option value="governanta" <?= $f['F_cargo'] == 'governanta' ? 'selected' : '' ?>>Governanta</option>
                        <option value="contabilista" <?= $f['F_cargo'] == 'contabilista' ? 'selected' : '' ?>>Contabilista</option>
                    </select>
                </label>
                <label>Telefone:<input type="text" name="telefone" value="<?= htmlspecialchars($f['F_telefone']) ?>"></label>
                <button type="button" id="btnWizardProximoFuncionario" class="atalho-btn" style="align-self:flex-end; margin-top:10px;">Próximo &rarr;</button>
            </div>
            <!-- Passo 2 -->
            <div class="wizard-step" id="wizardStep2Funcionario" style="display:none;">
                <h3>Turno</h3>
                <label>Horário de Início:<input type="time" name="horario_inicio" value="<?= $horario_inicio ?>" required></label>
                <label>Horário de Fim:<input type="time" name="horario_fim" value="<?= $horario_fim ?>" required></label>
                <h3>Férias/Ausência</h3>
                <label>Data de Início:<input type="date" name="data_inicio_ausencia" value="<?= $data_inicio_ausencia ?>"></label>
                <label>Data de Fim:<input type="date" name="data_fim_ausencia" value="<?= $data_fim_ausencia ?>"></label>
                <label>Tipo de Ausência:
                    <select name="tipo_ausencia">
                        <option value="Férias" <?= $tipo_ausencia == 'Férias' ? 'selected' : '' ?>>Férias</option>
                        <option value="Falta" <?= $tipo_ausencia == 'Falta' ? 'selected' : '' ?>>Falta</option>
                    </select>
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorFuncionario" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Atualizar Funcionário</button>
                </div>
            </div>
        </form>
    </div>
    <style>
        .mensagem.erro { background: #f44336; color: #fff; padding: 8px; border-radius: 4px; }
    </style>
    <?php
    exit;
}
?>
