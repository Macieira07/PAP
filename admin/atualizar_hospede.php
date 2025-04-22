<?php
// Start session if not already started
session_start();

// Include database connection
require_once('../conexao.php');

// Function to sanitize input data
function sanitize_input($data) {
    global $conexao;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conexao->real_escape_string($data);
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred',
    'redirect' => 'admin_hospedes.php'
];

try {
    // Validate and sanitize inputs
    if (!isset($_POST['id']) || !isset($_POST['nome']) || !isset($_POST['email']) || !isset($_POST['telefone'])) {
        throw new Exception("All required fields must be provided");
    }

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nome = sanitize_input($_POST['nome']);
    $apelido = sanitize_input($_POST['apelido'] ?? ''); // Optional field
    $email = sanitize_input($_POST['email']);
    $telefone = sanitize_input($_POST['telefone']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Prepare the query based on whether this is an update or insert
    if ($id > 0) {
        // Update existing guest
        $stmt = $conexao->prepare("UPDATE hospedes SET H_nome=?, H_apelido=?, H_email=?, H_telefone=? WHERE H_id_hospede=?");
        $stmt->bind_param("ssssi", $nome, $apelido, $email, $telefone, $id);
        $operation = "update";
    } else {
        // Insert new guest
        $stmt = $conexao->prepare("INSERT INTO hospedes (H_nome, H_apelido, H_email, H_telefone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $apelido, $email, $telefone);
        $operation = "insert";
    }

    // Execute the query
    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = ($operation === "update") ? "Guest updated successfully" : "Guest added successfully";
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // Log error to file (optional)
    error_log("Error in atualizar_hospede.php: " . $e->getMessage());
}

// Store message in session for display after redirect
$_SESSION['flash_message'] = [
    'type' => $response['status'],
    'message' => $response['message']
];

// Redirect back to the guests page
header("Location: " . $response['redirect']);
exit();
?>