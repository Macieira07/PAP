<?php
include "../conexao.php";

$despesa_id = intval($_GET['despesa_id']);
$previsoes = $conexao->query("
    SELECT * FROM despesas_previsoes 
    WHERE despesa_id = $despesa_id 
    ORDER BY data_prevista ASC
");

if ($previsoes && $previsoes->num_rows > 0) {
    echo '<table border="1" cellpadding="8" style="width:100%; border-collapse:collapse; margin-bottom:15px;">';
    echo '<thead><tr><th>Data Prevista</th><th>Valor</th><th>Estado</th></tr></thead>';
    echo '<tbody>';
    
    while ($prev = $previsoes->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($prev['data_prevista']) . '</td>';
        echo '<td>' . number_format($prev['valor'], 2) . ' €</td>';
        echo '<td>' . ($prev['paga'] ? '<span style="color:green">Paga</span>' : '<span style="color:red">Por pagar</span>') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
} else {
    echo '<p>Nenhuma previsão futura encontrada para esta despesa.</p>';
}
?>