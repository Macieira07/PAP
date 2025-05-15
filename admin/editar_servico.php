<?php
require '../conexao.php';

$id = $_GET['id'];

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

    $stmt = $conexao->prepare("UPDATE servicos SET S_nome=?, S_descricao=?, S_preco=?, categoria_id=? WHERE S_id_servico=?");
    $stmt->bind_param("ssdii", $nome_servico, $descricao, $preco, $categoria_servico, $id);
    $stmt->execute();

    header("Location: servicos.php");
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM servicos WHERE S_id_servico=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$servico = $resultado->fetch_assoc();

// Recupera as categorias de serviços para o dropdown
$stmt = $conexao->prepare("SELECT * FROM categorias_servico");
$stmt->execute();
$categorias = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Editar Serviço</title>
    <script>
        function atualizarPreco() {
            var preco = 0;
            var categoria = "outros"; // Categoria padrão

            if (document.getElementById('servico_limpeza_casa').checked) {
                preco += 100;
                categoria = "Serviços de Limpeza";
            }
            if (document.getElementById('servico_limpeza_jardim').checked) {
                preco += 500;
                categoria = "Serviços de Limpeza";
            }
            if (document.getElementById('servico_recepcao').checked) {
                preco += 50;
                categoria = "Serviços Básicos";
            }
            if (document.getElementById('servico_concierge').checked) {
                preco += 150;
                categoria = "Serviços de Luxo";
            }
            if (document.getElementById('servico_deposito_bagagem').checked) {
                preco += 30;
                categoria = "Serviços Adicionais";
            }
            if (document.getElementById('servico_lavanderia').checked) {
                preco += 80;
                categoria = "Serviços Adicionais";
            }
            if (document.getElementById('servico_caixa_segurança').checked) {
                preco += 20;
                categoria = "Serviços de Segurança";
            }
            if (document.getElementById('servico_wifi').checked) {
                preco += 10;
                categoria = "Tecnologia";
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
    <h1>Editar Serviço</h1>

    <form method="post">
        Nome do Serviço: <input type="text" name="nome_servico" value="<?= $servico['S_nome'] ?>" required><br><br>
        Descrição: <textarea name="descricao"><?= $servico['S_descricao'] ?></textarea><br><br>

        <h3>Escolha os Serviços:</h3>
        <label><input type="checkbox" id="servico_limpeza_casa" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Limpeza da Casa') !== false) ? 'checked' : '' ?>> Limpeza da Casa (100€)</label><br>
        <label><input type="checkbox" id="servico_limpeza_jardim" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Limpeza do Jardim') !== false) ? 'checked' : '' ?>> Limpeza do Jardim (500€)</label><br>
        <label><input type="checkbox" id="servico_recepcao" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Recepção 24h') !== false) ? 'checked' : '' ?>> Recepção 24h (50€)</label><br>
        <label><input type="checkbox" id="servico_concierge" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Concierge') !== false) ? 'checked' : '' ?>> Concierge (150€)</label><br>
        <label><input type="checkbox" id="servico_deposito_bagagem" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Depósito de Bagagens') !== false) ? 'checked' : '' ?>> Depósito de Bagagens (30€)</label><br>
        <label><input type="checkbox" id="servico_lavanderia" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Lavanderia') !== false) ? 'checked' : '' ?>> Lavanderia (80€)</label><br>
        <label><input type="checkbox" id="servico_caixa_segurança" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Caixa de Segurança') !== false) ? 'checked' : '' ?>> Caixa de Segurança (20€)</label><br>
        <label><input type="checkbox" id="servico_wifi" onclick="atualizarPreco()" <?= (strpos($servico['S_descricao'], 'Wi-Fi Gratuito') !== false) ? 'checked' : '' ?>> Wi-Fi Gratuito (10€)</label><br><br>

        Categoria: 
        <select name="categoria_servico" id="categoria_servico" required>
            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                <option value="<?= $categoria['id'] ?>" <?= ($categoria['id'] == $servico['categoria_id']) ? 'selected' : '' ?>><?= $categoria['nome'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        Preço (€): <input type="number" step="0.01" id="preco" name="preco" value="<?= $servico['S_preco'] ?>" readonly required><br><br>
        <button type="submit">Salvar Alterações</button>
    </form>

    <a href="servicos.php">← Voltar</a>
</body>
</html>
