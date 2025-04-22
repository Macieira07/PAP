<?php
// Verifica se os arquivos do PHPMailer existem
$phpmailer_path = __DIR__ . '/PHPMailer/src/';
if (!file_exists($phpmailer_path . 'Exception.php')) {
    die("Erro: PHPMailer não encontrado no caminho especificado.");
}

require $phpmailer_path . 'Exception.php';
require $phpmailer_path . 'PHPMailer.php';
require $phpmailer_path . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmail($destino, $assunto, $mensagem) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'quinta.flores2019@gmail.com';
        $mail->Password   = 'svwy ziac roqo ygzw'; // Senha de app do Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Configurações adicionais de segurança
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Remetente e destinatário
        $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
        $mail->addAddress($destino);
        $mail->addReplyTo('quinta.flores2019@gmail.com', 'Informações');

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;
        $mail->AltBody = strip_tags($mensagem);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Erro ao enviar email para {$destino}: " . $e->getMessage());
        return false;
    }
}
?>