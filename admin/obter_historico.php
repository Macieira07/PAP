<?php
include "../conexao.php";

$despesa_id = intval($_GET['despesa_id']);
$historico = $conexao->query("
    SELECT m.* FROM movimentacoes m
    WHERE m.origem = 'despesa' AND m.origem_id IN (
        SELECT id FROM despesas WHERE id = $despesa_id OR id IN (
            SELECT despesa_id FROM despesas_previsoes WHERE despesa_id = $despesa_id AND paga = 1
        )
    )
    ORDER BY m.data DESC
");

if ($historico && $historico->num_rows > 0) {
    echo '<table border="1" cellpadding="8" style="width:100%; border-collapse:collapse; margin-bottom:15px;">';
    echo '<thead><tr><th>Data</th><th>Descrição</th><th>Valor</th></tr></thead>';
    echo '<tbody>';
    
    while ($hist = $historico->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($hist['data']) . '</td>';
        echo '<td>' . htmlspecialchars($hist['descricao']) . '</td>';
        echo '<td>' . number_format($hist['valor'], 2) . ' €</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
} else {
    echo '<p>Nenhum histórico de pagamentos encontrado para esta despesa.</p>';
}
?>