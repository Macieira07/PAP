<?php
require '../conexao.php';

// Recupera as categorias de serviços para o dropdown
$stmt = $conexao->prepare("SELECT * FROM categorias_servico");
$stmt->execute();
$categorias = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = $_POST['nome_servico'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria_servico = $_POST['categoria_servico'];

    // Validação de preço
    if (!is_numeric($preco) || $preco <= 0) {
        echo "O preço deve ser um valor positivo.";
        exit;
    }

    // Inserção de serviço
    $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco, categoria_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $nome_servico, $descricao, $preco, $categoria_servico);
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
            var categoria = "outros"; // Categoria padrão

            if (document.getElementById('servico_limpeza_casa').checked) {
                preco += 100;
                categoria = "Serviços de Limpeza"; // Alterando para Limpeza
            }
            if (document.getElementById('servico_limpeza_jardim').checked) {
                preco += 500;
                categoria = "Serviços de Limpeza"; // Alterando para Limpeza
            }
            if (document.getElementById('servico_recepcao').checked) {
                preco += 50;
                categoria = "Serviços Básicos"; // Alterando para Serviços Básicos
            }
            if (document.getElementById('servico_concierge').checked) {
                preco += 150;
                categoria = "Serviços de Luxo"; // Alterando para Serviços de Luxo
            }
            if (document.getElementById('servico_deposito_bagagem').checked) {
                preco += 30;
                categoria = "Serviços Adicionais"; // Alterando para Serviços Adicionais
            }
            if (document.getElementById('servico_lavanderia').checked) {
                preco += 80;
                categoria = "Serviços Adicionais"; // Alterando para Serviços Adicionais
            }
            if (document.getElementById('servico_caixa_segurança').checked) {
                preco += 20;
                categoria = "Serviços de Segurança"; // Alterando para Serviços de Segurança
            }
            if (document.getElementById('servico_wifi').checked) {
                preco += 10;
                categoria = "Tecnologia"; // Alterando para Tecnologia
            }

            // Atualiza a categoria no select
            var categoriaSelect = document.getElementById("categoria_servico");
            for (var i = 0; i < categoriaSelect.options.length; i++) {
                if (categoriaSelect.options[i].text === categoria) {
                    categoriaSelect.selectedIndex = i;
                    break;
                }
            }

            document.getElementById('preco').value = preco.toFixed(2);
        }
    </script>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=rk8gMHQsBQHb&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Adicionar Serviço</h1>
    </div>

    <form method="post">
        Nome do Serviço: <input type="text" name="nome_servico" required><br><br>
        Descrição: <textarea name="descricao"></textarea><br><br>

        <h3>Escolha os Serviços:</h3>
        <label><input type="checkbox" id="servico_limpeza_casa" onclick="atualizarPreco()"> Limpeza da Casa (100€)</label><br>
        <label><input type="checkbox" id="servico_limpeza_jardim" onclick="atualizarPreco()"> Limpeza do Jardim (500€)</label><br>
        <label><input type="checkbox" id="servico_recepcao" onclick="atualizarPreco()"> Recepção 24h (50€)</label><br>
        <label><input type="checkbox" id="servico_concierge" onclick="atualizarPreco()"> Concierge (150€)</label><br>
        <label><input type="checkbox" id="servico_deposito_bagagem" onclick="atualizarPreco()"> Depósito de Bagagens (30€)</label><br>
        <label><input type="checkbox" id="servico_lavanderia" onclick="atualizarPreco()"> Lavanderia (80€)</label><br>
        <label><input type="checkbox" id="servico_caixa_segurança" onclick="atualizarPreco()"> Caixa de Segurança (20€)</label><br>
        <label><input type="checkbox" id="servico_wifi" onclick="atualizarPreco()"> Wi-Fi Gratuito (10€)</label><br><br>

        Categoria: 
        <select name="categoria_servico" id="categoria_servico" required>
            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                <option value="<?= $categoria['id'] ?>"><?= $categoria['nome'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        Preço (€): <input type="number" step="0.01" id="preco" name="preco" readonly required><br><br>
        <button type="submit">Adicionar Serviço</button>
    </form>

    <a href="servicos.php">← Voltar</a>
</body>
</html>
