<?php
require '../conexao.php';

// Filtro por mês e ano
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// Consulta de faturamento
$query = "
    SELECT 
        SUM(R_preco_total) AS total_faturado,
        MONTH(R_data_checkin) AS mes,
        YEAR(R_data_checkin) AS ano
    FROM reservas 
    WHERE YEAR(R_data_checkin) = ? AND MONTH(R_data_checkin) = ?
";
$stmt = $conexao->prepare($query);
$stmt->bind_param("ii", $ano, $mes);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$total_faturado = $result['total_faturado'] ? $result['total_faturado'] : 0;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="faturamento_' . $mes . '_' . $ano . '.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['Mês', 'Ano', 'Total Faturado']);
fputcsv($output, [$mes, $ano, $total_faturado]);

fclose($output);
exit;
?>
