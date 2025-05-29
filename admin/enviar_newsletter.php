<?php
require '../vendor/autoload.php'; // tem de estar fora do if
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include '../conexao.php';

    $assunto = $_POST['assunto'];
    $mensagem = $_POST['mensagem'];

    $emails = $conexao->query("SELECT N_email FROM newsletter");

    $falhas = [];
    while ($row = $emails->fetch_assoc()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'quinta.flores2019@gmail.com';
            $mail->Password = 'kgre oqhy kxcn grid'; // cuidado com esta senha!
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
            $mail->addAddress($row['N_email']);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = nl2br($mensagem);

            $mail->send();
        } catch (Exception $e) {
            $falhas[] = $row['N_email'];
        }
    }

    if (empty($falhas)) {
        echo "Todos os emails foram enviados com sucesso!";
    } else {
        echo "Houve falha nos seguintes emails: " . implode(", ", $falhas);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Enviar Newsletter</title>
</head>
<body>
    <h2>Enviar Newsletter para todos os subscritores</h2>
    <form method="POST">
        <label>Assunto:</label><br>
        <input type="text" name="assunto" required><br><br>

        <label>Mensagem:</label><br>
        <textarea name="mensagem" rows="10" cols="60" required></textarea><br><br>

        <button type="submit">Enviar</button>
    </form>
</body>
</html>
