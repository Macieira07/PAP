<?php
session_start();
require_once '../conexao.php';

// Configurações globais
define('SITE_NAME', 'Quinta Flores');
define('PRIMARY_COLOR', '#4a8f29');
define('EMAIL_COLOR', '#4a8f29');
define('CONTACT_PHONE', '+351 912 418 976');
define('CONTACT_EMAIL', 'quinta.flores2019@gmail.com');
define('PROPERTY_ADDRESS', 'Travessa da Seara 265-Calheiros, Ponte de Lima');
define('RNAL', 'AL123456'); // Número de registo de alojamento local

ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/reservas_errors.log');

// Verificar oferta
$oferta_info = '';
if (isset($_SESSION['codigo_oferta'])) {
    $codigos_oferta = [
        'LOVE260' => 'Pacote Amor (2 noites - €260)',
        'PARTY260' => 'Pacote Festa com Amigos (2 noites - €260)',
        'RETIRO240' => 'Pacote Retiro na Catequese (2 noites - €240)'
    ];
    $codigo = $_SESSION['codigo_oferta'];
    $oferta_info = isset($codigos_oferta[$codigo]) ? $codigos_oferta[$codigo] : $codigo;
}

// Verificar se o ID da reserva está na sessão
if (!isset($_SESSION['reserva_id'])) {
    error_log("Erro: ID da reserva não encontrado na sessão");
    die('<div style="text-align: center; padding: 20px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 5px; max-width: 600px; margin: 20px auto;">
            <h2 style="color: #f44336;">Erro no Processamento</h2>
            <p>Dados da reserva não encontrados. Por favor, inicie o processo novamente.</p>
            <div style="margin-top: 20px;">
                <a href="../index.php" style="background-color: #f0f0f0; color: #333; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; border: 1px solid #ddd;">Voltar à nossa página inicial</a>
            </div>
          </div>');
}

$reserva_id = $_SESSION['reserva_id'];

// Buscar os dados da reserva no banco
$query = "SELECT r.*, c.C_nome as casa_nome FROM reservas r LEFT JOIN casas c ON r.R_id_casa = c.C_id_casa WHERE r.R_id_reserva = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param('i', $reserva_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    error_log("Erro: Reserva não encontrada no banco para ID $reserva_id");
    die('<div style="text-align: center; padding: 20px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 5px; max-width: 600px; margin: 20px auto;">
            <h2 style="color: #f44336;">Erro no Processamento</h2>
            <p>Reserva não encontrada. Por favor, inicie o processo novamente.</p>
            <div style="margin-top: 20px;">
                <a href="../index.php" style="background-color: #f0f0f0; color: #333; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; border: 1px solid #ddd;">Voltar à nossa página inicial</a>
            </div>
          </div>');
}
$reserva = $res->fetch_assoc();

// Limpar reserva_id da sessão para evitar duplicidade
unset($_SESSION['reserva_id']);

// Agora use $reserva para preencher os dados da confirmação, PDF, e-mail, etc.
// Exemplo de variáveis:
$checkin = $reserva['R_data_checkin'];
$checkout = $reserva['R_data_checkout'];
$num_hospedes = $reserva['R_num_hospedes'];
$preco_total = $reserva['R_preco_total'];
$metodo_pagamento = $reserva['R_metodo_pagamento'];
$servicos_texto = $reserva['R_servicos'] ?? '';
$casa_nome = $reserva['casa_nome'] ?? 'Casa de Campo';

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
        }
    }
}
// Aplicar desconto da oferta se existir
if (isset($_SESSION['codigo_oferta'])) {
    switch ($_SESSION['codigo_oferta']) {
        case 'LOVE260':
        case 'PARTY260':
            $preco_total = 260;
            break;
        case 'RETIRO240':
            $preco_total = 240;
            break;
    }
}

// Gerar PDF
require_once('tcpdf/tcpdf.php');
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(SITE_NAME);
$pdf->SetAuthor(SITE_NAME);
$pdf->SetTitle('Fatura #' . $reserva_id);
$pdf->SetSubject('Fatura de Alojamento Local');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();

// Cabeçalho
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 8, SITE_NAME, 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Alojamento Local • RNAL: ' . RNAL, 0, 1, 'L');
$pdf->Cell(0, 5, 'Telefone: ' . CONTACT_PHONE . ' • Email: ' . CONTACT_EMAIL, 0, 1, 'L');

// Linha divisória
$pdf->SetDrawColor(74, 143, 41);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY()+3, 195, $pdf->GetY()+3);
$pdf->Ln(8);

// Título
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'FATURA/RECIBO', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(100, 5, 'Número: ' . str_pad($reserva_id, 5, '0', STR_PAD_LEFT), 0, 0, 'L');
$pdf->Cell(0, 5, 'Data: ' . date('d/m/Y'), 0, 1, 'R');
$pdf->Ln(5);

