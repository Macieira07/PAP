<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $descricao = $_POST['descricao'];
    $recorrente = isset($_POST['recorrente']) ? 1 : 0;
    $periodicidade = $_POST['periodicidade'] ?? null;
    $data_fim_recorrencia = $_POST['data_fim_recorrencia'] ?? null;

    // Validação de valor
    if (!is_numeric($valor) || $valor <= 0) {
        echo "O valor deve ser um valor positivo.";
        exit;
    }

    $stmt = $conexao->prepare("INSERT INTO despesas (D_nome, D_valor, D_data, D_descricao, recorrente, periodicidade, data_fim_recorrencia) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssiss", $nome, $valor, $data, $descricao, $recorrente, $periodicidade, $data_fim_recorrencia);
    $stmt->execute();

    header("Location: despesas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">

    <link rel="stylesheet" href="global.css">
    <meta charset="UTF-8">
    <title>Adicionar Despesa</title>
</head>
<body>
    <h2>Adicionar Nova Despesa</h2>
    <form method="post">
        Nome da Despesa: <input type="text" name="nome" required><br><br>
        Valor (€): <input type="number" step="0.01" name="valor" required><br><br>
        Data: <input type="date" name="data" required><br><br>
        Descrição: <textarea name="descricao"></textarea><br><br>
        <label>
            <input type="checkbox" name="recorrente" value="1" id="recorrente_cb" onchange="document.getElementById('recorrencia_opts').style.display=this.checked?'block':'none'"> Despesa recorrente
        </label>
        <div id="recorrencia_opts" style="display:none; margin-top:10px;">
            <label>Periodicidade:
                <select name="periodicidade">
                    <option value="mensal">Mensal</option>
                    <option value="semanal">Semanal</option>
                    <option value="anual">Anual</option>
                </select>
            </label>
            <label>Data de término:
                <input type="date" name="data_fim_recorrencia">
            </label>
        </div>
        <button type="submit">Salvar</button>
    </form>
    <a href="despesas.php">← Voltar</a>
</body>
</html>
