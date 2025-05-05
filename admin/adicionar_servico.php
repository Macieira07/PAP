<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = $_POST['nome_servico'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];

    // Validação de preço
    if (!is_numeric($preco) || $preco <= 0) {
        echo "O preço deve ser um valor positivo.";
        exit;
    }

    $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $nome_servico, $descricao, $preco);
    $stmt->execute();

    header("Location: servicos.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Adicionar Serviço</title>
    <script>
        function atualizarPreco() {
            var preco = 0;
            if (document.getElementById('servico_limpeza_casa').checked) {
                preco += 100;
            }
            if (document.getElementById('servico_limpeza_jardim').checked) {
                preco += 500;
            }
            document.getElementById('preco').value = preco.toFixed(2);
        }
    </script>
</head>
<body>
    <h2>Adicionar Novo Serviço</h2>
    <form method="post">
        Nome do Serviço: <input type="text" name="nome_servico" required><br><br>
        Descrição: <textarea name="descricao"></textarea><br><br>

        <h3>Escolha os Serviços:</h3>
        <label><input type="checkbox" id="servico_limpeza_casa" onclick="atualizarPreco()"> Serviço de Limpeza da Casa (100€)</label><br>
        <label><input type="checkbox" id="servico_limpeza_jardim" onclick="atualizarPreco()"> Serviço de Limpeza do Jardim (500€)</label><br><br>

        Preço (€): <input type="number" step="0.01" id="preco" name="preco" value="0" readonly required><br><br>
        <button type="submit">Salvar</button>
    </form>
    <a href="servicos.php">← Voltar</a>
</body>
</html>
