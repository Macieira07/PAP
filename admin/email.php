<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Certifica-te que este caminho está correto

function enviarEmailCodigo($destinatarioEmail, $destinatarioNome, $codigo) {
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'quinta.flores2019@gmail.com'; // O teu e-mail
        $mail->Password   = 'cbra fjzb nizo lilw'; // Palavra-passe da aplicação
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Remetente e destinatário
        $mail->setFrom('quinta.flores2019@gmail.com', 'Alojamento Quinta Flores');
        $mail->addAddress($destinatarioEmail, $destinatarioNome);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = 'Bem-vindo à Quinta Flores!';

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h2>Bem-vindo(a), $destinatarioNome!</h2>
                <p>A sua conta foi criada com sucesso na plataforma da <strong>Quinta Flores</strong>.</p>

                <p><strong>Dados de acesso:</strong></p>
                <ul>
                    <li><strong>Email:</strong> $destinatarioEmail</li>
                    <li><strong>Código de acesso:</strong> $codigo</li>
                </ul>

                <p>Utilize estes dados para iniciar sessão na sua conta.</p>

                <br>
                <p style='font-size: 14px;'>Obrigado por se registar connosco.<br>
                Equipa da Quinta Flores 🌸</p>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return "Erro ao enviar email: {$mail->ErrorInfo}";
    }
}
