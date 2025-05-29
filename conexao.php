<?php
// Configurações de conexão para o Docker
$host = 'db'; // Nome do serviço MySQL no docker-compose
$usuario = 'root';
$senha = ''; // Senha vazia conforme definido no docker-compose
$banco = 'basedados_pap';
// Cria conexão com tratamento de erros
try {
    $conexao = new mysqli($host, $usuario, $senha, $banco);
    
    if ($conexao->connect_error) {
        throw new Exception("Falha na conexão: " . $conexao->connect_error);
    }
    // Configura charset para evitar problemas com caracteres especiais
    if (!$conexao->set_charset("utf8mb4")) {
        throw new Exception("Erro ao configurar charset: " . $conexao->error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Erro crítico: Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}
?>