<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../pagamento/PHPMailer/src/PHPMailer.php';
require '../pagamento/PHPMailer/src/SMTP.php';
require '../pagamento/PHPMailer/src/Exception.php';

$mail = new PHPMailer(true); // <-- Faltava esta linha!

try {
    // Debug e configuração SMTP
    $mail->SMTPDebug = 2; // Mostra detalhes da conexão
    $mail->Debugoutput = 'html'; // Formato legível no navegador

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'quinta.flores2019@gmail.com'; // Teu Gmail
    $mail->Password   = 'kgre oqhy kxcn grid'; // Senha da aplicação!
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Dados do formulário
    $nome = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $assunto = htmlspecialchars($_POST['subject'] ?? '');
    $mensagem = htmlspecialchars($_POST['message'] ?? '');

    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        mostrarMensagem("Por favor preencha todos os campos.", "erro");
        exit;
    }

    // Configuração do email
    $mail->setFrom($email, $nome);
    $mail->addAddress('quinta.flores2019@gmail.com'); // Teu email de destino

    $mail->Subject = "Mensagem do site: $assunto";
    $mail->Body    = "Nome: $nome\nEmail: $email\n\nMensagem:\n$mensagem";

    $mail->send();

    mostrarMensagem("Mensagem enviada com sucesso! Responderemos em breve.", "sucesso");

} catch (Exception $e) {
    mostrarMensagem("Erro ao enviar mensagem. Detalhe: {$mail->ErrorInfo}", "erro");
}

// Função de mensagens
function mostrarMensagem($mensagem, $tipo) {
    $cor = $tipo === "sucesso" ? "#d4edda" : "#f8d7da";
    $borda = $tipo === "sucesso" ? "#c3e6cb" : "#f5c6cb";
    $texto = $tipo === "sucesso" ? "#155724" : "#721c24";

    echo "<div style='
        background-color: $cor;
        color: $texto;
        border: 1px solid $borda;
        padding: 15px;
        margin: 20px auto;
        max-width: 600px;
        border-radius: 5px;
        font-family: Arial, sans-serif;
        text-align: center;
    '>$mensagem</div>";
}
?>
