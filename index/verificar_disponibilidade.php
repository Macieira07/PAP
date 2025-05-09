<?php
// Ativar a exibição de erros para depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir a conexão ao banco de dados
include '../conexao.php';

// Recebe os dados do frontend (datas de check-in e check-out)
$data = json_decode(file_get_contents('php://input'), true);

// Verifica se os dados foram recebidos corretamente
if (!$data) {
    die('Erro ao ler os dados JSON: ' . json_last_error_msg());
}

// Extrai as variáveis de check-in e check-out
$checkIn = $data['checkIn'];
$checkOut = $data['checkOut'];

// Exibe as variáveis recebidas para garantir que os dados estão certos
echo "Check-In: $checkIn, Check-Out: $checkOut<br>";

// Prepara a consulta SQL para verificar a disponibilidade
$sql = "
    SELECT COUNT(*) AS reservas
    FROM reservas
    WHERE (R_data_checkin < ? AND R_data_checkout > ?)
";

// Prepara a consulta
$stmt = $conexao->prepare($sql);

// Verifica se a preparação da consulta foi bem-sucedida
if ($stmt === false) {
    die("Erro na consulta SQL: " . $conexao->error);
}

// Vincula os parâmetros de data (o formato deve ser YYYY-MM-DD)
$stmt->bind_param('ss', $checkOut, $checkIn);

// Executa a consulta
$stmt->execute();

// Obtém o resultado
$stmt->bind_result($reservas);
$stmt->fetch();

// Exibe a quantidade de reservas encontradas
echo "Reservas encontradas: $reservas<br>";

// Verifica se há reservas durante o período
if ($reservas > 0) {
    echo json_encode(['available' => false]);
} else {
    echo json_encode(['available' => true]);
}

// Fecha a declaração e a conexão
$stmt->close();
$conexao->close();
?>
