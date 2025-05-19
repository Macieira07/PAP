<?php
require '../conexao.php';

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação de campos obrigatórios
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha'])) {
        $mensagem = "Todos os campos obrigatórios devem ser preenchidos!";
        $tipo_mensagem = "erro";
    } else {
        // Sanitização de dados
        $nome = htmlspecialchars($_POST['nome']);
        $email = htmlspecialchars($_POST['email']);
        $senha = $_POST['senha'];
        $cargo = htmlspecialchars($_POST['cargo']);
        $telefone = htmlspecialchars($_POST['telefone']);
        
        // Dados de turno e férias
        $turno_inicio = $_POST['turno_inicio'] ?? null;
        $ferias_inicio = $_POST['ferias_inicio'] ?? null;

        // Validação do formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = "Email inválido!";
            $tipo_mensagem = "erro";
        } elseif (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha)) {
            $mensagem = "A senha deve ter pelo menos 8 caracteres e uma letra maiúscula!";
            $tipo_mensagem = "erro";
        } else {
            // Verificação se o email já existe no banco
            $stmt = $conexao->prepare("SELECT F_email FROM funcionarios WHERE F_email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $mensagem = "Email já registrado!";
                $tipo_mensagem = "erro";
            } else {
                // Inserir o novo funcionário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("INSERT INTO funcionarios (F_nome, F_email, F_senha, F_cargo, F_telefone) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nome, $email, $senha_hash, $cargo, $telefone);
                $stmt->execute();

                $funcionario_id = $conexao->insert_id;

                // Inserir turno
                if ($turno_inicio) {
                    $stmt_turno = $conexao->prepare("INSERT INTO turnos (F_id_funcionario, turno, data_inicio, data_fim) VALUES (?, ?, CURDATE(), CURDATE())");
                    $stmt_turno->bind_param("is", $funcionario_id, $turno_inicio);
                    $stmt_turno->execute();
                }

                // Inserir férias
                if ($ferias_inicio) {
                    $ferias_fim = (new DateTime($ferias_inicio))->modify('+14 days')->format('Y-m-d');

                    // Verificar se já existem férias na mesma semana
                    $stmt_verificar = $conexao->prepare("
                        SELECT * FROM ferias_ausencias 
                        WHERE tipo_ausencia = 'Férias' 
                        AND (
                            WEEK(data_inicio, 1) = WEEK(?, 1) 
                            OR WEEK(data_fim, 1) = WEEK(?, 1)
                        )
                    ");
                    $stmt_verificar->bind_param("ss", $ferias_inicio, $ferias_fim);
                    $stmt_verificar->execute();
                    $resultado_verificar = $stmt_verificar->get_result();

                    if ($resultado_verificar->num_rows > 0) {
                        $mensagem = "Já existem férias marcadas para a mesma semana. Escolha outra data.";
                        $tipo_mensagem = "erro";
                    } else {
                        $tipo_ausencia = 'Férias';
                        $stmt_ferias = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, tipo_ausencia, data_inicio, data_fim) VALUES (?, ?, ?, ?)");
                        $stmt_ferias->bind_param("isss", $funcionario_id, $tipo_ausencia, $ferias_inicio, $ferias_fim);
                        $stmt_ferias->execute();
                    }
                }

                $mensagem = "Funcionário adicionado com sucesso!";
                $tipo_mensagem = "sucesso";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Adicionar Funcionário</title>
    <style>
        .mensagem {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .mensagem.sucesso {
            background-color: #4CAF50;
            color: white;
        }
        .mensagem.erro {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=37174&format=png&color=000000" alt="Ícone Funcionários" style="height: 50px;">
        <h1>Adicionar Funcionário</h1>
    </div>

    <?php if ($mensagem): ?>
        <div class="mensagem <?= $tipo_mensagem ?>"><?= $mensagem ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Nome:</label>
        <input type="text" name="nome" required><br><br>

        <label>Email:</label>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label>
        <input type="password" name="senha" required><br><br>

        <label>Cargo:</label>
        <select name="cargo" required>
            <option value="gerente">Gerente</option>
            <option value="administrador">Administrador</option>
            <option value="recepcionista">Recepcionista</option>
            <option value="governanta">Governanta</option>
            <option value="contabilista">Contabilista</option>
        </select><br><br>

        <label>Telefone:</label>
        <input type="text" name="telefone"><br><br>

        <h3>Turno</h3>
        <label>Selecione o Turno:</label>
        <select name="turno_inicio">
            <option value="08:00-16:00">Manhã (08:00 - 16:00)</option>
            <option value="16:00-00:00">Tarde (16:00 - 00:00)</option>
            <option value="00:00-08:00">Noite (00:00 - 08:00)</option>
        </select><br><br>

        <h3>Férias</h3>
        <label>Data de Início:</label>
        <input type="date" name="ferias_inicio"><br>
        <p>O período de férias será automaticamente ajustado para 15 dias.</p><br>

        <button type="submit">Salvar</button>
    </form>

    <a href="funcionarios.php">← Voltar</a>
</body>
</html>