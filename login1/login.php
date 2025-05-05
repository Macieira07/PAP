<?php
session_start();

// Função para registrar logs no arquivo de erro do PHP
function debug_log($message, $data = null) {
    $log_message = date('[Y-m-d H:i:s]') . " - " . $message;
    if ($data !== null) {
        $log_message .= ": " . (is_array($data) || is_object($data) ? json_encode($data) : $data);
    }
    error_log($log_message);
}

// Evita que qualquer saída seja enviada antes dos cabeçalhos
ob_start();

// Verifica se é uma requisição POST (login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("Iniciando processo de login");
    
    // Configura o cabeçalho para resposta JSON
    header('Content-Type: application/json');
    
    try {
        // Verifica e gera token CSRF se necessário
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            debug_log("Novo CSRF token gerado");
        }

        // Inclui a conexão com o banco de dados
        require_once '../conexao.php';
        debug_log("Conexão com a base de dados incluída");

        // Verifica o token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            debug_log("Token CSRF inválido", ['recebido' => $_POST['csrf_token'] ?? 'não enviado', 'esperado' => $_SESSION['csrf_token']]);
            echo json_encode(['error' => 'Token CSRF inválido']);
            exit;
        }

        // Valida campos obrigatórios
        if (empty($_POST['email']) || empty($_POST['senha'])) {
            debug_log("Campos obrigatórios não preenchidos", ['email' => !empty($_POST['email']), 'senha' => !empty($_POST['senha'])]);
            echo json_encode(['error' => 'Por favor, preencha todos os campos.']);
            exit;
        }

        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);
        debug_log("Dados recebidos para login", ['email' => $email]);

        // Valida formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            debug_log("Email em formato inválido", $email);
            echo json_encode(['error' => 'Email inválido.']);
            exit;
        }

        // Verificar se a conexão está ativa
        if (!isset($conexao) || $conexao->connect_error) {
            debug_log("Conexão com o banco inválida", $conexao->connect_error ?? 'Variável $conexao não definida');
            echo json_encode(['error' => 'Erro na conexão com a base de dados']);
            exit;
        }

        // Função para verificar usuário - CORRIGIDA
        function verificarUsuario($conexao, $email, $senha, $tabela, $emailCol, $senhaCol, $idCol, $nomeCol) {
            debug_log("Verificando usuário na tabela $tabela", ['email' => $email]);
            
            $sql_code = "SELECT * FROM $tabela WHERE $emailCol = ?";
            $stmt = $conexao->prepare($sql_code);
            if (!$stmt) {
                debug_log("Erro ao preparar consulta", $conexao->error);
                return false;
            }

            $stmt->bind_param("s", $email);
            $result = $stmt->execute();
            if (!$result) {
                debug_log("Erro ao executar consulta", $stmt->error);
                return false;
            }
            
            $sql_query = $stmt->get_result();
            debug_log("Consulta executada", ['registros_encontrados' => $sql_query->num_rows]);

            if ($sql_query->num_rows == 1) {
                $usuario = $sql_query->fetch_assoc();
                debug_log("Usuário encontrado, verificando senha");
                
                // LOG para depuração - mostra a senha armazenada
                debug_log("DEBUG - Senha armazenada (hash)", $usuario[$senhaCol]);
                
                // Verificação melhorada de senha
                $senha_correta = false;
                
                // Primeiro tenta com password_verify (caso seja bcrypt)
                if (substr($usuario[$senhaCol], 0, 4) === '$2y$') {
                    $senha_correta = password_verify($senha, $usuario[$senhaCol]);
                    debug_log("Verificando com password_verify (bcrypt)", $senha_correta ? "Senha correta" : "Senha incorreta");
                }
                // Se não for bcrypt, compara diretamente (temporário, não recomendado para produção)
                else {
                    $senha_correta = ($senha === $usuario[$senhaCol]);
                    debug_log("Verificando com comparação direta", $senha_correta ? "Senha correta" : "Senha incorreta");
                }
                
                if ($senha_correta) {
                    debug_log("Password verificada com sucesso");
                    return $usuario;
                } else {
                    debug_log("Password incorreta");
                }
            } else {
                debug_log("Usuário não encontrado");
            }
            return false;
        }

        // Verifica se é funcionário
        $usuario = verificarUsuario($conexao, $email, $senha, 'funcionarios', 'F_email', 'F_senha', 'F_id_funcionario', 'F_nome');
        if ($usuario) {
            if (isset($usuario['F_ativo']) && $usuario['F_ativo'] != 'Sim') {
                debug_log("Conta de funcionário desativada", ['id' => $usuario['F_id_funcionario']]);
                echo json_encode(['error' => 'Sua conta está desativada.']);
                exit;
            }

            debug_log("Login de funcionário bem-sucedido", ['id' => $usuario['F_id_funcionario'], 'nome' => $usuario['F_nome']]);
            $_SESSION['id'] = $usuario['F_id_funcionario'];
            $_SESSION['nome'] = $usuario['F_nome'];
            $_SESSION['email'] = $usuario['F_email'];
            $_SESSION['tipo'] = 'funcionario';
            $_SESSION['F_cargo'] = $usuario['F_cargo'];
            $_SESSION['login_attempts'] = 0;

            // Log de login
            try {
                $log_sql = "INSERT INTO logs_acesso (usuario_id, tipo_usuario, acao, data) VALUES (?, 'funcionario', 'login', NOW())";
                $log_stmt = $conexao->prepare($log_sql);
                $log_stmt->bind_param("i", $usuario['F_id_funcionario']);
                $log_stmt->execute();
                debug_log("Log de acesso registrado");
            } catch (Exception $e) {
                debug_log("Erro ao registrar log de acesso", $e->getMessage());
            }

            echo json_encode(['redirect' => '../admin/admin.php']);
            exit;
        }

        // Verifica se é hóspede
        $usuario = verificarUsuario($conexao, $email, $senha, 'hospedes', 'H_email', 'H_senha', 'H_id_hospede', 'H_nome');
        if ($usuario) {
            // MODIFICAÇÃO: Comentado temporariamente para permitir login mesmo sem verificação de email
            /*
            if ($usuario['H_verificado_email'] != 'Sim') {
                debug_log("Conta de hóspede não verificada", ['id' => $usuario['H_id_hospede']]);
                echo json_encode(['error' => 'Por favor, verifique seu email antes de fazer login.']);
                exit;
            }
            */
            
            debug_log("Login de hóspede bem-sucedido", ['id' => $usuario['H_id_hospede'], 'nome' => $usuario['H_nome']]);
            $_SESSION['id'] = $usuario['H_id_hospede'];
            $_SESSION['nome'] = $usuario['H_nome'];
            $_SESSION['email'] = $usuario['H_email'];
            $_SESSION['tipo'] = 'hospede';
            $_SESSION['login_attempts'] = 0;

            try {
                $log_sql = "INSERT INTO logs_acesso (usuario_id, tipo_usuario, acao, data) VALUES (?, 'hospede', 'login', NOW())";
                $log_stmt = $conexao->prepare($log_sql);
                $log_stmt->bind_param("i", $usuario['H_id_hospede']);
                $log_stmt->execute();
                debug_log("Log de acesso registrado");
            } catch (Exception $e) {
                debug_log("Erro ao registrar log de acesso", $e->getMessage());
            }

            echo json_encode(['redirect' => '../pagamento/pagina1.php']);
            exit;
        }

        // Login falhou
        debug_log("Login falhou", ['email' => $email]);
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_login_attempt'] = time();

        try {
            $tipo = strpos($email, '@hotel.com') !== false ? 'funcionario' : 'hospede';
            $acao = 'tentativa_login';
            $log_sql = "INSERT INTO logs_acesso (email, tipo_usuario, acao, data, status) VALUES (?, ?, ?, NOW(), 'falha')";
            $log_stmt = $conexao->prepare($log_sql);
            $log_stmt->bind_param("sss", $email, $tipo, $acao);
            $log_stmt->execute();
            debug_log("Log de tentativa falha registrado");
        } catch (Exception $e) {
            debug_log("Erro ao registrar log de acesso", $e->getMessage());
        }

        sleep(min($_SESSION['login_attempts'], 5)); // antiflood com limite máximo

        // Adicionando informações de debug na resposta para visualização no console
        $debug_info = [
            'error' => 'Credenciais inválidas.',
            'debug' => [
                'tempo' => date('Y-m-d H:i:s'),
                'email_tentativa' => $email,
                'tipo_usuario_inferido' => $tipo,
                'verificacao' => 'Falha na verificação de senha ou utilizador não encontrado'
            ]
        ];
        echo json_encode($debug_info);
    } catch (Exception $e) {
        // Captura qualquer exceção não tratada
        debug_log("Erro não tratado durante o login", $e->getMessage());
        echo json_encode(['error' => 'Ocorreu um erro durante o processo de login. Por favor, tente novamente.']);
    }
    
    // Limpa e encerra qualquer saída pendente
    ob_end_flush();
    exit;
}

// Se não for POST, mostra o formulário normal
$csrf_token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
include('login_page.php');