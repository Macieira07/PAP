<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');

    if (!$titulo || !$conteudo) {
        $mensagem = '<p style="color:red;">Título e conteúdo são obrigatórios.</p>';
    } else {
        $stmt = $conexao->prepare("INSERT INTO modelos_newsletter (MN_titulo, MN_descricao, MN_conteudo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $titulo, $descricao, $conteudo);

        if ($stmt->execute()) {
            header('Location: modelos_newsletter.php');
            exit;
        } else {
            $mensagem = '<p style="color:red;">Erro ao adicionar modelo.</p>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Adicionar Modelo Newsletter</title>
    <link rel="stylesheet" href="global.css" />
    <script src="https://cdn.tiny.cloud/1/mktwxkq2t7w5yim7b7gqo3ndcmusjcxuwkqkuhi8mwa08ux2/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#conteudo',
        menubar: false,
        plugins: 'image link media emoticons code',
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | image link media emoticons | code',
        height: 300
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('formModelo').addEventListener('submit', function (e) {
            tinymce.triggerSave(); // sincroniza conteúdo no textarea

            const titulo = document.getElementById('titulo').value.trim();
            const conteudo = document.getElementById('conteudo').value.trim();

            if (!titulo) {
                alert('O título é obrigatório!');
                e.preventDefault();
                return false;
            }
            if (!conteudo) {
                alert('O conteúdo é obrigatório!');
                e.preventDefault();
                return false;
            }
        });
    });
    </script>
</head>
<body>
    <a href="modelos_newsletter.php">← Voltar</a>
    <h1>Adicionar Modelo Newsletter</h1>
    <?= $mensagem ?>

    <form id="formModelo" method="POST" action="">
        <label for="titulo">Título:</label><br />
        <input type="text" id="titulo" name="titulo" required /><br /><br />

        <label for="descricao">Descrição curta (opcional):</label><br />
        <textarea id="descricao" name="descricao" rows="2" style="width:100%;"></textarea><br /><br />

        <label for="conteudo">Conteúdo HTML:</label><br />
        <textarea id="conteudo" name="conteudo"></textarea><br /><br />

        <button type="submit">Adicionar Modelo</button>
    </form>

    <p><a href="modelos_newsletter.php">Voltar à lista</a></p>
</body>
</html>
