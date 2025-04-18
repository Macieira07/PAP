<?php
// Caminhos CORRETOS para sua estrutura atual
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require_once 'init.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmail($destino, $assunto, $mensagem) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor (substitua com seus dados!)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Servidor SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'quinta.flores2019@gmail.com';  // Seu email
        $mail->Password   = 'svwy ziac roqo ygzw';           // Senha de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Remetente/Destinatário
        $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
        $mail->addAddress($destino);

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Erro no email: " . $e->getMessage());
        return false;
    }
}
?>