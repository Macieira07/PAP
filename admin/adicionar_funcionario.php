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

        // Validação do formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = "Email inválido!";
        } 

        // Validação da senha (mínimo de 8 caracteres, uma letra maiúscula, um número, um caractere especial)
        elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $senha)) {
            $erro = "A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, um número e um caractere especial!";
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
                header("Location: funcionarios.php"); // Redireciona para a lista de funcionários
                exit;
            }
        }
    }
}
?>
<link rel="stylesheet" href="admin.css">
<form method="post">
    Nome: <input type="text" name="nome" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    Senha: <input type="password" name="senha" required><br><br>
    Cargo: <input type="text" name="cargo" value="funcionário"><br><br>
    Telefone: <input type="text" name="telefone"><br><br>
    <button type="submit">Salvar</button>
</form>

<?php
if (isset($erro)) {
    echo "<p style='color: red;'>$erro</p>";
}
?>
