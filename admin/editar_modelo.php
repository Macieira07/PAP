<?php
include '../conexao.php';

$mensagem = '';
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    die("ID inválido");
}

$stmt = $conexao->prepare("SELECT MN_titulo, MN_descricao, MN_conteudo FROM modelos_newsletter WHERE MN_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    die("Modelo não encontrado");
}
$stmt->bind_result($titulo, $descricao, $conteudo);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';

    if (!$titulo || !$conteudo) {
        $mensagem = '<p style="color:red;">Título e conteúdo são obrigatórios.</p>';
    } else {
        $stmt = $conexao->prepare("UPDATE modelos_newsletter SET MN_titulo = ?, MN_descricao = ?, MN_conteudo = ? WHERE MN_id = ?");
        $stmt->bind_param("sssi", $titulo, $descricao, $conteudo, $id);
        if ($stmt->execute()) {
            header('Location: modelos_newsletter.php');
            exit;
        } else {
            $mensagem = '<p style="color:red;">Erro ao atualizar modelo.</p>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Editar Modelo Newsletter</title>
    <link rel="stylesheet" href="global.css" />
    <script src="https://cdn.tiny.cloud/1/mktwxkq2t7w5yim7b7gqo3ndcmusjcxuwkqkuhi8mwa08ux2/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#conteudo',
        plugins: 'image link media emoticons code',
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link media emoticons | code',
        menubar: false,
        height: 300,
    });
    </script>
</head>
<body>
    <h1>Editar Modelo Newsletter</h1>
    <?= $mensagem ?>

    <form method="POST" action="">
        <label for="titulo">Título:</label><br />
        <input type="text" id="titulo" name="titulo" required value="<?= htmlspecialchars($titulo) ?>" /><br /><br />

        <label for="descricao">Descrição curta (opcional):</label><br />
        <textarea id="descricao" name="descricao" rows="2" style="width:100%;"><?= htmlspecialchars($descricao) ?></textarea><br /><br />

        <label for="conteudo">Conteúdo HTML:</label><br />
        <textarea id="conteudo" name="conteudo" required><?= htmlspecialchars($conteudo) ?></textarea><br /><br />

        <button type="submit">Atualizar Modelo</button>
    </form>

    <p><a href="modelos_newsletter.php">Voltar à lista</a></p>
</body>
</html>
