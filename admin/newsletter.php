<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../conexao.php';
require '../vendor/autoload.php';

$mensagem_envio = '';

// Buscar modelos da BD para mostrar
$modelos_result = $conexao->query("SELECT * FROM modelos_newsletter ORDER BY MN_id DESC");
if (!$modelos_result) {
    die("Erro ao buscar modelos: " . $conexao->error);
}

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
                $mail->Password = 'cbra fjzb nizo lilw'; // Atenção à segurança disto
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('quinta.flores2019@gmail.com', 'Quinta Flores');
                $mail->addAddress($row['N_email']);

                $mail->isHTML(true);
                $mail->Subject = $assunto;
                $mail->Body = $mensagem;

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
    <title>Newsletter - Quinta Flores</title>
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
        });
    </script>
    <style>
        /* Só para dar destaque aos modelos e facilitar o drag/drop */
        #modelos-newsletter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .modelo {
            border: 1px solid #aaa;
            padding: 10px;
            width: 200px;
            cursor: grab;
            background: #f9f9f9;
            user-select: none;
            border-radius: 5px;
        }
        .modelo strong {
            display: block;
            margin-bottom: 5px;
        }
        .modelo small {
            color: #666;
        }
        #form-newsletter {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            max-width: 700px;
        }
    </style>
</head>
<body>
    <h2>Hóspedes Subscritos</h2>
    <a href="admin.php">← Voltar</a>
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

    <h3>Modelos de Newsletter (Arraste para o formulário para carregar)</h3>
    <div id="modelos-newsletter">
        <?php while ($modelo = $modelos_result->fetch_assoc()): ?>
            <div class="modelo" draggable="true"
                data-assunto="<?= htmlspecialchars($modelo['MN_titulo']) ?>"
                data-mensagem="<?= htmlspecialchars($modelo['MN_conteudo']) ?>"
                title="<?= htmlspecialchars($modelo['MN_descricao']) ?>">
                <strong><?= htmlspecialchars($modelo['MN_titulo']) ?></strong>
                <small><?= htmlspecialchars($modelo['MN_descricao']) ?></small>
            </div>
        <?php endwhile; ?>
    </div>

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
                <!-- REMOVIDO required daqui para evitar erro de validação -->
                <textarea id="mensagem" name="mensagem"></textarea>
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

            // Validação manual para a mensagem
            const conteudo = tinymce.get('mensagem').getContent({ format: 'text' }).trim();
            if (!conteudo) {
                e.preventDefault();
                alert('Por favor, preencha a mensagem.');
                return false;
            }
        });

        // Drag & Drop para modelos de newsletter
        const modelos = document.querySelectorAll('.modelo');
        const dropZone = document.getElementById('form-newsletter');

        modelos.forEach(modelo => {
            modelo.addEventListener('dragstart', e => {
                e.dataTransfer.setData('text/plain', ''); // necessário para Firefox
                e.dataTransfer.setData('assunto', modelo.getAttribute('data-assunto'));
                e.dataTransfer.setData('mensagem', modelo.getAttribute('data-mensagem'));
            });

            // Também permite clicar para carregar
            modelo.addEventListener('click', () => {
                document.getElementById('assunto').value = modelo.getAttribute('data-assunto');
                tinymce.get('mensagem').setContent(modelo.getAttribute('data-mensagem'));
                if (formNewsletter.style.display === 'none' || formNewsletter.style.display === '') {
                    formNewsletter.style.display = 'block';
                    btnToggle.textContent = 'Cancelar Envio';
                }
            });
        });

        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.style.border = '2px dashed #aaa';
        });

        dropZone.addEventListener('dragleave', e => {
            e.preventDefault();
            dropZone.style.border = '1px solid #ccc';
        });

        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.style.border = '1px solid #ccc';

            const assunto = e.dataTransfer.getData('assunto');
            const mensagem = e.dataTransfer.getData('mensagem');

            if (assunto && mensagem) {
                document.getElementById('assunto').value = assunto;
                tinymce.get('mensagem').setContent(mensagem);
                if (formNewsletter.style.display === 'none' || formNewsletter.style.display === '') {
                    formNewsletter.style.display = 'block';
                    btnToggle.textContent = 'Cancelar Envio';
                }
            }
        });
    </script>
</body>
</html>
