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
    'redirect' => 'admin_funcionarios.php'
];

try {
    // Validate and sanitize inputs
    if (!isset($_POST['id']) || !isset($_POST['nome']) || !isset($_POST['email']) || 
        !isset($_POST['cargo']) || !isset($_POST['telefone'])) {
        throw new Exception("All required fields must be provided");
    }

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nome = sanitize_input($_POST['nome']);
    $email = sanitize_input($_POST['email']);
    $cargo = sanitize_input($_POST['cargo']);
    $telefone = sanitize_input($_POST['telefone']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Prepare the query based on whether this is an update or insert
    if ($id > 0) {
        // Update existing employee
        $stmt = $conexao->prepare("UPDATE funcionarios SET F_nome=?, F_email=?, F_cargo=?, F_telefone=? WHERE F_id_funcionario=?");
        $stmt->bind_param("ssssi", $nome, $email, $cargo, $telefone, $id);
        $operation = "update";
    } else {
        // Insert new employee
        $stmt = $conexao->prepare("INSERT INTO funcionarios (F_nome, F_email, F_cargo, F_telefone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $cargo, $telefone);
        $operation = "insert";
    }

    // Execute the query
    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = ($operation === "update") ? "Employee updated successfully" : "Employee added successfully";
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // Log error to file (optional)
    error_log("Error in atualizar_funcionario.php: " . $e->getMessage());
}

// Store message in session for display after redirect
$_SESSION['flash_message'] = [
    'type' => $response['status'],
    'message' => $response['message']
];

// Redirect back to the employees page
header("Location: " . $response['redirect']);
exit();
?>