// Dados do cliente
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'Cliente:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, $_SESSION['nome'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Contacto: ' . (isset($_SESSION['telefone']) ? $_SESSION['telefone'] : 'Não informado'), 0, 1, 'L');
$pdf->Cell(0, 5, 'Email: ' . $_SESSION['email'], 0, 1, 'L');
$pdf->Ln(8);

// Detalhes da reserva - Lista no lado esquerdo
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'Detalhes da Estadia:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Lista de detalhes
$detalhes = [
    'Alojamento' => $casa_nome,
    'Check-in' => $checkin_date->format('d/m/Y') . ' (a partir das 15:00)',
    'Check-out' => $checkout_date->format('d/m/Y') . ' (até às 11:00)',
    'Nº de Noites' => $num_noites,
    'Nº de Hóspedes' => $num_hospedes,
    'Método de Pagamento' => $metodo_pagamento
];

foreach ($detalhes as $label => $value) {
    $pdf->Cell(50, 5, $label . ':', 0, 0, 'L');
    $pdf->Cell(0, 5, $value, 0, 1, 'L');
}

// Adicionar oferta se existir
if (!empty($oferta_info)) {
    $pdf->Cell(50, 5, 'Oferta Especial:', 0, 0, 'L');
    $pdf->Cell(0, 5, $oferta_info, 0, 1, 'L');
}

$pdf->Ln(5);

// Tabela de valores
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(120, 7, 'Descrição', 1, 0, 'L');
$pdf->Cell(30, 7, 'Qtd', 1, 0, 'C');
$pdf->Cell(30, 7, 'Valor', 1, 1, 'R');

$pdf->SetFont('helvetica', '', 10);

// Mostrar oferta ou hospedagem normal
if (!empty($oferta_info)) {
    $pdf->Cell(120, 7, 'Pacote Promocional (' . $oferta_info . ')', 1, 0, 'L');
    $pdf->Cell(30, 7, '1', 1, 0, 'C');
    $pdf->Cell(30, 7, '€' . number_format($preco_total, 2, ',', '.'), 1, 1, 'R');
} else {
    $pdf->Cell(120, 7, 'Hospedagem (Casa de Campo)', 1, 0, 'L');
    $pdf->Cell(30, 7, $num_noites . ' noite' . ($num_noites > 1 ? 's' : ''), 1, 0, 'C');
    $pdf->Cell(30, 7, '€' . number_format($preco_por_noite * $num_noites, 2, ',', '.'), 1, 1, 'R');
}

