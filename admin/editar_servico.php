<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = $_POST['nome_servico'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria_servico = $_POST['categoria_servico'];

    if (!is_numeric($preco) || $preco <= 0) {
        echo "O preço deve ser um valor positivo.";
        exit;
    }

    // Preparar e executar o UPDATE
    $stmt = $conexao->prepare("UPDATE servicos SET S_nome=?, S_descricao=?, S_preco=?, S_categoria_id=? WHERE S_id_servico=?");
    $stmt->bind_param("ssdii", $nome_servico, $descricao, $preco, $categoria_servico, $id);
    $stmt->execute();

    header("Location: servicos.php");
    exit;
}

// Buscar o serviço atual
$stmt = $conexao->prepare("SELECT * FROM servicos WHERE S_id_servico=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$servico = $resultado->fetch_assoc();

// Buscar categorias para o select
$stmt = $conexao->prepare("SELECT * FROM categorias_servico");
$stmt->execute();
$categorias = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Serviço</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <script>
        function atualizarPrecoEDescricao() {
            var preco = 0;
            var descricao = [];
            var categoria = "outros";
            var nomes = [];

            if (document.getElementById('servico_limpeza_casa').checked) {
                preco += 100;
                descricao.push("Limpeza da Casa");
                nomes.push("Limpeza da Casa");
                categoria = "Serviços de Limpeza";
            }
            if (document.getElementById('servico_limpeza_jardim').checked) {
                preco += 500;
                descricao.push("Limpeza do Jardim");
                nomes.push("Limpeza do Jardim");
                categoria = "Serviços de Limpeza";
            }
            if (document.getElementById('servico_recepcao').checked) {
                preco += 50;
                descricao.push("Recepção 24h");
                nomes.push("Recepção 24h");
                categoria = "Serviços Básicos";
            }
            if (document.getElementById('servico_concierge').checked) {
                preco += 150;
                descricao.push("Concierge");
                nomes.push("Concierge");
                categoria = "Serviços de Luxo";
            }
            if (document.getElementById('servico_deposito_bagagem').checked) {
                preco += 30;
                descricao.push("Depósito de Bagagens");
                nomes.push("Depósito de Bagagens");
                categoria = "Serviços Adicionais";
            }
            if (document.getElementById('servico_lavanderia').checked) {
                preco += 80;
                descricao.push("Lavanderia");
                nomes.push("Lavanderia");
                categoria = "Serviços Adicionais";
            }
            if (document.getElementById('servico_caixa_segurança').checked) {
                preco += 20;
                descricao.push("Caixa de Segurança");
                nomes.push("Caixa de Segurança");
                categoria = "Serviços de Segurança";
            }
            if (document.getElementById('servico_wifi').checked) {
                preco += 10;
                descricao.push("Wi-Fi Gratuito");
                nomes.push("Wi-Fi Gratuito");
                categoria = "Tecnologia";
            }

            document.getElementById('preco').value = preco.toFixed(2);
            document.getElementById('descricao').value = descricao.join(', ');
            document.getElementById('nome_servico').value = nomes.join(' + ');

            // Atualizar a categoria automaticamente
            var categoriaSelect = document.getElementById("categoria_servico");
            for (var i = 0; i < categoriaSelect.options.length; i++) {
                if (categoriaSelect.options[i].text === categoria) {
                    categoriaSelect.selectedIndex = i;
                    break;
                }
            }
        }

        window.onload = function() {
            atualizarPrecoEDescricao();
        };
    </script>
</head>
<body>
    <h1>Editar Serviço</h1>

    <form method="post">
        Nome do Serviço: <input type="text" name="nome_servico" id="nome_servico" value="<?= htmlspecialchars($servico['S_nome']) ?>" required><br><br>
        Descrição: <textarea name="descricao" id="descricao" readonly><?= htmlspecialchars($servico['S_descricao']) ?></textarea><br><br>

        <h3>Escolha os Serviços:</h3>
        <label><input type="checkbox" id="servico_limpeza_casa" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Limpeza da Casa') !== false) ? 'checked' : '' ?>> Limpeza da Casa (100€)</label><br>
        <label><input type="checkbox" id="servico_limpeza_jardim" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Limpeza do Jardim') !== false) ? 'checked' : '' ?>> Limpeza do Jardim (500€)</label><br>
        <label><input type="checkbox" id="servico_recepcao" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Recepção 24h') !== false) ? 'checked' : '' ?>> Recepção 24h (50€)</label><br>
        <label><input type="checkbox" id="servico_concierge" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Concierge') !== false) ? 'checked' : '' ?>> Concierge (150€)</label><br>
        <label><input type="checkbox" id="servico_deposito_bagagem" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Depósito de Bagagens') !== false) ? 'checked' : '' ?>> Depósito de Bagagens (30€)</label><br>
        <label><input type="checkbox" id="servico_lavanderia" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Lavanderia') !== false) ? 'checked' : '' ?>> Lavanderia (80€)</label><br>
        <label><input type="checkbox" id="servico_caixa_segurança" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Caixa de Segurança') !== false) ? 'checked' : '' ?>> Caixa de Segurança (20€)</label><br>
        <label><input type="checkbox" id="servico_wifi" onclick="atualizarPrecoEDescricao()" <?= (strpos($servico['S_descricao'], 'Wi-Fi Gratuito') !== false) ? 'checked' : '' ?>> Wi-Fi Gratuito (10€)</label><br><br>

        Categoria: 
        <select name="categoria_servico" id="categoria_servico" required>
            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                <option value="<?= $categoria['id'] ?>" <?= ($categoria['id'] == $servico['S_categoria_id']) ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nome']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        Preço (€): <input type="number" step="0.01" id="preco" name="preco" value="<?= htmlspecialchars($servico['S_preco']) ?>" readonly required><br><br>
        <button type="submit">Salvar Alterações</button>
    </form>

    <a href="servicos.php">← Voltar</a>
</body>
</html>
