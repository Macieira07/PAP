<?php
// Configuração de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/registro_errors.log');
error_log("Início do processamento de registro");

// Configurações de sessão
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Inclui arquivos necessários
require 'email_functions.php';
require_once '../conexao.php';

// Verifica conexão com o banco
if ($conexao->connect_error) {
    die(json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $conexao->connect_error]));
}

// Gera token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST recebido: " . print_r($_POST, true));
    
    // Validação CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die(json_encode(['error' => 'Token CSRF inválido']));
    }

    // Campos obrigatórios
    $requiredFields = ['nome', 'email', 'password', 'telefone', 'documento'];
    $missingFields = array_diff($requiredFields, array_keys(array_filter($_POST)));
    
    if (!empty($missingFields)) {
        die(json_encode(['error' => 'Por favor, preencha todos os campos obrigatórios: ' . implode(', ', $missingFields)]));
    }

    if (!isset($_POST['aceitar_termos'])) {
        die(json_encode(['error' => 'Você deve aceitar os termos de uso e política de privacidade.']));
    }

    // Sanitização dos dados
    $nome = trim(htmlspecialchars($_POST['nome'], ENT_QUOTES));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
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

    // Verifica se email já existe
    $sql_check = "SELECT H_id_hospede FROM hospedes WHERE H_email = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows > 0) {
        die(json_encode(['error' => 'Este email já está registrado.']));
    }

    // Criptografa senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        $conexao->begin_transaction();
        
        // Query atualizada para a estrutura da tabela
        $sql = "INSERT INTO hospedes (
            H_nome, H_email, H_senha, H_telefone, 
            H_documento_ident, H_token_verificacao, H_token_expira,
            H_verificado_email, H_aceitou_termos_uso
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Não', 'Sim')";
        
        $stmt = $conexao->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar a consulta: " . $conexao->error);
        }
        
        $stmt->bind_param(
            "sssssss", 
            $nome, $email, $senha_hash, $telefone, 
            $documento, $token, $token_expira
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar a consulta: " . $stmt->error);
        }
        
        // Link de verificação
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/PAP/login/verify.php?token=$token";
        
        // Email de verificação
        $subject = "Verifique seu email - Quinta Flores";
        $body = "<h2>Bem-vindo à Quinta Flores!</h2>
                <p>Obrigado por se registrar. Por favor, clique no link abaixo para verificar seu email:</p>
                <p><a href='$verification_link'>Verificar Email</a></p>
                <p>Se você não se registrou, ignore este email.</p>";
        
        $conexao->commit();
        
        if (enviarEmail($email, $subject, $body)) {
            echo json_encode(['success' => 'Registro bem-sucedido! Verifique seu email para confirmar a conta.']);
        } else {
            echo json_encode(['warning' => 'Registro concluído, mas houve um problema ao enviar o email de verificação.']);
        }
        
    } catch (Exception $e) {
        $conexao->rollback();
        error_log("Erro no registro: " . $e->getMessage());
        die(json_encode(['error' => 'Ocorreu um erro durante o registro. Por favor, tente novamente.']));
    }
} else {
    header("Location: login.php");
    exit();
}
?>