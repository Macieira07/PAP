<?php
session_start();
require_once('../conexao.php');

// Constants for messages
define('SUCCESS_UPDATE', 'Guest updated successfully');
define('SUCCESS_INSERT', 'Guest added successfully');
define('ERROR_UNKNOWN', 'An unknown error occurred');
define('ERROR_FIELDS', 'All required fields must be provided');
define('ERROR_EMAIL', 'Invalid email format');

// Function to sanitize input data
function sanitize_input($data) {
    global $conexao;
    return $conexao->real_escape_string(htmlspecialchars(trim($data)));
}

// Function to handle database operations
function handle_guest_operation($id, $nome, $apelido, $email, $telefone) {
    global $conexao;

    if ($id > 0) {
        $stmt = $conexao->prepare("UPDATE hospedes SET H_nome=?, H_apelido=?, H_email=?, H_telefone=? WHERE H_id_hospede=?");
        $stmt->bind_param("ssssi", $nome, $apelido, $email, $telefone, $id);
        $message = SUCCESS_UPDATE;
    } else {
        $stmt = $conexao->prepare("INSERT INTO hospedes (H_nome, H_apelido, H_email, H_telefone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $apelido, $email, $telefone);
        $message = SUCCESS_INSERT;
    }

    if ($stmt->execute()) {
        $stmt->close();
        return ['status' => 'success', 'message' => $message];
    } else {
        $stmt->close();
        throw new Exception("Database error: " . $stmt->error);
    }
}

$response = ['status' => 'error', 'message' => ERROR_UNKNOWN, 'redirect' => 'admin_hospedes.php'];

try {
    if (!isset($_POST['id'], $_POST['nome'], $_POST['email'], $_POST['telefone'])) {
        throw new Exception(ERROR_FIELDS);
    }

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nome = sanitize_input($_POST['nome']);
    $apelido = sanitize_input($_POST['apelido'] ?? '');
    $email = sanitize_input($_POST['email']);
    $telefone = sanitize_input($_POST['telefone']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception(ERROR_EMAIL);
    }

    $response = handle_guest_operation($id, $nome, $apelido, $email, $telefone);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error in atualizar_hospede.php: " . $e->getMessage());
}

$_SESSION['flash_message'] = [
    'type' => $response['status'],
    'message' => $response['message']
];

header("Location: " . $response['redirect']);
exit();
?>