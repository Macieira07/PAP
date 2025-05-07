<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletar dados do formulário
    $nome = $_POST['name'];
    $email = $_POST['email'];
    $assunto = $_POST['subject'];
    $mensagem = $_POST['message'];

    // Validar os dados
    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        echo "Por favor, preencha todos os campos.";
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        // Configuração do servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seuemail@gmail.com';  // Seu e-mail do Gmail
        $mail->Password = 'svwy ziac roqo ygzw';  // Sua senha do Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinatário e remetente
        $mail->setFrom($email, $nome);
        $mail->addAddress('info@quintaflores.pt');  // Para onde a mensagem será enviada

        // Conteúdo do e-mail
        $mail->isHTML(false);
        $mail->Subject = "Mensagem de Contato: $assunto";
        $mail->Body    = "Nome: $nome\nEmail: $email\nAssunto: $assunto\n\nMensagem:\n$mensagem";

        // Enviar o e-mail
        $mail->send();
        echo 'Mensagem enviada com sucesso!';
    } catch (Exception $e) {
        echo "Erro ao enviar a mensagem. Erro: {$mail->ErrorInfo}";
    }
}
?>
