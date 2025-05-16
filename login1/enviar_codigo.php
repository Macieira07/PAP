<?php
require_once '../conexao.php';
require_once 'email_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Verifica se o email existe na base de dados
    $stmt = $conexao->prepare("SELECT * FROM hospedes WHERE H_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $codigo = rand(100000, 999999);
        $senha_hash = password_hash($codigo, PASSWORD_DEFAULT);

        // Atualiza a senha na base de dados
        $update = $conexao->prepare("UPDATE hospedes SET H_senha = ? WHERE H_email = ?");
        $update->bind_param("ss", $senha_hash, $email);
        $update->execute();

        // Envia o email com o código
        $assunto = "Código de acesso à Quinta Flores";
        $mensagem = "<p>O seu código de acesso é: <strong>$codigo</strong></p><p>Utilize este código como senha para aceder à sua conta.</p>";

        if (enviarEmail($email, $assunto, $mensagem)) {
            echo json_encode(['sucesso' => 'Código enviado com sucesso. Verifique o seu email.']);
        } else {
            echo json_encode(['erro' => 'Erro ao enviar o email.']);
        }
    } else {
        echo json_encode(['erro' => 'Email não encontrado.']);
    }
} else {
    echo json_encode(['erro' => 'Requisição inválida.']);
}