// Serviços adicionais (se existirem)
if (!empty($servicos_adicionais)) {
    foreach ($servicos_adicionais as $servico) {
        $pdf->Cell(120, 7, 'Serviço: ' . trim($servico), 1, 0, 'L');
        $pdf->Cell(30, 7, '1', 1, 0, 'C');
        
        // Extrair valor do serviço
        preg_match('/€(\d+)/', $servico, $matches);
        $valor_servico = isset($matches[1]) ? $matches[1] : '0';
        $pdf->Cell(30, 7, '€' . number_format($valor_servico, 2, ',', '.'), 1, 1, 'R');
    }
}
// Total
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(120, 8, 'TOTAL', 1, 0, 'R');
$pdf->Cell(60, 8, '€' . number_format($preco_total, 2, ',', '.'), 1, 1, 'R');
$pdf->Ln(10);
// Informações adicionais
$pdf->SetFont('helvetica', 'I', 8);
$pdf->MultiCell(0, 4, 'Este documento serve como fatura-recibo nos termos do artigo 42.º do Código do IVA. Isento de IVA - alínea 14 do artigo 9.º do Código do IVA.', 0, 'L');
$pdf->Ln(5);
$pdf->Cell(0, 4, 'Processado por sistema automático em ' . date('d/m/Y H:i'), 0, 1, 'L');
$pdfContent = $pdf->Output('', 'S');
// Envio de email
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
    $mail->Password = 'cbra fjzb nizo lilw';
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
    $mail->addAddress($_SESSION['email'], $_SESSION['nome']);
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
    // Adicionar oferta ao email se existir
    $oferta_email = '';
    if (!empty($oferta_info)) {
        $oferta_email = '<div style="margin: 15px 0;">
            <h3 style="color:'.EMAIL_COLOR.'; margin-bottom: 10px;">Oferta Especial</h3>
            <p>'.$oferta_info.'</p>
        </div>';
    }
    // Adicionar método de pagamento
    $pagamento_email = '<div style="margin: 15px 0;">
        <h3 style="color:'.EMAIL_COLOR.'; margin-bottom: 10px;">Método de Pagamento</h3>
        <p>'.$metodo_pagamento.'</p>
    </div>';
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmação de Reserva - '.SITE_NAME.'</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: '.EMAIL_COLOR.'; color: white; padding: 25px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 25px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
            .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
            h1 { color: '.EMAIL_COLOR.'; }
            .highlight { background-color: #f8f8f8; padding: 15px; border-left: 4px solid '.EMAIL_COLOR.'; margin: 20px 0; }
            .details { margin: 15px 0; }
            .details-item { margin-bottom: 12px; }
            .logo { text-align: center; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="logo">
            <img src="../assets/logos/logotipo1.png" alt="'.SITE_NAME.'" style="max-width: 150px;">
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
                    '.$oferta_email.'
                    '.$servicos_email.'
                    '.$pagamento_email.'
                    <div class="details-item"><strong>Detalhes do Alojamento:</strong> Casa de campo com todas as comodidades</div>
                    <div class="details-item"><strong>Morada:</strong> '.PROPERTY_ADDRESS.'</div>
                    <div class="details-item"><strong>Telefone:</strong> '.CONTACT_PHONE.'</div>
                    <div class="details-item"><strong>Preço Total:</strong> '.number_format($preco_total, 2, ',', '.').' €</div>
                </div>
            </div>
            <p>Em anexo, você encontrará a fatura/recibo da sua reserva em PDF.</p>
            
            <p>Atenciosamente,<br>Quinta Flores</p>
        </div>
        <div class="footer">
            <p>© '.date('Y').' '.SITE_NAME.'. Todos os direitos reservados.</p>
            <p>Este é um e-mail automático, por favor não responda diretamente.</p>
        </div>
    </body>
    </html>
    ';
    $mail->AltBody = "Olá {$_SESSION['nome']},\n\nSua reserva na ".SITE_NAME." foi confirmada.\n\nDetalhes:\nCheck-in: {$checkin}\nCheck-out: {$checkout}\nHóspedes: {$num_hospedes}\nNoites: {$num_noites}\n\nServiços Adicionais:\n".implode("\n", $servicos_adicionais)."\n\nOferta: {$oferta_info}\n\nMétodo de Pagamento: {$metodo_pagamento}\n\nTotal: {$preco_total} €\n\nLocal: ".PROPERTY_ADDRESS."\n\nAtenciosamente,\nQuinta Flores";
    $mail->addStringAttachment($pdfContent, 'Fatura_Reserva_'.str_pad($reserva_id, 5, '0', STR_PAD_LEFT).'.pdf');
    
    $ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//".SITE_NAME."//Reserva//PT
BEGIN:VEVENT
UID:".md5($reserva_id)."@".SITE_NAME."
DTSTAMP:".gmdate('Ymd').'T'.gmdate('His')."Z
DTSTART:".date('Ymd', strtotime($checkin))."T150000Z
DTEND:".date('Ymd', strtotime($checkout))."T110000Z
SUMMARY:Reserva ".SITE_NAME."
DESCRIPTION:Reserva confirmada para ".$_SESSION['nome']."\\nCheck-in: ".$checkin."\\nCheck-out: ".$checkout."\\nHóspedes: ".$num_hospedes."\\nTotal: ".$preco_total." €
LOCATION:".PROPERTY_ADDRESS."
END:VEVENT
END:VCALENDAR";
    $mail->addStringAttachment($ical, 'evento.ics');
    if ($mail->send()) {
        error_log("E-mail enviado com sucesso para {$_SESSION['email']}");
        echo '<div style="text-align: center; padding: 40px 20px; max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;">
                <img src="../assets/logos/logotipo1.png" alt="'.SITE_NAME.'" style="max-width: 150px; margin-bottom: 20px;">
                <svg width="100" height="100" viewBox="0 0 24 24" style="fill:'.PRIMARY_COLOR.';margin:0 auto 25px;">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <h2 style="color:'.PRIMARY_COLOR.'; font-size: 28px; margin-bottom: 15px;">Reserva Confirmada com Sucesso!</h2>
                <p style="font-size: 18px; margin-bottom: 10px;">A fatura/recibo foi enviada para:</p>
                <p style="font-size: 18px; font-weight: bold; margin: 0 0 30px;">'.htmlspecialchars($_SESSION['email']).'</p>
                
                <p style="font-size: 16px; margin-bottom: 30px;"><strong>Por favor, traga o comprovativo imprimido quando chegar ao alojamento.</strong></p>
                
                <div style="margin-top: 40px;">
                    <a href="../index.php" style="background-color:'.PRIMARY_COLOR.'; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold; font-size:16px;">Voltar ao Índice</a>
                </div>
              </div>';
    } else {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        echo '<div style="text-align: center; padding: 20px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 5px; max-width: 600px; margin: 20px auto;">
                <img src="../assets/logos/logotipo1.png" alt="'.SITE_NAME.'" style="max-width: 150px; margin-bottom: 20px;">
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
            <img src="../assets/logos/logotipo1.png" alt="'.SITE_NAME.'" style="max-width: 150px; margin-bottom: 20px;">
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