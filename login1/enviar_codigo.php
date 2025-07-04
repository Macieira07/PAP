<?php
/*
 * ============================================================
 *   API Enviar Código de Verificação - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, lógica de envio)
 *     - JSON (comunicação com frontend)
 *
 *   Bibliotecas e Frameworks:
 *     - PHPMailer (envio de emails)
 *
 *   Estrutura da Página:
 *     1. Configuração inicial PHP (includes, headers)
 *     2. Receção e validação dos dados do frontend
 *     3. Geração e envio do código
 *     4. Resposta JSON para o frontend
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
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
