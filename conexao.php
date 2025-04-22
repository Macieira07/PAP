<?php
// Define função para debug se ainda não existir
if (!function_exists('debug_log')) {
    function debug_log($message, $data = null) {
        $log_message = date('[Y-m-d H:i:s]') . " - " . $message;
        if ($data !== null) {
            $log_message .= ": " . (is_array($data) || is_object($data) ? json_encode($data) : $data);
        }
        error_log($log_message);
    }
}

// Configurações do banco de dados
// Note: Em ambiente Docker, o host 'db' é o nome do serviço no docker-compose
$host = 'db';       // Nome do serviço no docker-compose
$usuario = 'root';  // Usuário padrão do MySQL
$senha = '';        // Senha vazia conforme configurado
$banco = 'basedados_pap';

debug_log("Tentando conectar ao banco de dados", ['host' => $host, 'banco' => $banco, 'usuario' => $usuario]);

// Cria conexão com tratamento de erros
try {
    $conexao = new mysqli($host, $usuario, $senha, $banco);
    
    if ($conexao->connect_error) {
        debug_log("Erro de conexão MySQL", $conexao->connect_error);
        throw new Exception("Falha na conexão: " . $conexao->connect_error);
    }
    
    // Configura charset para evitar problemas com caracteres especiais
    if (!$conexao->set_charset("utf8mb4")) {
        debug_log("Erro ao configurar charset", $conexao->error);
        throw new Exception("Erro ao configurar charset: " . $conexao->error);
    }
    
    debug_log("Conexão com o banco de dados estabelecida com sucesso");
    
} catch (Exception $e) {
    debug_log("Exceção capturada na conexão", $e->getMessage());
    
    // Em ambiente de produção, nunca exiba detalhes técnicos ao usuário
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && 
        stripos($_SERVER['SCRIPT_NAME'], 'login.php') !== false) {
        // Se estiver em uma requisição AJAX de login, retorna JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro na conexão com o servidor. Por favor, tente novamente mais tarde.']);
        exit;
    } else {
        // Para outras páginas, exibe mensagem amigável
        die("Erro crítico: Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
    }
}
?>