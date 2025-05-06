<?php
require '../conexao.php';

$descricao_servicos = [
    'servico_recepcao' => 'Recepção 24h: Atendimento ao cliente disponível 24 horas.',
    'servico_concierge' => 'Serviço de Concierge: Reservas de restaurantes, passeios e mais.',
    'servico_deposito_bagagem' => 'Depósito de Bagagens: Armazenamento seguro para sua bagagem.',
    'servico_lavanderia' => 'Serviço de Lavanderia: Roupas lavadas e engomadas.',
    'servico_caixa_segurança' => 'Caixa de Segurança: Proteja seus itens pessoais.',
    'servico_wifi' => 'Wi-Fi Gratuito: Acesso à internet sem custo.',
    'servico_transfer' => 'Transfer para o aeroporto: Transporte para o aeroporto ou estação.',
    'servico_quarto' => 'Serviço de Quarto: Refeições entregues diretamente no quarto.',
    'servico_bicicleta' => 'Aluguel de Bicicleta: Explore a cidade de bicicleta.',
    'servico_massage' => 'Massagem e Spa: Serviços de relaxamento no local.',
    'servico_estacionamento' => 'Estacionamento Privado: Estacionamento seguro para veículos.',
];

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
            if (document.getElementById('servico_recepcao').checked) {
                preco += 50;
                document.getElementById('descricao').value = 'Recepção 24h: Atendimento ao cliente disponível 24 horas.';
            }
            if (document.getElementById('servico_concierge').checked) {
                preco += 150;
                document.getElementById('descricao').value = 'Serviço de Concierge: Reservas de restaurantes, passeios e mais.';
            }
            if (document.getElementById('servico_deposito_bagagem').checked) {
                preco += 30;
                document.getElementById('descricao').value = 'Depósito de Bagagens: Armazenamento seguro para sua bagagem.';
            }
            if (document.getElementById('servico_lavanderia').checked) {
                preco += 80;
                document.getElementById('descricao').value = 'Serviço de Lavanderia: Roupas lavadas e engomadas.';
            }
            if (document.getElementById('servico_caixa_segurança').checked) {
                preco += 20;
                document.getElementById('descricao').value = 'Caixa de Segurança: Proteja seus itens pessoais.';
            }
            if (document.getElementById('servico_wifi').checked) {
                preco += 10;
                document.getElementById('descricao').value = 'Wi-Fi Gratuito: Acesso à internet sem custo.';
            }
            if (document.getElementById('servico_transfer').checked) {
                preco += 60;
                document.getElementById('descricao').value = 'Transfer para o aeroporto: Transporte para o aeroporto ou estação.';
            }
            if (document.getElementById('servico_quarto').checked) {
                preco += 50;
                document.getElementById('descricao').value = 'Serviço de Quarto: Refeições entregues diretamente no quarto.';
            }
            if (document.getElementById('servico_bicicleta').checked) {
                preco += 40;
                document.getElementById('descricao').value = 'Aluguel de Bicicleta: Explore a cidade de bicicleta.';
            }
            if (document.getElementById('servico_massage').checked) {
                preco += 100;
                document.getElementById('descricao').value = 'Massagem e Spa: Serviços de relaxamento no local.';
            }
            if (document.getElementById('servico_estacionamento').checked) {
                preco += 30;
                document.getElementById('descricao').value = 'Estacionamento Privado: Estacionamento seguro para veículos.';
            }

            document.getElementById('preco').value = preco.toFixed(2);
        }
    </script>
</head>
<body>
    <h2>Adicionar Novo Serviço</h2>
    <form method="post">
        Nome do Serviço: <input type="text" name="nome_servico" required><br><br>
        Descrição: <textarea id="descricao" name="descricao" readonly></textarea><br><br>

        <h3>Escolha os Serviços:</h3>
        <label><input type="checkbox" id="servico_recepcao" onclick="atualizarPreco()"> Recepção 24h (50€)</label><br>
        <label><input type="checkbox" id="servico_concierge" onclick="atualizarPreco()"> Serviço de Concierge (150€)</label><br>
        <label><input type="checkbox" id="servico_deposito_bagagem" onclick="atualizarPreco()"> Depósito de Bagagens (30€)</label><br>
        <label><input type="checkbox" id="servico_lavanderia" onclick="atualizarPreco()"> Serviço de Lavanderia (80€)</label><br>
        <label><input type="checkbox" id="servico_caixa_segurança" onclick="atualizarPreco()"> Caixa de Segurança (20€)</label><br>
        <label><input type="checkbox" id="servico_wifi" onclick="atualizarPreco()"> Wi-Fi Gratuito (10€)</label><br>
        <label><input type="checkbox" id="servico_transfer" onclick="atualizarPreco()"> Transfer para o aeroporto (60€)</label><br>
        <label><input type="checkbox" id="servico_quarto" onclick="atualizarPreco()"> Serviço de Quarto (50€)</label><br>
        <label><input type="checkbox" id="servico_bicicleta" onclick="atualizarPreco()"> Aluguel de Bicicleta (40€)</label><br>
        <label><input type="checkbox" id="servico_massage" onclick="atualizarPreco()"> Massagem e Spa (100€)</label><br>
        <label><input type="checkbox" id="servico_estacionamento" onclick="atualizarPreco()"> Estacionamento Privado (30€)</label><br><br>

        Preço (€): <input type="number" step="0.01" id="preco" name="preco" value="0" readonly required><br><br>
        <button type="submit">Salvar</button>
    </form>
    <a href="servicos.php">← Voltar</a>
</body>
</html>
