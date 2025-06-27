<?php
require '../conexao.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode(['hospedes'=>[], 'reservas'=>[], 'casas'=>[], 'funcionarios'=>[]]);
    exit;
}
$q_sql = '%' . $conexao->real_escape_string($q) . '%';

// Hóspedes
$hospedes = [];
$res = $conexao->query("SELECT H_id_hospede as id, H_nome as nome, H_email as email FROM hospedes WHERE H_nome LIKE '$q_sql' OR H_email LIKE '$q_sql' LIMIT 5");
while ($row = $res->fetch_assoc()) $hospedes[] = $row;

// Reservas (join para pegar nome do hóspede e casa)
$reservas = [];
$res = $conexao->query("SELECT r.R_id_reserva as id, h.H_nome as nome_hospede, c.C_nome as nome_casa, r.R_data_checkin as data_entrada, r.R_data_checkout as data_saida FROM reservas r JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede JOIN casas c ON r.R_id_casa = c.C_id_casa WHERE r.R_id_reserva LIKE '$q_sql' OR h.H_nome LIKE '$q_sql' OR c.C_nome LIKE '$q_sql' LIMIT 5");
while ($row = $res->fetch_assoc()) $reservas[] = $row;

// Casas
$casas = [];
$res = $conexao->query("SELECT C_id_casa as id, C_nome as nome, C_estado, C_capacidade FROM casas WHERE C_nome LIKE '$q_sql' OR C_id_casa LIKE '$q_sql' OR C_estado LIKE '$q_sql' OR C_capacidade LIKE '$q_sql' LIMIT 5");
while ($row = $res->fetch_assoc()) $casas[] = $row;

// Funcionários
$funcionarios = [];
$res = $conexao->query("SELECT F_id_funcionario as id, F_nome as nome, F_email as email FROM funcionarios WHERE F_nome LIKE '$q_sql' OR F_email LIKE '$q_sql' LIMIT 5");
while ($row = $res->fetch_assoc()) $funcionarios[] = $row;

// Resultado
echo json_encode([
    'hospedes' => $hospedes,
    'reservas' => $reservas,
    'casas' => $casas,
    'funcionarios' => $funcionarios
]); 