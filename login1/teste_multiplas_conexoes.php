<?php
// Arquivo para testar diferentes configurações de conexão
session_start();

// Define uma constante para identificar que este é um teste
define('DB_TEST', true);

// Insere cabeçalho adequado para exibir como texto plano no navegador
header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE DE MÚLTIPLAS CONFIGURAÇÕES DE CONEXÃO ===\n\n";
echo "Data e hora: " . date('Y-m-d H:i:s') . "\n";
echo "PHP version: " . phpversion() . "\n\n";

// Função para imprimir mensagens formatadas
function print_message($type, $message) {
    $type_upper = strtoupper($type);
    echo "[$type_upper] $message\n";
}

// Função para testar uma conexão específica
function test_connection($host, $user, $password, $database) {
    echo "\n\n--- TESTANDO CONEXÃO: Host = $host ---\n";
    
    try {
        $conn = new mysqli($host, $user, $password, $database);
        
        if ($conn->connect_error) {
            print_message('error', 'Falha na conexão: ' . $conn->connect_error);
            return false;
        } else {
            print_message('success', 'Conexão estabelecida com sucesso!');
            print_message('info', 'Servidor: ' . $conn->host_info);
            print_message('info', 'Versão do servidor: ' . $conn->server_info);
            
            // Testar uma consulta simples
            $result = $conn->query("SELECT 1 AS test");
            if ($result) {
                $row = $result->fetch_assoc();
                print_message('success', 'Consulta executada com sucesso: ' . $row['test']);
                $result->free();
            } else {
                print_message('error', 'Falha ao executar consulta: ' . $conn->error);
            }
            
            $conn->close();
            return true;
        }
    } catch (Exception $e) {
        print_message('error', 'Exceção: ' . $e->getMessage());
        return false;
    }
}

// Configurações a serem testadas
$configs = [
    ['host' => 'db', 'desc' => 'Host padrão do Docker'],
    ['host' => 'localhost', 'desc' => 'Localhost padrão'],
    ['host' => '127.0.0.1', 'desc' => 'IP localhost'],
    ['host' => 'mysql', 'desc' => 'Nome comum para serviço MySQL em Docker']
];

// Credenciais padrão
$user = 'root';
$password = '';  // Senha vazia por padrão, ajuste se necessário
$database = 'basedados_pap';

print_message('info', 'Iniciando testes de conexão com múltiplas configurações...');
print_message('info', 'Usuário: ' . $user);
print_message('info', 'Banco de dados: ' . $database);

$successful_hosts = [];

// Testar cada configuração
foreach ($configs as $config) {
    $host = $config['host'];
    $desc = $config['desc'];
    
    print_message('info', "Testando $host ($desc)");
    
    if (test_connection($host, $user, $password, $database)) {
        $successful_hosts[] = $host;
    }
}

echo "\n\n=== RESUMO DOS TESTES ===\n";
if (count($successful_hosts) > 0) {
    print_message('success', 'Hosts que funcionaram:');
    foreach ($successful_hosts as $host) {
        echo " - $host\n";
    }
    echo "\nVocê deve atualizar o arquivo conexao.php para usar um destes hosts.\n";
} else {
    print_message('error', 'Nenhuma configuração testada funcionou. Verifique suas credenciais e se o banco de dados está ativo.');
}

echo "\n=== FIM DO TESTE ===";
?>