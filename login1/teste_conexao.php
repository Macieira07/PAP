<?php
// Arquivo para testar a conexão com o banco de dados
session_start();

// Define uma constante para identificar que este é um teste
define('DB_TEST', true);

// Insere cabeçalho adequado para exibir como texto plano no navegador
header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE DE CONEXÃO COM O BANCO DE DADOS ===\n\n";
echo "Data e hora: " . date('Y-m-d H:i:s') . "\n";
echo "PHP version: " . phpversion() . "\n\n";

// Função para imprimir mensagens formatadas
function print_message($type, $message) {
    $type_upper = strtoupper($type);
    echo "[$type_upper] $message\n";
}

// Testar conexão com o banco
print_message('info', 'Iniciando teste de conexão...');

try {
    // Incluir o arquivo de conexão
    print_message('info', 'Carregando arquivo de conexão...');
    require_once '../conexao.php';
    print_message('success', 'Arquivo de conexão carregado');
    
    // Verificar se a conexão está estabelecida
    if (isset($conexao) && $conexao instanceof mysqli) {
        if ($conexao->connect_error) {
            print_message('error', 'Falha na conexão: ' . $conexao->connect_error);
        } else {
            print_message('success', 'Conexão com o MySQL estabelecida');
            print_message('info', 'Servidor: ' . $conexao->host_info);
            print_message('info', 'Versão do servidor: ' . $conexao->server_info);
            print_message('info', 'Charset: ' . $conexao->character_set_name());
            
            // Tenta uma consulta simples
            print_message('info', 'Testando consulta ao banco...');
            $result = $conexao->query("SELECT 1 AS test");
            if ($result) {
                $row = $result->fetch_assoc();
                print_message('success', 'Consulta executada com sucesso: ' . $row['test']);
                $result->free();
            } else {
                print_message('error', 'Falha ao executar consulta: ' . $conexao->error);
            }
            
            // Testar tabelas existentes
            print_message('info', 'Verificando tabelas do banco...');
            $tables = $conexao->query("SHOW TABLES");
            if ($tables) {
                $tableCount = $tables->num_rows;
                print_message('success', "Encontradas $tableCount tabelas");
                while ($table = $tables->fetch_row()) {
                    print_message('info', '- ' . $table[0]);
                }
                $tables->free();
            } else {
                print_message('error', 'Não foi possível listar tabelas: ' . $conexao->error);
            }
        }
    } else {
        print_message('error', 'Variável de conexão não está definida ou não é válida');
    }
} catch (Exception $e) {
    print_message('error', 'Exceção: ' . $e->getMessage());
}

echo "\n=== FIM DO TESTE ===";
?>