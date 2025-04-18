<?php
session_start();
header('Content-Type: application/json'); // Responder sempre em JSON

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['error' => 'Token CSRF inválido']);
        exit;
    }

    if (empty($_POST['email']) || empty($_POST['senha'])) {
        echo json_encode(['error' => 'Por favor, preencha todos os campos.']);
        exit;
    }

    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Email inválido.']);
        exit;
    }

    function verificarUsuario($conexao, $email, $senha, $tabela, $emailCol, $senhaCol, $idCol, $nomeCol) {
        $sql_code = "SELECT * FROM $tabela WHERE $emailCol = ?";
        $stmt = $conexao->prepare($sql_code);
        if (!$stmt) {
            error_log("Erro ao preparar consulta: " . $conexao->error);
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $sql_query = $stmt->get_result();

        if ($sql_query->num_rows == 1) {
            $usuario = $sql_query->fetch_assoc();
            if (password_verify($senha, $usuario[$senhaCol])) {
                return $usuario;
            }
        }
        return false;
    }

    // Funcionário
    $usuario = verificarUsuario($conexao, $email, $senha, 'funcionarios', 'F_email', 'F_senha', 'F_id_funcionario', 'F_nome');
    if ($usuario) {
        if (isset($usuario['F_ativo']) && $usuario['F_ativo'] != 'Sim') {
            echo json_encode(['error' => 'Sua conta está desativada.']);
            exit;
        }

        $_SESSION['id'] = $usuario['F_id_funcionario'];
        $_SESSION['nome'] = $usuario['F_nome'];
        $_SESSION['email'] = $usuario['F_email'];
        $_SESSION['tipo'] = 'funcionario';
        $_SESSION['F_cargo'] = $usuario['F_cargo'];
        $_SESSION['login_attempts'] = 0;

        // Log de login
        $log_sql = "INSERT INTO logs_acesso (usuario_id, tipo_usuario, acao, data) VALUES (?, 'funcionario', 'login', NOW())";
        $log_stmt = $conexao->prepare($log_sql);
        $log_stmt->bind_param("i", $usuario['F_id_funcionario']);
        $log_stmt->execute();

        echo json_encode(['redirect' => '../admin/admin_index.php']);
        exit;
    }

    // Hóspede
    $usuario = verificarUsuario($conexao, $email, $senha, 'hospedes', 'H_email', 'H_senha', 'H_id_hospede', 'H_nome');
    if ($usuario && $usuario['H_verificado_email'] == 'Sim') {
        $_SESSION['id'] = $usuario['H_id_hospede'];
        $_SESSION['nome'] = $usuario['H_nome'];
        $_SESSION['email'] = $usuario['H_email'];
        $_SESSION['tipo'] = 'hospede';
        $_SESSION['login_attempts'] = 0;

        $log_sql = "INSERT INTO logs_acesso (usuario_id, tipo_usuario, acao, data) VALUES (?, 'hospede', 'login', NOW())";
        $log_stmt = $conexao->prepare($log_sql);
        $log_stmt->bind_param("i", $usuario['H_id_hospede']);
        $log_stmt->execute();

        echo json_encode(['redirect' => '../pagamento/pagina1.php']);
        exit;
    }

    // Login falhou
    $_SESSION['login_attempts']++;
    $_SESSION['last_login_attempt'] = time();

    $tipo = strpos($email, '@hotel.com') !== false ? 'funcionario' : 'hospede';
    $acao = 'tentativa_login';
    $log_sql = "INSERT INTO logs_acesso (email, tipo_usuario, acao, data, status) VALUES (?, ?, ?, NOW(), 'falha')";
    $log_stmt = $conexao->prepare($log_sql);
    $log_stmt->bind_param("sss", $email, $tipo, $acao);
    $log_stmt->execute();

    sleep($_SESSION['login_attempts']); // antiflood

    echo json_encode(['error' => 'Credenciais inválidas.']);
    exit;
}

// Se for GET, ignora o JSON e mostra o formulário normal (ex: login_page.php)
header('Content-Type: text/html; charset=utf-8');
$csrf_token = $_SESSION['csrf_token'];
include('login_page.php');
