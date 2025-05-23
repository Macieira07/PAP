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
    $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco, S_categoria_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $nome_servico, $descricao, $preco, $categoria_servico);
    $stmt->execute();

    header("Location: servicos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Serviço</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    
    <script>
    const servicos = {
        servico_limpeza_casa: {
            nome: "Limpeza da Casa",
            preco: 100,
            descricao: "Serviço completo de limpeza da casa.",
            categoria: "Serviços de Limpeza"
        },
        servico_limpeza_jardim: {
            nome: "Limpeza do Jardim",
            preco: 500,
            descricao: "Manutenção e limpeza do jardim.",
            categoria: "Serviços de Limpeza"
        },
        servico_recepcao: {
            nome: "Recepção 24h",
            preco: 50,
            descricao: "Atendimento disponível 24 horas por dia.",
            categoria: "Serviços Básicos"
        },
        servico_concierge: {
            nome: "Concierge",
            preco: 150,
            descricao: "Serviço de apoio personalizado para hóspedes.",
            categoria: "Serviços de Luxo"
        },
        servico_deposito_bagagem: {
            nome: "Depósito de Bagagens",
            preco: 30,
            descricao: "Guarda segura de bagagens.",
            categoria: "Serviços Adicionais"
        },
        servico_lavanderia: {
            nome: "Lavanderia",
            preco: 80,
            descricao: "Serviço de lavagem e secagem de roupas.",
            categoria: "Serviços Adicionais"
        },
        servico_caixa_segurança: {
            nome: "Caixa de Segurança",
            preco: 20,
            descricao: "Armazenamento seguro de objetos de valor.",
            categoria: "Serviços de Segurança"
        },
        servico_wifi: {
            nome: "Wi-Fi Gratuito",
            preco: 10,
            descricao: "Acesso gratuito à internet sem fios.",
            categoria: "Tecnologia"
        }
    };

    function atualizarPreco() {
        let precoTotal = 0;
        let nomesSelecionados = [];
        let descricoesSelecionadas = [];
        let ultimaCategoria = "outros";

        for (let id in servicos) {
            const checkbox = document.getElementById(id);
            if (checkbox && checkbox.checked) {
                const servico = servicos[id];
                precoTotal += servico.preco;
                nomesSelecionados.push(servico.nome);
                descricoesSelecionadas.push(servico.descricao);
                ultimaCategoria = servico.categoria;
            }
        }

        document.getElementById('nome_servico').value = nomesSelecionados.join(" + ");
        document.getElementById('descricao').value = descricoesSelecionadas.join("\n");
        document.getElementById('preco').value = precoTotal.toFixed(2);

        const categoriaSelect = document.getElementById("categoria_servico");
        for (let i = 0; i < categoriaSelect.options.length; i++) {
            if (categoriaSelect.options[i].text === ultimaCategoria) {
                categoriaSelect.selectedIndex = i;
                break;
            }
        }
    }
    </script>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=rk8gMHQsBQHb&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Adicionar Serviço</h1>
    </div>

    <form method="post">
        Nome do Serviço: <input type="text" id="nome_servico" name="nome_servico" required><br><br>
        Descrição: <textarea id="descricao" name="descricao"></textarea><br><br>

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
