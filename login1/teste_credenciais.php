<?php
session_start();

// Verificação de segurança - em produção, remover ou proteger adequadamente esta página
if (!isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die("Acesso restrito a localhost apenas.");
}

// Inclui arquivo de conexão
require_once '../conexao.php';

// Função para exibir dados de forma segura
function exibirValor($valor) {
    return htmlspecialchars($valor ?? 'N/A', ENT_QUOTES, 'UTF-8');
}

// Função para exibir hash seguro (apenas primeiros e últimos caracteres)
function exibirHashSeguro($hash) {
    if (empty($hash)) return 'N/A';
    $len = strlen($hash);
    if ($len <= 10) return '***';
    return substr($hash, 0, 5) . '...[' . ($len-10) . ' caracteres]...' . substr($hash, -5);
}

// Verificar um email específico
$email = '';
$tabela = '';
$mensagem = '';
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $tabela = $_POST['tabela'] ?? 'hospedes';
    $senha_teste = $_POST['senha'] ?? '';
    
    if (empty($email)) {
        $mensagem = "Por favor, informe um email para pesquisar.";
    } else {
        // Determinar colunas com base na tabela
        $email_col = ($tabela === 'funcionarios') ? 'F_email' : 'H_email';
        $senha_col = ($tabela === 'funcionarios') ? 'F_senha' : 'H_senha';
        $id_col = ($tabela === 'funcionarios') ? 'F_id_funcionario' : 'H_id_hospede';
        $nome_col = ($tabela === 'funcionarios') ? 'F_nome' : 'H_nome';
        
        // Buscar usuário
        $sql = "SELECT * FROM $tabela WHERE $email_col = ?";
        $stmt = $conexao->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows === 0) {
                $mensagem = "Usuário com email '$email' não encontrado na tabela $tabela.";
            } else {
                $mensagem = "Usuário encontrado!";
                
                // Verificar senha
                if (!empty($senha_teste)) {
                    $usuario = $resultado->fetch_assoc();
                    $hash_armazenado = $usuario[$senha_col] ?? '';
                    
                    if (password_verify($senha_teste, $hash_armazenado)) {
                        $mensagem .= " Senha verificada com sucesso!";
                    } else {
                        $mensagem .= " Senha incorreta!";
                    }
                    
                    // Reposicionar o ponteiro do resultado para o início
                    $resultado->data_seek(0);
                }
            }
            
            $stmt->close();
        } else {
            $mensagem = "Erro na preparação da consulta: " . $conexao->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Verificação de Credenciais</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #6A0DAD;
            border-bottom: 2px solid #6A0DAD;
            padding-bottom: 10px;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-info {
            background-color: #e8f4fd;
            border-left: 5px solid #2196F3;
        }
        .alert-success {
            background-color: #e8f5e9;
            border-left: 5px solid #4CAF50;
        }
        .alert-warning {
            background-color: #fff8e1;
            border-left: 5px solid #FFC107;
        }
        .alert-danger {
            background-color: #ffebee;
            border-left: 5px solid #F44336;
        }
        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #6A0DAD;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #580b92;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .code {
            font-family: monospace;
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
            color: #d63384;
        }
    </style>
</head>
<body>
    <h1>Teste de Verificação de Credenciais</h1>
    
    <div class="alert alert-warning">
        <strong>Atenção!</strong> Esta página é apenas para diagnóstico e deve ser removida em ambiente de produção.
    </div>
    
    <?php if (!empty($mensagem)): ?>
        <div class="alert <?php echo strpos($mensagem, 'não encontrado') !== false || strpos($mensagem, 'Erro') !== false || strpos($mensagem, 'incorreta') !== false ? 'alert-danger' : (strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-info'); ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email para verificar:</label>
            <input type="email" id="email" name="email" value="<?php echo exibirValor($email); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="senha">Senha para testar (opcional):</label>
            <input type="password" id="senha" name="senha" placeholder="Digite a senha para verificar">
        </div>
        
        <div class="form-group">
            <label for="tabela">Tabela:</label>
            <select id="tabela" name="tabela">
                <option value="hospedes" <?php echo $tabela === 'hospedes' ? 'selected' : ''; ?>>Hóspedes</option>
                <option value="funcionarios" <?php echo $tabela === 'funcionarios' ? 'selected' : ''; ?>>Funcionários</option>
            </select>
        </div>
        
        <button type="submit">Verificar</button>
    </form>
    
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <h2>Resultados da Consulta</h2>
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php $usuario = $resultado->fetch_assoc(); ?>
                <?php foreach ($usuario as $campo => $valor): ?>
                    <tr>
                        <td><?php echo exibirValor($campo); ?></td>
                        <td>
                            <?php 
                            // Exibir hash de senha de forma segura
                            if (strpos($campo, 'senha') !== false) {
                                echo exibirHashSeguro($valor);
                            } else {
                                echo exibirValor($valor);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Informações Adicionais</h2>
        <div class="alert alert-info">
            <p><strong>Verificação de Senha:</strong></p>
            <p>Para verificar se a senha está correta, digite-a no campo "Senha para testar" e envie o formulário novamente.</p>
            <p>O sistema usa <span class="code">password_verify()</span> para verificar senhas, que espera um hash gerado por <span class="code">password_hash()</span>.</p>
        </div>
    <?php endif; ?>
    
    <h2>Solução de Problemas</h2>
    <ul>
        <li>Se o usuário não for encontrado, verifique se o email está correto e está na tabela correta.</li>
        <li>Se a senha estiver incorreta, verifique se foi inserida corretamente.</li>
        <li>Se o hash da senha não começar com <span class="code">$2y$</span> (BCrypt), pode haver um problema no formato de armazenamento.</li>
        <li>Verifique se a função <span class="code">password_hash()</span> foi usada para criar a senha no registro.</li>
    </ul>
</body>
</html>