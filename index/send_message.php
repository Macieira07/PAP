<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../pagamento/PHPMailer/src/PHPMailer.php';
require '../pagamento/PHPMailer/src/SMTP.php';
require '../pagamento/PHPMailer/src/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 0; // para não mostrar debug no browser (muda para 2 só para testar)
    $mail->Debugoutput = 'html';

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'quinta.flores2019@gmail.com';
    $mail->Password   = 'kgre oqhy kxcn grid';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $nome = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $assunto = htmlspecialchars($_POST['subject'] ?? '');
    $mensagem = htmlspecialchars($_POST['message'] ?? '');

    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Por favor preencha todos os campos.'
        ];
        header('Location: contact.php'); // muda para o nome da tua página do formulário
        exit;
    }

    $mail->setFrom($email, $nome);
    $mail->addAddress('quinta.flores2019@gmail.com');

    $mail->Subject = "Mensagem do site: $assunto";
    $mail->Body    = "Nome: $nome\nEmail: $email\n\nMensagem:\n$mensagem";

    $mail->send();

    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Mensagem enviada com sucesso! Responderemos em breve.'
    ];
    header('Location: contact.php');
    exit;

} catch (Exception $e) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => "Erro ao enviar mensagem. Detalhe: {$mail->ErrorInfo}"
    ];
    header('Location: contact.php');
    exit;
}
?>
