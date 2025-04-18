<?php
session_start();
require_once '../conexao.php';

// Configurações globais
define('SITE_NAME', 'Quinta das Flores');
define('PRIMARY_COLOR', '#4a8f29');
define('EMAIL_COLOR', '#6a0dad');
define('CONTACT_PHONE', '+351 912 418 976');
define('CONTACT_EMAIL', 'quinta.flores2019@gmail.com');
define('PROPERTY_ADDRESS', 'Travessa da Seara 265-Calheiros, Ponte de Lima');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/reservas_errors.log');

if (!isset($_SESSION['id']) || !isset($_SESSION['checkin']) || !isset($_SESSION['checkout']) || !isset($_SESSION['num_hospedes'])) {
    error_log("Erro: Dados da reserva não encontrados na sessão");
    die('<div style="text-align: center; padding: 20px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 5px; max-width: 600px; margin: 20px auto;">
            <h2 style="color: #f44336;">Erro no Processamento</h2>
            <p>Dados da reserva não encontrados. Por favor, inicie o processo novamente.</p>
            <div style="margin-top: 20px;">
                <a href="../index.php" style="background-color: #f0f0f0; color: #333; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; border: 1px solid #ddd;">Voltar ao Índice</a>
            </div>
          </div>');
}

$checkin = filter_var($_SESSION['checkin'], FILTER_SANITIZE_STRING);
$checkout = filter_var($_SESSION['checkout'], FILTER_SANITIZE_STRING);
$num_hospedes = filter_var($_SESSION['num_hospedes'], FILTER_VALIDATE_INT);

if (!strtotime($checkin) || !strtotime($checkout)) {
    error_log("Datas inválidas: Checkin=$checkin, Checkout=$checkout");
    die("Datas inválidas.");
}

$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'basedados_pap';
$mysqli = new mysqli($host, $usuario, $senha, $banco);

if ($mysqli->connect_error) {
    error_log("Falha na conexão com o banco: " . $mysqli->connect_error);
    die('Falha na conexão com o banco de dados.');
}

$checkin_date = new DateTime($checkin);
$checkout_date = new DateTime($checkout);
$diferenca = $checkin_date->diff($checkout_date);
$num_noites = $diferenca->days;
$preco_por_noite = 120;
$preco_total = $num_noites * $preco_por_noite;

$servicos_adicionais = [];
$descricao_servicos = '';

if (isset($_SESSION['servicos'])) {
    foreach ($_SESSION['servicos'] as $servico) {
        switch ($servico) {
            case 'pequeno-almoco':
                $preco_total += 15 * $num_noites;
                $servicos_adicionais[] = 'Pequeno-Almoço (€15/noite)';
                break;
            case 'decoracao':
                $preco_total += 130;
                $servicos_adicionais[] = 'Decoração Temática (€130)';
                $descricao_servicos .= "Tema da Decoração: " . (isset($_SESSION['descricao_decoracao']) ? htmlspecialchars($_SESSION['descricao_decoracao']) : 'Não especificado');
                break;
            case 'limpeza':
                $preco_total += 15 * $num_noites;
                $servicos_adicionais[] = 'Limpeza Diária (€15/noite)';
                break;
            case 'cesto':
                $preco_total += 10;
                $servicos_adicionais[] = 'Cesto de Boas-Vindas (€10)';
                break;
            case 'jantar':
                $preco_total += 15 * $num_noites;
                $servicos_adicionais[] = 'Jantar (€15/noite)';
                break;
        }
    }
}

$query = "INSERT INTO reservas (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, R_num_hospedes, R_preco_total, R_estado, R_servicos)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexao->prepare($query);

$id_hospede = $_SESSION['id'];
$id_casa = 1;
$estado = 'confirmada';
$servicos_texto = !empty($servicos_adicionais) ? implode(', ', $servicos_adicionais) : 'Nenhum serviço adicional';

$stmt->bind_param('iissidss', $id_hospede, $id_casa, $checkin, $checkout, $num_hospedes, $preco_total, $estado, $servicos_texto);

if (!$stmt->execute()) {
    error_log("Erro ao salvar reserva: " . $stmt->error);
    die('<div style="text-align: center; padding: 20px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 5px; max-width: 600px; margin: 20px auto;">
            <h2 style="color: #f44336;">Erro no Processamento</h2>
            <p>Ocorreu um erro ao processar sua reserva. Por favor, tente novamente.</p>
            <div style="margin-top: 20px;">
                <a href="../index.php" style="background-color: #f0f0f0; color: #333; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; border: 1px solid #ddd;">Voltar ao Índice</a>
            </div>
          </div>');
}

$reserva_id = $conexao->insert_id;
error_log("Nova reserva criada: ID=$reserva_id para {$_SESSION['email']}");

require_once('tcpdf/tcpdf.php');
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(SITE_NAME);
$pdf->SetAuthor(SITE_NAME);
$pdf->SetTitle('Comprovante de Reserva');
$pdf->SetSubject('Reserva ' . SITE_NAME);
$pdf->SetKeywords('Reserva, ' . SITE_NAME . ', Comprovante');

$pdf->AddPage();
$pdf->Image('../logotipos/logotipo1.jpg', 10, 10, 30, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
$pdf->SetY(40);
$pdf->SetFont('helvetica', '', 12);

$html = '
<h1 style="color:'.EMAIL_COLOR.';">Detalhes da Reserva</h1>
<table border="0" cellpadding="4">
    <tr><td width="30%"><strong>Nº Reserva:</strong></td><td>'.$reserva_id.'</td></tr>
    <tr><td><strong>Nome:</strong></td><td>'.htmlspecialchars($_SESSION['nome'].' '.$_SESSION['apelido']).'</td></tr>
    <tr><td><strong>E-mail:</strong></td><td>'.htmlspecialchars($_SESSION['email']).'</td></tr>
    <tr><td><strong>Check-in:</strong></td><td>'.htmlspecialchars($checkin).' (a partir das 15:00)</td></tr>
    <tr><td><strong>Check-out:</strong></td><td>'.htmlspecialchars($checkout).' (até às 11:00)</td></tr>
    <tr><td><strong>Hóspedes:</strong></td><td>'.$num_hospedes.'</td></tr>
    <tr><td><strong>Noites:</strong></td><td>'.$num_noites.'</td></tr>';

if (!empty($servicos_adicionais)) {
    $html .= '<tr><td><strong>Serviços Adicionais:</strong></td><td>'.implode('<br>', $servicos_adicionais).'</td></tr>';
    if (!empty($descricao_servicos)) {
        $html .= '<tr><td><strong>Detalhes Decoração:</strong></td><td>'.$descricao_servicos.'</td></tr>';
    }
}

$html .= '
    <tr><td><strong>Total:</strong></td><td>'.$preco_total.' €</td></tr>
    <tr><td><strong>Morada:</strong></td><td>'.PROPERTY_ADDRESS.'</td></tr>
</table>

<h2 style="color:'.EMAIL_COLOR.';">Informações Importantes</h2>
<ul>
    <li>Check-in a partir das 15:00 horas</li>
    <li>Check-out até às 11:00 horas</li>
    <li>Traga este comprovante quando chegar à Quinta</li>
    <li>Taxa adicional para check-out atrasado</li>
    <li>Cancelamento gratuito até 10 dias antes da data de check-in</li>
</ul>

<p style="text-align:center;margin-top:30px;">Obrigado por escolher '.SITE_NAME.'! Esperamos proporcionar-lhe uma estadia memorável e repleta de momentos especiais.</p>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdfContent = $pdf->Output('', 'S');

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = CONTACT_EMAIL;
    $mail->Password = 'svwy ziac roqo ygzw';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom(CONTACT_EMAIL, SITE_NAME);
    $mail->addAddress($_SESSION['email'], $_SESSION['nome'].' '.$_SESSION['apelido']);
    $mail->addReplyTo(CONTACT_EMAIL, SITE_NAME);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = '✅ Reserva #'.$reserva_id.' Confirmada - '.SITE_NAME.' | '.$checkin.' a '.$checkout;
    
    $servicos_email = '';
    if (!empty($servicos_adicionais)) {
        $servicos_email = '<div style="margin: 15px 0;">
            <h3 style="color:'.EMAIL_COLOR.'; margin-bottom: 10px;">Serviços Adicionais</h3>
            <ul>';
        
        foreach ($servicos_adicionais as $servico) {
            $servicos_email .= '<li>'.$servico.'</li>';
        }
        
        $servicos_email .= '</ul>';
        
        if (!empty($descricao_servicos)) {
            $servicos_email .= '<p><strong>Detalhes da Decoração:</strong> '.$descricao_servicos.'</p>';
        }
        
        $servicos_email .= '</div>';
    }
    
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmação de Reserva - '.SITE_NAME.'</title>
        <link rel="icon" type="image/x-icon" href="../logotipos/logotipo1.jpg">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: '.EMAIL_COLOR.'; color: white; padding: 25px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 25px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
            .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
            h1 { color: '.EMAIL_COLOR.'; }
            .highlight { background-color: #f8f8f8; padding: 15px; border-left: 4px solid '.EMAIL_COLOR.'; margin: 20px 0; }
            .button { background-color: '.EMAIL_COLOR.'; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; }
            .details { margin: 15px 0; }
            .details-item { margin-bottom: 12px; }
            .contact-box { background-color: #f0e6ff; padding: 15px; border-radius: 5px; margin: 25px 0; }
            .logo { text-align: center; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="logo">
            <img src="../logotipos/logotipo1.jpg" alt="'.SITE_NAME.'" style="max-width: 150px;">
        </div>
        
        <div class="header">
            <h1 style="color: white; margin: 0; font-size: 24px;">'.SITE_NAME.'</h1>
            <p style="margin: 5px 0 0; font-size: 18px;">Reserva Confirmada #'.$reserva_id.'</p>
        </div>
        
        <div class="content">
            <p>Olá, '.htmlspecialchars($_SESSION['nome']).'!</p>
            <p>Agradecemos por escolher a '.SITE_NAME.' para sua estadia. Abaixo estão os detalhes da sua reserva:</p>
            
            <div class="highlight">
                <h2 style="margin-top: 0; color: '.EMAIL_COLOR.';">Detalhes da Reserva</h2>
                <div class="details">
                    <div class="details-item"><strong>Check-in:</strong> '.htmlspecialchars($checkin).' (a partir das 15:00)</div>
                    <div class="details-item"><strong>Check-out:</strong> '.htmlspecialchars($checkout).' (até às 11:00)</div>
                    <div class="details-item"><strong>Nº de Hóspedes:</strong> '.$num_hospedes.'</div>
                    <div class="details-item"><strong>Nº de Noites:</strong> '.$num_noites.'</div>
                    '.$servicos_email.'
                    <div class="details-item"><strong>Preço Total:</strong> '.$preco_total.' €</div>
                </div>
            </div>
            
            <h2 style="color: '.EMAIL_COLOR.';">Informações Importantes</h2>
            <ul>
                <li>Check-in a partir das 15:00 horas</li>
                <li>Check-out até às 11:00 horas</li>
                <li>Traga o comprovante em anexo quando chegar à Quinta</li>
                <li>Não é permitido fumar nas dependências</li>
                <li>Cancelamento gratuito até 10 dias antes da data de check-in</li>
            </ul>
            
            <p>Em anexo, você encontrará o comprovante da sua reserva em PDF.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="https://www.google.com/maps?q='.urlencode(PROPERTY_ADDRESS).'" class="button">Ver Localização no Mapa</a>
            </div>
            
            <div class="contact-box">
                <h3 style="margin-top: 0; color: '.EMAIL_COLOR.';">Precisa de ajuda?</h3>
                <p><strong>E-mail:</strong> '.CONTACT_EMAIL.'</p>
                <p><strong>Endereço:</strong> '.PROPERTY_ADDRESS.'</p>
            </div>
            
            <p>Estamos ansiosos para recebê-lo(a) na '.SITE_NAME.' e proporcionar uma estadia memorável repleta de momentos especiais!</p>
            
            <p>Atenciosamente,<br>Equipa '.SITE_NAME.'</p>
        </div>
        
        <div class="footer">
            <p>© '.date('Y').' '.SITE_NAME.'. Todos os direitos reservados.</p>
            <p>Este é um e-mail automático, por favor não responda diretamente.</p>
        </div>
    </body>
    </html>
    ';

    $mail->AltBody = "Olá {$_SESSION['nome']},\n\nSua reserva na ".SITE_NAME." foi confirmada.\n\nDetalhes:\nCheck-in: {$checkin}\nCheck-out: {$checkout}\nHóspedes: {$num_hospedes}\nNoites: {$num_noites}\n\nServiços Adicionais:\n".implode("\n", $servicos_adicionais)."\n\nTotal: {$preco_total} €\n\nLocal: ".PROPERTY_ADDRESS."\n\nIMPORTANTE: Traga este comprovante quando chegar à Quinta.\nCancelamento gratuito até 10 dias antes da data de check-in.\n\nEm anexo encontrará o comprovante completo.\n\nPara dúvidas: ".CONTACT_EMAIL."\n\nAtenciosamente,\nEquipa ".SITE_NAME;
    
    $mail->addStringAttachment($pdfContent, 'Comprovante_Reserva_'.$reserva_id.'.pdf');
    
    $ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//".SITE_NAME."//Reserva//PT
BEGIN:VEVENT
UID:".md5($reserva_id)."@".SITE_NAME."
DTSTAMP:".gmdate('Ymd').'T'.gmdate('His')."Z
DTSTART:".date('Ymd', strtotime($checkin))."T150000Z
DTEND:".date('Ymd', strtotime($checkout))."T110000Z
SUMMARY:Reserva ".SITE_NAME."
DESCRIPTION:Reserva confirmada para ".$_SESSION['nome']." ".$_SESSION['apelido']."\\nCheck-in: ".$checkin."\\nCheck-out: ".$checkout."\\nHóspedes: ".$num_hospedes."\\nTotal: ".$preco_total." €
LOCATION:".PROPERTY_ADDRESS."
END:VEVENT
END:VCALENDAR";
    
    $mail->addStringAttachment($ical, 'evento.ics');

    if ($mail->send()) {
        error_log("E-mail enviado com sucesso para {$_SESSION['email']}");
        
        echo '<div style="text-align: center; padding: 40px 20px; max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;">
                <img src="../logotipos/logotipo1.jpg" alt="'.SITE_NAME.'" style="max-width: 150px; margin-bottom: 20px;">
                <svg width="100" height="100" viewBox="0 0 24 24" style="fill:'.PRIMARY_COLOR.';margin:0 auto 25px;">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <h2 style="color:'.PRIMARY_COLOR.'; font-size: 28px; margin-bottom: 15px;">Reserva Confirmada com Sucesso!</h2>
                <p style="font-size: 18px; margin-bottom: 10px;">O comprovante foi enviado para:</p>
                <p style="font-size: 18px; font-weight: bold; margin: 0 0 30px;">'.htmlspecialchars($_SESSION['email']).'</p>
                
                <div style="background-color: #f8f8f8; border-radius: 8px; padding: 20px; margin: 30px auto; max-width: 500px; text-align: left;">
                    <h3 style="color:'.PRIMARY_COLOR.'; margin-top: 0;">Resumo da Reserva</h3>
                    <p><strong>Nº Reserva:</strong> '.$reserva_id.'</p>
                    <p><strong>Check-in:</strong> '.htmlspecialchars($checkin).'</p>
                    <p><strong>Check-out:</strong> '.htmlspecialchars($checkout).'</p>
                    <p><strong>Hóspedes:</strong> '.$num_hospedes.'</p>';
        
        if (!empty($servicos_adicionais)) {
            echo '<p><strong>Serviços Adicionais:</strong><br>';
            foreach ($servicos_adicionais as $servico) {
                echo htmlspecialchars($servico).'<br>';
            }
            echo '</p>';
            
            if (!empty($descricao_servicos)) {
                echo '<p><strong>Detalhes da Decoração:</strong><br>'.htmlspecialchars($descricao_servicos).'</p>';
            }
        }
        
        echo '          <p><strong>Valor Total:</strong> '.$preco_total.' €</p>
                </div>
                
                <p style="font-size: 16px; margin-bottom: 20px;">Por favor, traga o comprovante em anexo quando chegar à Quinta.</p>
                <p style="font-size: 16px; margin-bottom: 30px;">Cancelamento gratuito até 10 dias antes da data de check-in.</p>
                
                <p style="font-size: 16px; margin-bottom: 30px;">Obrigado por escolher a '.SITE_NAME.'! Esperamos proporcionar-lhe uma estadia memorável e repleta de momentos especiais.</p>
                
                <div style="margin-top: 40px;">
                    <a href="../index.html" style="background-color:'.PRIMARY_COLOR.'; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold; font-size:16px;">Voltar ao Índice</a>
                </div>
              </div>';
    } else {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        echo '<div style="text-align: center; padding: 20px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 5px; max-width: 600px; margin: 20px auto;">
                <img src="../logotipos/logotipo1.jpg" alt="'.SITE_NAME.'" style="max-width: 150px; margin-bottom: 20px;">
                <h2 style="color: #f44336;">Reserva Confirmada!</h2>
                <p style="font-size: 16px;">Sua reserva foi registrada com sucesso (Nº '.$reserva_id.'), mas houve um problema ao enviar o e-mail de confirmação.</p>
                <p style="font-size: 16px;">Por favor, entre em contato conosco pelo e-mail '.CONTACT_EMAIL.' para obter os detalhes.</p>
                <div style="margin-top: 30px;">
                    <a href="../index.php" style="background-color: #f0f0f0; color: #333; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; margin-right: 10px;">Voltar ao Índice</a>
                    <a href="mailto:'.CONTACT_EMAIL.'" style="background-color:'.PRIMARY_COLOR.'; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold;">Contatar Suporte</a>
                </div>
              </div>';
    }
} catch (Exception $e) {
    error_log("Exception ao enviar e-mail: " . $e->getMessage());
    echo '<div style="text-align: center; padding: 20px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 5px; max-width: 600px; margin: 20px auto;">
            <img src="../logotipos/logotipo1.jpg" alt="'.SITE_NAME.'" style="max-width: 150px; margin-bottom: 20px;">
            <h2 style="color: #f44336;">Erro no Processamento</h2>
            <p style="font-size: 16px;">Ocorreu um erro ao processar sua reserva. Por favor, tente novamente.</p>
            <p style="font-size: 14px; color: #666;">Detalhes: '.htmlspecialchars($e->getMessage()).'</p>
            <div style="margin-top: 30px;">
                <a href="../index.php" style="background-color: #f0f0f0; color: #333; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; margin-right: 10px;">Voltar ao Índice</a>
                <a href="mailto:'.CONTACT_EMAIL.'" style="background-color:'.PRIMARY_COLOR.'; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold;">Contatar Suporte</a>
            </div>
          </div>';
}

session_destroy();
?>  