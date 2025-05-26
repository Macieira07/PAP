<?php
session_start();

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Método de requisição inválido.'
    ];
    header('Location: lermais.html');
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Verificar se os arquivos do PHPMailer existem
$phpmailer_path = '../pagamento/PHPMailer/src/PHPMailer.php';
if (!file_exists($phpmailer_path)) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Erro: PHPMailer não encontrado. Verifique o caminho dos arquivos.'
    ];
    header('Location: contact.php');
    exit;
}

require $phpmailer_path;
require '../pagamento/PHPMailer/src/SMTP.php';
require '../pagamento/PHPMailer/src/Exception.php';

// Função para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para limpar dados de entrada
function limparDados($dados) {
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

try {
    // Receber e validar dados do formulário
    $nome = limparDados($_POST['name'] ?? '');
    $email = limparDados($_POST['email'] ?? '');
    $assunto = limparDados($_POST['subject'] ?? '');
    $mensagem = limparDados($_POST['message'] ?? '');

    // Validações
    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        throw new Exception('Por favor, preencha todos os campos obrigatórios.');
    }

    if (!validarEmail($email)) {
        throw new Exception('Por favor, introduza um email válido.');
    }

    if (strlen($nome) < 2) {
        throw new Exception('O nome deve ter pelo menos 2 caracteres.');
    }

    if (strlen($mensagem) < 10) {
        throw new Exception('A mensagem deve ter pelo menos 10 caracteres.');
    }

    // Configurar PHPMailer
    $mail = new PHPMailer(true);

    // Configurações do servidor SMTP
    $mail->SMTPDebug = 0; // Mudar para 2 para debug
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'quinta.flores2019@gmail.com'; // Seu email Gmail
    $mail->Password = 'kgre oqhy kxcn grid'; // Sua senha de aplicação
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configurações do email
    $mail->setFrom('quinta.flores2019@gmail.com', 'Site Quinta Flores'); // Usar seu próprio email como remetente
    $mail->addAddress('quinta.flores2019@gmail.com', 'Quinta Flores'); // Destinatário
    $mail->addReplyTo($email, $nome); // Para responder diretamente ao cliente

    // Conteúdo do email
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "Nova mensagem do site: " . $assunto;
    
    // Corpo do email em HTML
    $corpoHTML = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;'>
            Nova Mensagem do Site
        </h2>
        
        <div style='background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;'>
            <p><strong>Nome:</strong> {$nome}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Assunto:</strong> {$assunto}</p>
        </div>
        
        <div style='background-color: #ffffff; padding: 20px; border-left: 4px solid #4CAF50; margin: 20px 0;'>
            <h3 style='color: #333; margin-top: 0;'>Mensagem:</h3>
            <p style='line-height: 1.6; color: #555;'>" . nl2br($mensagem) . "</p>
        </div>
        
        <div style='color: #777; font-size: 12px; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 20px;'>
            <p>Esta mensagem foi enviada através do formulário de contacto do site em " . date('d/m/Y \à\s H:i:s') . "</p>
        </div>
    </div>";

    $mail->Body = $corpoHTML;

    // Versão texto alternativa
    $corpoTexto = "Nova Mensagem do Site\n\n";
    $corpoTexto .= "Nome: {$nome}\n";
    $corpoTexto .= "Email: {$email}\n";
    $corpoTexto .= "Assunto: {$assunto}\n\n";
    $corpoTexto .= "Mensagem:\n{$mensagem}\n\n";
    $corpoTexto .= "Enviado em: " . date('d/m/Y às H:i:s');

    $mail->AltBody = $corpoTexto;

    // Enviar email
    if ($mail->send()) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Mensagem enviada com sucesso! Responderemos em breve.'
        ];
    } else {
        throw new Exception('Erro ao enviar a mensagem.');
    }

} catch (Exception $e) {
    // Log do erro (opcional)
    error_log("Erro no envio de email: " . $e->getMessage());
    
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()
    ];
}

// Redirecionar de volta para a página de contacto
header('Location: lermais.html');
exit;
?>