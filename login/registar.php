<?php
// Adicione no início do registar.php
ini_set('error_log', __DIR__ . '/registro_errors.log');
error_log("Início do processamento de registro");~



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
require 'mail_config.php';
require_once '../conexao.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Para debug - remova em produção
    error_log("POST recebido: " . print_r($_POST, true));
    
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token'])) {
        die(json_encode(['error' => 'Token CSRF ausente']));
    }
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die(json_encode(['error' => 'Token CSRF inválido']));
    }

    // Verificar campos obrigatórios
    $requiredFields = ['nome', 'email', 'password', 'telefone', 'documento'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        die(json_encode(['error' => 'Por favor, preencha todos os campos obrigatórios: ' . implode(', ', $missingFields)]));
    }

    if (!isset($_POST['aceitar_termos'])) {
        die(json_encode(['error' => 'Você deve aceitar os termos de uso e política de privacidade.']));
    }

    // Obter e sanitizar dados
    $nome = trim(htmlspecialchars($_POST['nome'], ENT_QUOTES));
    $email = trim($_POST['email']);
    $senha = $_POST['password'];
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $documento = trim(htmlspecialchars($_POST['documento'], ENT_QUOTES));
    $token = bin2hex(random_bytes(32));
    $token_expira = date('Y-m-d H:i:s', strtotime('+1 day'));

    // Validações
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['error' => 'Email inválido.']));
    }

    if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
        die(json_encode(['error' => 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula e um número.']));
    }

    // Verificar se o email já existe
    $sql_check = "SELECT H_id_hospede FROM hospedes WHERE H_email = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        die(json_encode(['error' => 'Este email já está registrado.']));
    }

    // Criptografar senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir no banco de dados
    try {
        $conexao->begin_transaction();
        
        $sql = "INSERT INTO hospedes (
            H_nome, 
            H_email, 
            H_senha, 
            H_telefone, 
            H_documento_ident, 
            H_token_verificacao,
            H_token_expira,
            H_verificado_email, 
            H_aceitou_termos_uso, 
            H_data_criacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Não', 'Sim', NOW())";
        
        $stmt = $conexao->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar a consulta: " . $conexao->error);
        }
        
        $stmt->bind_param(
            "sssssss", 
            $nome, 
            $email, 
            $senha_hash, 
            $telefone, 
            $documento,
            $token,
            $token_expira
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar a consulta: " . $stmt->error);
        }
        
        // Registrar atividade
        $hospede_id = $conexao->insert_id;
        $log_sql = "INSERT INTO logs_acesso (usuario_id, tipo_usuario, acao, data) VALUES (?, ?, ?, NOW())";
        $log_stmt = $conexao->prepare($log_sql);
        if (!$log_stmt) {
            throw new Exception("Erro ao preparar log: " . $conexao->error);
        }
        
        $acao = 'registro';
        $log_stmt->bind_param("iss", $hospede_id, 'hospede', $acao);
        if (!$log_stmt->execute()) {
            throw new Exception("Erro ao registrar log: " . $log_stmt->error);
        }
        
        // Corrigir o caminho do verify.php
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/PAP/login/verify.php?token=$token";
        $subject = "Verifique seu email - Quinta Flores";
        $body = "<h2>Bem-vindo à Quinta Flores!</h2>
                <p>Obrigado por se registrar. Por favor, clique no link abaixo para verificar seu email:</p>
                <p><a href='$verification_link'>Verificar Email</a></p>
                <p>Se você não se registrou, ignore este email.</p>";
        
        $conexao->commit();
        
        if (enviarEmail($email, $subject, $body)) {
            echo json_encode(['success' => 'Registro bem-sucedido! Verifique seu email para confirmar a conta.']);
        } else {
            // Se o email falhar, ainda informamos sucesso no registro
            echo json_encode(['success' => 'Registro bem-sucedido! Problema ao enviar email de verificação.']);
        }
        
    } catch (Exception $e) {
        $conexao->rollback();
        error_log("Erro no registro: " . $e->getMessage());
        die(json_encode(['error' => 'Ocorreu um erro durante o registro: ' . $e->getMessage()]));
    }
} else {
    header("Location: login.php");
    exit();
}
?>