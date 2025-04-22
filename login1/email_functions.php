<?php
// Carrega as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega as classes necessárias
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/**
 * Função para enviar emails usando o PHPMailer
 * 
 * @param string $to Endereço de email do destinatário
 * @param string $subject Assunto do email
 * @param string $body Corpo do email (HTML)
 * @param array $attachments Anexos opcionais (array de caminhos para arquivos)
 * @return bool True se o email foi enviado, False caso contrário
 */
function enviarEmail($to, $subject, $body, $attachments = []) {
    try {
        $mail = new PHPMailer(true);
        
        // Configurações do servidor SMTP
        $mail->SMTPDebug = 0;                       // 0 = desativado, 1 = mensagens cliente, 2 = mensagens cliente/servidor
        $mail->isSMTP();                            // Usar SMTP
        $mail->Host       = 'smtp.gmail.com';       // Servidor SMTP
        $mail->SMTPAuth   = true;                   // Habilitar autenticação SMTP
        $mail->Username   = 'quintafloreshotel@gmail.com';  // Seu email
        $mail->Password   = 'svdl gkwh wecb zbsl';  // Sua senha ou senha de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar TLS
        $mail->Port       = 587;                    // Porta TCP
        $mail->CharSet    = 'UTF-8';                // Conjunto de caracteres
        
        // Remetente e destinatário
        $mail->setFrom('quintafloreshotel@gmail.com', 'Quinta Flores');
        $mail->addAddress($to);
        
        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        // Adicionar anexos, se houver
        if (!empty($attachments) && is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        
        // Para ambiente de desenvolvimento, simula o envio e registra no log
        if ($_SERVER['SERVER_NAME'] == 'localhost' || 
            $_SERVER['HTTP_HOST'] == 'localhost' || 
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
            
            error_log("Simulando envio de email para: $to");
            error_log("Assunto: $subject");
            error_log("Conteúdo: " . substr($body, 0, 200) . "...");
            
            // Você pode desabilitar esta linha em produção para realmente enviar email
            return true; // Simula sucesso em ambiente local
        }
        
        // Envia o email
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Erro ao enviar email: {$mail->ErrorInfo}");
        return false;
    }
}
