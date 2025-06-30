<?php
require '../conexao.php';

$id_casa = isset($_GET['id_casa']) ? intval($_GET['id_casa']) : 0;
$datas = [];

if ($id_casa) {
    $sql = "SELECT R_data_checkin, R_data_checkout FROM reservas WHERE R_id_casa = ? AND R_estado != 'cancelada'";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_casa);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row['R_data_checkin']);
        $end = new DateTime($row['R_data_checkout']);
        while ($start <= $end) {
            $datas[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }
}
header('Content-Type: application/json');
echo json_encode($datas);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="global.css">
</head>
<body>
    <!-- Restante do conteúdo -->
</body>
</html>