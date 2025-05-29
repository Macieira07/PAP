<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../conexao.php';
require '../vendor/autoload.php';

$mensagem_envio = '';


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['enviar_newsletter'])) {
    $assunto = $_POST['assunto'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';
   


    if (!$assunto || !$mensagem) {
        $mensagem_envio = '<p style="color:red;">Assunto e mensagem são obrigatórios.</p>';
    } else {
        $emails = $conexao->query("SELECT N_email FROM newsletter");
        if (!$emails) {
            die("Erro ao buscar emails: " . $conexao->error);
        }

        $falhas = [];

        while ($row = $emails->fetch_assoc()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'quinta.flores2019@gmail.com';
                $mail->Password = 'kgre oqhy kxcn grid';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
                $mail->addAddress($row['N_email']);

                $mail->isHTML(true);
                $mail->Subject = $assunto;
                $mail->Body = $mensagem; // mensagem já em HTML

                $mail->send();
            } catch (Exception $e) {
                $falhas[] = $row['N_email'];
            }
        }

        if (empty($falhas)) {
            $mensagem_envio = '<p style="color:green;">Todos os emails foram enviados com sucesso!</p>';
        } else {
            $mensagem_envio = '<p style="color:red;">Falha ao enviar para: ' . implode(", ", $falhas) . '</p>';
        }
    }
}

$resultado = $conexao->query("SELECT * FROM newsletter ORDER BY N_id DESC");
if (!$resultado) {
    die("Erro na consulta: " . $conexao->error);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="admin.css">
    <title>Newsletter com Editor Rich Text</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/mktwxkq2t7w5yim7b7gqo3ndcmusjcxuwkqkuhi8mwa08ux2/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#mensagem',
            plugins: 'image link media emoticons code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link media emoticons | code',
            menubar: false,
            height: 300,
            /* Configuração para upload local de imagens ficaria aqui */
        });
    </script>
</head>
<body>
    <h2>Hóspedes Subscritos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Data Subscrição</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['N_id']) ?></td>
                <td><?= htmlspecialchars($row['N_email']) ?></td>
                <td><?= htmlspecialchars($row['N_data_subscricao']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <button id="btn-toggle-form">Enviar Newsletter</button>

    <div id="form-newsletter" style="display:none;">
        <?= $mensagem_envio ?>
        <form method="POST" action="">
            <div class="form-group input-icon">
                <label for="assunto"><i class="fa-solid fa-envelope"></i> Assunto</label>
                <input type="text" id="assunto" name="assunto" required placeholder="Assunto da newsletter" />
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="form-group">
                <label for="mensagem"><i class="fa-solid fa-message"></i> Mensagem</label>
                <textarea id="mensagem" name="mensagem" required></textarea>
            </div>
            <button type="submit" name="enviar_newsletter" value="1">
    <i class="fa-solid fa-paper-plane"></i> Enviar
</button>

        </form>
    </div>

    <script>
        const btnToggle = document.getElementById('btn-toggle-form');
        const formNewsletter = document.getElementById('form-newsletter');

        btnToggle.addEventListener('click', () => {
            if (formNewsletter.style.display === 'none' || formNewsletter.style.display === '') {
                formNewsletter.style.display = 'block';
                btnToggle.textContent = 'Cancelar Envio';
            } else {
                formNewsletter.style.display = 'none';
                btnToggle.textContent = 'Enviar Newsletter';
            }
        });

        <?php if ($mensagem_envio): ?>
            formNewsletter.style.display = 'block';
            btnToggle.textContent = 'Cancelar Envio';
        <?php endif; ?>

        // Sincroniza o conteúdo do TinyMCE com o textarea antes de enviar o formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            tinymce.triggerSave();
        });
    </script>
</body>
</html>
