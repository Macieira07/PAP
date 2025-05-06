<?php
require '../conexao.php';

$inicio = $_POST['data_inicio'] ?? '2025-01-01';  // Valor padrão para o início
$fim = $_POST['data_fim'] ?? '2025-12-31';        // Valor padrão para o fim

// Consulta para somar o preço total das reservas confirmadas entre as datas informadas
$stmt = $conexao->prepare("SELECT SUM(R_preco_total) AS receita_total 
                           FROM reservas 
                           WHERE R_estado = 'confirmada' 
                           AND R_data_checkin BETWEEN ? AND ?");
$stmt->bind_param("ss", $inicio, $fim);
$stmt->execute();
$resultado = $stmt->get_result();
$dados = $resultado->fetch_assoc();
?>

<h2>Relatório de Receitas</h2>
<form method="post" action="relatorio_receitas.php">
    <label for="data_inicio">Data Início: </label>
    <input type="date" name="data_inicio" value="<?= $inicio ?>"><br>
    <label for="data_fim">Data Fim: </label>
    <input type="date" name="data_fim" value="<?= $fim ?>"><br>
    <button type="submit">Gerar Relatório</button>
</form>

<p>Receita total de <?= $inicio ?> a <?= $fim ?>: €<?= number_format($dados['receita_total'], 2, ',', '.') ?></p>
