<?php
ob_start(); // Previne saídas antes do PDF

// Configurações globais
define('SITE_NAME', 'Quinta das Flores');
define('PRIMARY_COLOR', '#4a8f29');
define('EMAIL_COLOR', '#4a8f29');
define('CONTACT_PHONE', '+351 912 418 976');
define('CONTACT_EMAIL', 'quinta.flores2019@gmail.com');
define('PROPERTY_ADDRESS', 'Travessa da Seara 265-Calheiros, Ponte de Lima');

require __DIR__ . '../../pagamento/tcpdf/tcpdf.php';
require __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) {
    die('Reserva não especificada.');
}
$reserva_id = (int)$_GET['id'];

// Obter dados da reserva
$query = "SELECT r.*, h.H_nome, h.H_email, h.H_telefone,
                 c.C_nome, c.C_preco_noite
          FROM reservas r
          JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
          JOIN casas c ON r.R_id_casa = c.C_id_casa
          WHERE r.R_id_reserva = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $reserva_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die('Reserva não encontrada.');
}
$r = $res->fetch_assoc();
$stmt->close();

// Calcular noites
$checkin = new DateTime($r['R_data_checkin']);
$checkout = new DateTime($r['R_data_checkout']);
$noites = $checkout->diff($checkin)->days;

// Gerar PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(SITE_NAME);
$pdf->SetAuthor(SITE_NAME);
$pdf->SetTitle('Comprovante de Reserva #' . $reserva_id);
$pdf->SetSubject('Reserva ' . SITE_NAME);
$pdf->SetKeywords('Reserva, ' . SITE_NAME . ', Comprovante');

// Remover cabeçalho e rodapé padrões
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// Cabeçalho personalizado
$pdf->Image('../assets/logos/logotipo1.png', 15, 10, 30, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetY(15);
$pdf->Cell(0, 10, SITE_NAME, 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 5, 'Comprovante de Reserva #' . $reserva_id, 0, 1, 'C');
$pdf->Line(10, 35, 200, 35);
$pdf->SetY(45);

// Extrair serviços adicionais
$servicos_adicionais = [];
if (!empty($r['R_servicos']) && $r['R_servicos'] != 'Nenhum serviço adicional') {
    $servicos_adicionais = explode(', ', $r['R_servicos']);
}

// Conteúdo principal do PDF
$pdf->SetFont('helvetica', '', 12);
$html = '
<h1 style="color:'.EMAIL_COLOR.';">Detalhes da Reserva</h1>
<table border="0" cellpadding="4">
    <tr><td width="40%"><strong>Nº Reserva:</strong></td><td>'.$reserva_id.'</td></tr>
    <tr><td><strong>Nome:</strong></td><td>'.htmlspecialchars($r['H_nome']).'</td></tr>
    <tr><td><strong>E-mail:</strong></td><td>'.htmlspecialchars($r['H_email']).'</td></tr>
    <tr><td><strong>Telefone:</strong></td><td>'.htmlspecialchars($r['H_telefone']).'</td></tr>
    <tr><td><strong>Check-in:</strong></td><td>'.htmlspecialchars($r['R_data_checkin']).' (a partir das 15:00)</td></tr>
    <tr><td><strong>Check-out:</strong></td><td>'.htmlspecialchars($r['R_data_checkout']).' (até às 11:00)</td></tr>
    <tr><td><strong>Hóspedes:</strong></td><td>'.$r['R_num_hospedes'].'</td></tr>
    <tr><td><strong>Noites:</strong></td><td>'.$noites.'</td></tr>';

if (!empty($servicos_adicionais)) {
    $html .= '<tr><td><strong>Serviços Adicionais:</strong></td><td>'.implode('<br>', $servicos_adicionais).'</td></tr>';
}

$html .= '
    <tr><td><strong>Total:</strong></td><td>'.$r['R_preco_total'].' €</td></tr>
    <tr><td><strong>Alojamento:</strong></td><td>'.$r['C_nome'].'</td></tr>
    <tr><td><strong>Morada:</strong></td><td>'.($r['C_morada'] ? $r['C_morada'] : PROPERTY_ADDRESS).'</td></tr>
    <tr><td><strong>Telefone:</strong></td><td>'.CONTACT_PHONE.'</td></tr>
</table>

<h2 style="color:'.EMAIL_COLOR.';">Informações Importantes</h2>
<ul>
    <li>Check-in a partir das 15:00 horas.</li>
    <li>Check-out até às 11:00 horas.</li>
    <li>Traga este comprovativo imprimido quando chegar ao nosso alojamento.</li>
    <li>Taxa adicional para check-out atrasado</li>
    <li>Para cancelar a sua reserva, é necessário ligar para o número +351 912 418 976 com 10 dias de antecedência. Caso a anulação seja feita dentro de um prazo inferior, será cobrado 50% do valor da reserva. Pedimos que esteja atento às condições e prazos para evitar custos adicionais.</li>
    <li>Proibido fumar dentro da nossa propriedade.</li>
    <li>Em caso de danos à propriedade durante a sua estadia, solicitamos que informe imediatamente a nossa equipa de recepção para que possamos resolver a situação o mais rápido possível. Dependendo da gravidade do dano, pode haver custos adicionais associados. Pedimos aos nossos hóspedes que cuidem do alojamento com o mesmo zelo com que cuidam da sua própria casa. Se houver problemas durante a estadia, nossa equipa está disponível 24 horas por dia para ajudá-lo a resolver qualquer questão de forma rápida e eficiente.</li>
</ul>

<p style="text-align:center;margin-top:30px;">Obrigado por escolher '.SITE_NAME.'! Esperamos proporcionar-lhe uma estadia memorável e repleta de momentos especiais.</p>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Rodapé personalizado
$pdf->SetY(-30);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetY(-25);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, SITE_NAME, 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, CONTACT_EMAIL . ' | ' . CONTACT_PHONE, 0, 1, 'C');
$pdf->Cell(0, 5, "Emitido em: " . date('d/m/Y H:i'), 0, 1, 'C');

ob_end_clean(); // limpa qualquer saída antes de enviar PDF
$pdf->Output("Reserva_$reserva_id.pdf", 'D');
exit;
?>