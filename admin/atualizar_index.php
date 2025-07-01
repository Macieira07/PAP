<?php
session_start();

$aboutFile = __DIR__ . '/../data/about.json';
$uploadDir = __DIR__ . '/../assets/images/';

// Carregar dados JSON atualizado (em memória para ler e atualizar)
$aboutData = file_exists($aboutFile) ? json_decode(file_get_contents($aboutFile), true) : [];

// Função para obter imagem completa com caminho, fallback para default
function getImage($key, $default = 'default.jpg') {
    global $aboutData;
    return '../assets/images/' . ($aboutData[$key] ?? $default);
}

// Processar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_image'])) {
    $imageKey = $_POST['image_key'] ?? '';

    if ($imageKey && isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid() . '.' . $ext;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $destination)) {
            // Atualizar o JSON no array e ficheiro
            $aboutData[$imageKey] = $newFileName;
            file_put_contents($aboutFile, json_encode($aboutData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $_SESSION['message'] = 'Imagem atualizada com sucesso!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Erro no upload da imagem.';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = 'Nenhuma imagem selecionada ou erro no upload.';
        $_SESSION['message_type'] = 'error';
    }
    header('Location: atualizar_index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Atualizar Imagens</title>
    <link rel="stylesheet" href="global.css"/>
    <style>
        .image-update {
            background: var(--cor-card);
            border-radius: var(--raio-borda);
            box-shadow: var(--sombra-suave);
            padding: 18px 16px;
            margin-bottom: 30px;
            max-width: 400px;
        }
        .image-update h3 {
            margin-top: 0;
            color: var(--cor-titulo);
        }
        .image-update img {
            display: block;
            margin-bottom: 10px;
            max-width: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(46,80,144,0.10);
        }
        .image-update form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        hr {
            border: none;
            border-top: 1.5px solid var(--cor-borda);
            margin: 24px 0;
        }
    </style>
</head>
<body>
    <h1>Atualizar Imagens</h1>
    <a href="admin.php">← Voltar</a> 
    <?php if(isset($_SESSION['message'])): ?>
        <div class="message <?= $_SESSION['message_type'] ?>"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    <?php if(empty($aboutData)): ?>
        <p>Nenhuma imagem para mostrar.</p>
    <?php else: ?>
        <div style="display: flex; flex-wrap: wrap; gap: 32px;">
        <?php foreach($aboutData as $key => $image): ?>
            <div class="image-update">
                <h3><?= htmlspecialchars($key) ?></h3>
                <img src="<?= htmlspecialchars(getImage($key)) ?>" alt="<?= htmlspecialchars($key) ?>" />
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="image_key" value="<?= htmlspecialchars($key) ?>">
                    <input type="file" name="new_image" accept="image/*" required>
                    <button type="submit" name="update_image">Atualizar</button>
                </form>
                
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
