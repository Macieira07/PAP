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

// Function to validate date format (YYYY-MM-DD)
function validate_date($date) {
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred',
    'redirect' => 'admin_reservas.php'
];

try {
    // Validate and sanitize inputs
    if (!isset($_POST['id']) || !isset($_POST['id_hospede']) || !isset($_POST['id_casa']) || 
        !isset($_POST['checkin']) || !isset($_POST['checkout']) || !isset($_POST['estado'])) {
        throw new Exception("All required fields must be provided");
    }

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $hospede = filter_var($_POST['id_hospede'], FILTER_VALIDATE_INT);
    $casa = filter_var($_POST['id_casa'], FILTER_VALIDATE_INT);
    $checkin = sanitize_input($_POST['checkin']);
    $checkout = sanitize_input($_POST['checkout']);
    $estado = sanitize_input($_POST['estado']);
    
    // Validate IDs
    if ($hospede === false || $casa === false) {
        throw new Exception("Invalid guest or house ID");
    }
    
    // Validate dates
    if (!validate_date($checkin) || !validate_date($checkout)) {
        throw new Exception("Invalid date format. Please use YYYY-MM-DD");
    }
    
    // Validate checkout is after checkin
    $checkin_date = new DateTime($checkin);
    $checkout_date = new DateTime($checkout);
    if ($checkout_date <= $checkin_date) {
        throw new Exception("Checkout date must be after checkin date");
    }
    
    // Validate reservation status
    $valid_states = ['confirmada', 'pendente', 'cancelada'];
    if (!in_array(strtolower($estado), $valid_states)) {
        throw new Exception("Invalid reservation status");
    }

    // Check for date conflicts with existing reservations (if needed)
    // This would be a more complex query to ensure no overlapping reservations for the same house

    // Prepare the query based on whether this is an update or insert
    if ($id > 0) {
        // Update existing reservation
        $stmt = $conexao->prepare("UPDATE reservas SET R_id_hospede=?, R_id_casa=?, R_data_checkin=?, R_data_checkout=?, R_estado=? WHERE R_id_reserva=?");
        $stmt->bind_param("iisssi", $hospede, $casa, $checkin, $checkout, $estado, $id);
        $operation = "update";
    } else {
        // Insert new reservation
        $stmt = $conexao->prepare("INSERT INTO reservas (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, R_estado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $hospede, $casa, $checkin, $checkout, $estado);
        $operation = "insert";
    }

    // Execute the query
    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = ($operation === "update") ? "Reservation updated successfully" : "Reservation added successfully";
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // Log error to file (optional)
    error_log("Error in atualizar_reserva.php: " . $e->getMessage());
}

// Store message in session for display after redirect
$_SESSION['flash_message'] = [
    'type' => $response['status'],
    'message' => $response['message']
];

// Redirect back to the reservations page
header("Location: " . $response['redirect']);
exit();
?>