<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação de campos obrigatórios
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha'])) {
        $erro = "Todos os campos são obrigatórios!";
    } else {
        // Sanitização de dados
        $nome = htmlspecialchars($_POST['nome']);
        $email = htmlspecialchars($_POST['email']);
        $senha = $_POST['senha'];
        $cargo = htmlspecialchars($_POST['cargo']);
        $telefone = htmlspecialchars($_POST['telefone']);
        
        // Dados de turno e férias
        $turno_inicio = $_POST['turno_inicio'] ?? null;
        $turno_fim = $_POST['turno_fim'] ?? null;
        $ferias_inicio = $_POST['ferias_inicio'] ?? null;
        $ferias_fim = $_POST['ferias_fim'] ?? null;
        $motivo_ferias = $_POST['motivo_ferias'] ?? null;

        // Validação do formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = "Email inválido!";
        } 

        // Validação da senha (mínimo de 8 caracteres, pelo menos uma letra maiúscula)
        elseif (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha)) {
            $erro = "A senha deve ter pelo menos 8 caracteres e uma letra maiúscula!";
        } 

        // Verificação se o email já existe no banco
        else {
            $stmt = $conexao->prepare("SELECT F_email FROM funcionarios WHERE F_email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $erro = "Email já registrado!";
            } else {
                // Se tudo estiver ok, insere o novo funcionário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("INSERT INTO funcionarios (F_nome, F_email, F_senha, F_cargo, F_telefone) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nome, $email, $senha_hash, $cargo, $telefone);
                $stmt->execute();

                // Obtém o ID do novo funcionário inserido
                $funcionario_id = $conexao->insert_id;

                // Inserir dados de turnos, se fornecidos
                if ($turno_inicio && $turno_fim) {
                    $stmt_turno = $conexao->prepare("INSERT INTO turnos (F_id_funcionario, T_data, T_inicio, T_fim) VALUES (?, CURDATE(), ?, ?)");
                    $stmt_turno->bind_param("iss", $funcionario_id, $turno_inicio, $turno_fim);
                    $stmt_turno->execute();
                }

                // Inserir dados de férias/ausência, se fornecidos
                if ($ferias_inicio && $ferias_fim && $motivo_ferias) {
                    $stmt_ferias = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, FA_inicio, FA_fim, FA_tipo, FA_motivo) VALUES (?, ?, ?, 'Férias', ?)");
                    $stmt_ferias->bind_param("isss", $funcionario_id, $ferias_inicio, $ferias_fim, $motivo_ferias);
                    $stmt_ferias->execute();
                }

                header("Location: funcionarios.php"); // Redireciona para a lista de funcionários
                exit;
            }
        }
    }
}
?>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=37174&format=png&color=000000" alt="Ícone Funcionários" style="height: 50px;">
        <h1>Adicionar um novo Funcionário</h1>
    </div>

<link rel="stylesheet" href="admin.css">
<form method="post">
    Nome: <input type="text" name="nome" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    Senha: <input type="password" name="senha" required><br><br>
    Cargo: 
    <select name="cargo" required>
        <option value="gerente">Gerente</option>
        <option value="administrador">Administrador</option>
        <option value="recepcionista">Recepcionista</option>
        <option value="governanta">Governanta</option>
        <option value="contabilista">Contabilista</option>
    </select><br><br>
    Telefone: <input type="text" name="telefone"><br><br>

    <!-- Dados de Turno -->
    <h3>Turno</h3>
    Início: <input type="time" name="turno_inicio"><br><br>
    Fim: <input type="time" name="turno_fim"><br><br>

    <!-- Dados de Férias/Ausência -->
    <h3>Férias/Ausência</h3>
    Início: <input type="date" name="ferias_inicio"><br><br>
    Fim: <input type="date" name="ferias_fim"><br><br>
    Motivo: <input type="text" name="motivo_ferias"><br><br>

    <button type="submit">Salvar</button>
</form>
<a href="funcionarios.php">← Voltar</a>

<?php
if (isset($erro)) {
    echo "<p style='color: red;'>$erro</p>";
}
