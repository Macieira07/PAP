<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = $_POST['nome_servico'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];

    // Validação de preço
    if (!is_numeric($preco) || $preco <= 0) {
        echo "O preço deve ser um valor positivo.";
        exit;
    }

    $stmt = $conexao->prepare("UPDATE servicos SET S_nome=?, S_descricao=?, S_preco=? WHERE S_id_servico=?");
    $stmt->bind_param("ssdi", $nome_servico, $descricao, $preco, $id);
    $stmt->execute();

    header("Location: servicos.php");
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM servicos WHERE S_id_servico=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$servico = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Editar Serviço</title>
    <script>
        function atualizarPreco() {
            var preco = 0;
            if (document.getElementById('servico_limpeza_casa').checked) {
                preco += 100;
            }
            if (document.getElementById('servico_limpeza_jardim').checked) {
                preco += 500;
            }
            if (document.getElementById('servico_recepcao').checked) {
                preco += 50;
            }
            if (document.getElementById('servico_concierge').checked) {
                preco += 150;
            }
            if (document.getElementById('servico_deposito_bagagem').checked) {
                preco += 30;
            }
            if (document.getElementById('servico_lavanderia').checked) {
                preco += 80;
            }
            if (document.getElementById('servico_caixa_segurança').checked) {
                preco += 20;
            }
            if (document.getElementById('servico_wifi').checked) {
                preco += 10;
            }
            document.getElementById('preco').value = preco.toFixed(2);
        }

        window.onload = function() {
            // Definir os valores dos checkboxes com base no serviço atual
            if (<?= $servico['S_preco'] ?> == 100) {
                document.getElementById('servico_limpeza_casa').checked = true;
            }
            if (<?= $servico['S_preco'] ?> == 500) {
                document.getElementById('servico_limpeza_jardim').checked = true;
            }
            // Adicionar mais verificações para outras opções de serviços conforme necessário
            atualizarPreco(); // Atualiza o preço inicial
        };
    </script>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=rk8gMHQsBQHb&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Editar Serviço</h1>
    </div>
    <form method="post">
        Nome do Serviço: <input type="text" name="nome_servico" value="<?= $servico['S_nome'] ?>" required><br><br>
        Descrição: <textarea name="descricao"><?= $servico['S_descricao'] ?></textarea><br><br>

        <h3>Escolha os Serviços:</h3>
        <label><input type="checkbox" id="servico_limpeza_casa" onclick="atualizarPreco()"> Serviço de Limpeza da Casa (100€)</label><br>
        <label><input type="checkbox" id="servico_limpeza_jardim" onclick="atualizarPreco()"> Serviço de Limpeza do Jardim (500€)</label><br>
        <label><input type="checkbox" id="servico_recepcao" onclick="atualizarPreco()"> Recepção 24h (50€)</label><br>
        <label><input type="checkbox" id="servico_concierge" onclick="atualizarPreco()"> Serviço de Concierge (150€)</label><br>
        <label><input type="checkbox" id="servico_deposito_bagagem" onclick="atualizarPreco()"> Depósito de Bagagens (30€)</label><br>
        <label><input type="checkbox" id="servico_lavanderia" onclick="atualizarPreco()"> Serviço de Lavanderia (80€)</label><br>
        <label><input type="checkbox" id="servico_caixa_segurança" onclick="atualizarPreco()"> Caixa de Segurança (20€)</label><br>
        <label><input type="checkbox" id="servico_wifi" onclick="atualizarPreco()"> Wi-Fi Gratuito (10€)</label><br><br>

        Preço (€): <input type="number" step="0.01" id="preco" name="preco" value="<?= $servico['S_preco'] ?>" readonly required><br><br>
        <button type="submit">Atualizar</button>
    </form>
    <a href="servicos.php">← Voltar</a>
</body>
</html>
