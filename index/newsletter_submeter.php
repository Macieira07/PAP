<?php
include '../conexao.php';   
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ajusta o caminho se necessário

header('Content-Type: application/json');
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    // Validação
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['mensagem' => 'Email inválido.', 'tipo' => 'erro']);
        exit;
    }
    // Verificar duplicado
    $stmt = $conexao->prepare("SELECT 1 FROM newsletter WHERE N_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['mensagem' => 'Este email já está subscrito.', 'tipo' => 'erro']);
        exit;
    }
    // Inserir
    $stmt = $conexao->prepare("INSERT INTO newsletter (N_email) VALUES (?)");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        // Enviar email de agradecimento
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'quinta.flores2019@gmail.com';
            $mail->Password   = 'cbra fjzb nizo lilw';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
            $mail->addAddress($email);
$mail->AddEmbeddedImage('../assets/logos/logotipo1.png', 'logotipo');
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->isHTML(true);
$mail->Subject = '🎉 Bem-vindo à Quinta Flores!';
$mail->Body = '
    <div style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; border-radius: 10px;">
        <div style="text-align: center;">
            <img src="cid:logotipo" alt="Quinta Flores" style="max-width: 200px; margin-bottom: 20px;">
            <h2 style="color: #2d8659;">Obrigado por subscrever a nossa newsletter</h2>
        </div>

        <p style="font-size: 16px; color: #333;">
            É com muito gosto que o(a) recebemos na comunidade Quinta Flores.<br><br>
            A partir de agora, receberá no seu e-mail informações relevantes sobre o nosso alojamento, novidades exclusivas e campanhas especiais para os nossos subscritores.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://www.instagram.com/quintaflores19/?utm_source=ig_web_button_share_sheet" style="background-color: #2d8659; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                Siga-nos no Instagram
            </a>
        </div>

        <p style="font-size: 14px; color: #666; text-align: center;">
            Com os melhores cumprimentos,<br>
            <strong>Quinta Flores</strong><br>
            📞 919 241 169 · ✉️ quinta.flores2019@gmail.com
        </p>
    </div>
';
            $mail->send();
            echo json_encode(['mensagem' => 'Subscreveste com sucesso! Verifica o teu email.', 'tipo' => 'sucesso']);
        } catch (Exception $e) {
            echo json_encode([
                'mensagem' => "Subscreveste, mas erro ao enviar email: {$mail->ErrorInfo}",
                'tipo'     => 'erro'
            ]);
        }
    } else {
        echo json_encode(['mensagem' => 'Ocorreu um erro ao subscrever.', 'tipo' => 'erro']);
    }

    $stmt->close();
    $conexao->close();
} else {
    echo json_encode(['mensagem' => 'Requisição inválida.', 'tipo' => 'erro']);
}
