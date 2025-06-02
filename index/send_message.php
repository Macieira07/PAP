<?php
session_start();

// Conexão com a base de dados
require_once '../conexao.php';

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['tipo' => 'erro', 'mensagem' => 'Email inválido.']);
    exit;
}

// Verifica se já está inscrito
$query = "SELECT id FROM newsletter WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['tipo' => 'erro', 'mensagem' => 'Este email já está inscrito.']);
    exit;
}

$stmt->close();

// Insere o email
$query = "INSERT INTO newsletter (email) VALUES (?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);

if ($stmt->execute()) {
    echo json_encode(['tipo' => 'sucesso', 'mensagem' => 'Inscrição feita com sucesso!']);
} else {
    echo json_encode(['tipo' => 'erro', 'mensagem' => 'Erro ao guardar. Tenta novamente.']);
}

$stmt->close();
$conn->close();
exit;
