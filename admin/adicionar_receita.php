<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $metodo = $_POST['metodo'];
    $observacoes = $_POST['observacoes'];
    
    // Inserir receita
    $conexao->query("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_metodo_pagamento, R_observacoes)
                     VALUES ('$descricao', $valor, '$data', '$tipo', '$metodo', '$observacoes')");
    
    // Atualizar saldo
    $conexao->query("UPDATE conta_virtual SET saldo = saldo + $valor WHERE id = 1");
    
    // Registrar movimentação
    $conexao->query("INSERT INTO movimentacoes (tipo, descricao, valor, origem)
                     VALUES ('receita', '$descricao', $valor, 'receita')");
    
    header('Location: despesas.php?msg=Receita adicionada com sucesso');
    exit;
}

// Formulário para adicionar receita
?>
<!DOCTYPE html>
<html>
<head>
    <title>Adicionar Receita</title>
</head>
<body>
    <h1>Adicionar Nova Receita</h1>
    <form method="POST">
        <div>
            <label>Descrição:</label>
            <input type="text" name="descricao" required>
        </div>
        <div>
            <label>Valor (€):</label>
            <input type="number" step="0.01" name="valor" required>
        </div>
        <div>
            <label>Data:</label>
            <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div>
            <label>Tipo:</label>
            <select name="tipo" required>
                <option value="Reserva">Reserva</option>
                <option value="Serviço">Serviço</option>
                <option value="Outro">Outro</option>
            </select>
        </div>
        <div>
            <label>Método de Pagamento:</label>
            <select name="metodo" required>
                <option value="Cartão">Cartão</option>
                <option value="Transferência">Transferência</option>
                <option value="MB WAY">MB WAY</option>
                <option value="Dinheiro">Dinheiro</option>
            </select>
        </div>
        <div>
            <label>Observações:</label>
            <textarea name="observacoes"></textarea>
        </div>
        <button type="submit">Adicionar Receita</button>
    </form>
</body>
</html>