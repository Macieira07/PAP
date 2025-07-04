<?php

/*
 * ============================================================
 *   Funções de Email (PHPMailer) - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, funções)
 *
 *   Bibliotecas e Frameworks:
 *     - PHPMailer (envio de emails)
 *
 *   Estrutura da Página:
 *     1. Função para enviar email de verificação
 *     2. Função para enviar email de recuperação de senha
 *     3. Outras funções utilitárias
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */

// ===================== 1. Funções de Email =====================
// Carrega as classes do PHPMailer
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Carrega as classes necessárias
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarEmail($to, $subject, $body, $attachments = [], $embeddedImages = [])
{
    try {
        $mail = new PHPMailer(true);

        // Configurações detalhadas
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Debug
        $mail->Debugoutput = function ($str, $level) {
            error_log("PHPMailer ($level): $str");
        };

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'quinta.flores2019@gmail.com';
        $mail->Password = 'cbra fjzb nizo lilw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;

        // Remetente e destinatário
        $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
        $mail->addAddress($to);

        // Imagem embutida do logotipo
        $mail->AddEmbeddedImage(__DIR__ . '/../assets/logos/logotipo1.png', 'logotipo_cid');

        // Embutir emojis (array de ['path' => caminho, 'cid' => cid])
        foreach ($embeddedImages as $img) {
            if (file_exists($img['path'])) {
                $mail->AddEmbeddedImage($img['path'], $img['cid']);
            }
        }

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Anexos extras
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }
        }

        error_log("Tentando enviar email para $to...");
        $mail->send();
        error_log("Email enviado com sucesso para $to");
        return true;
    } catch (Exception $e) {
        error_log('FALHA ao enviar email: ' . $e->getMessage());
        error_log('Detalhes PHPMailer: ' . $mail->ErrorInfo);
        return false;
    }
}
