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
            $mail->Password   = 'kgre oqhy kxcn grid';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Obrigado por subscrever a nossa newsletter!';
            $mail->Body    = '
                <h2>Obrigado pela sua subscrição!</h2>
                <p>Ficamos felizes por tê-lo connosco. Em breve receberá novidades e ofertas especiais.</p>
                <p>Até breve,<br>Quinta Flores</p>
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
