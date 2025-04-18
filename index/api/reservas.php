<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require_once __DIR__ . '/../basedados_pap/conexao.php'; // Caminho absoluto mais seguro

$casaId = $_GET['casa_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$casaId || !$date) {
    echo json_encode(['isBooked' => false]);
    exit;
}

try {
    $sql = "SELECT COUNT(*) as total 
            FROM reservas 
            WHERE R_id_casa = :casa_id 
            AND :date BETWEEN R_data_checkin AND DATE_SUB(R_data_checkout, INTERVAL 1 DAY)
            AND R_estado = 'confirmada'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':casa_id', $casaId, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['isBooked' => $result['total'] > 0]);

} catch (PDOException $e) {
    error_log("Erro no reservas.php: " . $e->getMessage());
    echo json_encode(['isBooked' => false, 'error' => 'Erro no servidor']);
}
?>