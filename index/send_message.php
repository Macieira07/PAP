<?php
/*
 * ============================================================
 *   API Enviar Mensagem de Contacto - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, lógica e conexão)
 *     - JSON (comunicação com frontend)
 *
 *   Bibliotecas e Frameworks:
 *     - PHPMailer (envio de emails)
 *
 *   Estrutura da Página:
 *     1. Configuração inicial PHP (includes, headers)
 *     2. Receção e validação dos dados do frontend
 *     3. Envio de email para administração
 *     4. Resposta JSON para o frontend
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
header('Content-Type: application/json');
session_start();

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php';  // Ajusta caminho se necessário

// Receber e limpar dados
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validação dos campos
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode([
        'tipo' => 'erro',
        'mensagem' => 'Por favor, preencha todos os campos obrigatórios.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'tipo' => 'erro',
        'mensagem' => 'O endereço de email fornecido não é válido. Por favor, corrija e tente novamente.'
    ]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // Configuração SMTP (mantenha suas configurações atuais)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'quinta.flores2019@gmail.com';
    $mail->Password = 'cbra fjzb nizo lilw';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Remetente e destinatário
    $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
    $mail->addReplyTo($email, $name);
    $mail->addAddress('quinta.flores2019@gmail.com', 'Quinta Flores');

    // Conteúdo do email
    $mail->isHTML(true);
    $mail->Subject = 'Novo Contacto: ' . $subject;
    $mail->Body = '
        <h2>Novo Contacto Recebido</h2>
        <p><strong>Nome:</strong> ' . htmlspecialchars($name) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
        <p><strong>Assunto:</strong> ' . htmlspecialchars($subject) . '</p>
        <p><strong>Mensagem:</strong></p>
        <div>' . nl2br(htmlspecialchars($message)) . '</div>
        <hr>
        <p>Este email foi enviado através do formulário de contacto do website.</p>
    ';
    $mail->AltBody = "Nome: {$name}\nEmail: {$email}\nAssunto: {$subject}\n\nMensagem:\n{$message}";

    $mail->send();

    echo json_encode([
        'tipo' => 'sucesso',
        'mensagem' => 'A sua mensagem foi enviada com sucesso! Entraremos em contacto brevemente. Obrigado!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'tipo' => 'erro',
        'mensagem' => 'Ocorreu um erro inesperado ao enviar a sua mensagem. Por favor, tente novamente mais tarde ou contacte-nos diretamente por telefone.'
    ]);
    // Log do erro para administração
    error_log('Erro no envio de email: ' . $e->getMessage());
}
exit;
