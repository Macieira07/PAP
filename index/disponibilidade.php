<?php
require_once '../conexao.php';  
header('Content-Type: application/json'); // importante

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $checkin = $data['checkin'];
    $checkout = $data['checkout'];
    $num_hospedes = $data['guests'];

    if (empty($checkin) || empty($checkout)) {
        throw new Exception("Datas de check-in e check-out são obrigatórias.");
    }

    if (strtotime($checkout) <= strtotime($checkin)) {
        throw new Exception("A data de check-out deve ser posterior à data de check-in.");
    }

    $query = "SELECT C_id_casa, C_nome, C_descricao, C_capacidade, C_preco_noite, C_caracteristicas 
              FROM casas 
              WHERE C_id_casa = 1 
              AND C_estado = 'disponível' 
              AND C_id_casa NOT IN (
                  SELECT R_id_casa 
                  FROM reservas 
                  WHERE (
                      (R_data_checkin <= ? AND R_data_checkout >= ?) OR
                      (R_data_checkin >= ? AND R_data_checkin < ?) OR
                      (R_data_checkout > ? AND R_data_checkout <= ?)
                  )
                  AND R_estado IN ('confirmada', 'pendente')
              )";

    $stmt = $conexao->prepare($query);
    $stmt->bind_param("ssssss", $checkout, $checkin, $checkin, $checkout, $checkin, $checkout);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $casa = $result->fetch_assoc();
        $num_noites = (new DateTime($checkin))->diff(new DateTime($checkout))->days;
        $preco_total = $num_noites * $casa['C_preco_noite'];

        echo json_encode([
            'disponivel' => true,
            'casa' => [
                'id' => $casa['C_id_casa'],
                'nome' => $casa['C_nome'],
                'descricao' => $casa['C_descricao'],
                'capacidade' => $casa['C_capacidade'],
                'preco_noite' => $casa['C_preco_noite'],
                'caracteristicas' => $casa['C_caracteristicas'],
                'num_noites' => $num_noites,
                'preco_total' => $preco_total
            ]
        ]);
    } else {
        echo json_encode([
            'disponivel' => false,
            'mensagem' => 'A Quinta Flores não está disponível para as datas selecionadas.'
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
      
}
