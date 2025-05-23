<?php
ob_start();

// Configurações globais
define('SITE_NAME', 'Quinta das Flores');
define('PRIMARY_COLOR', '#4a8f29'); // Verde principal
define('SECONDARY_COLOR', '#3a7320'); // Verde mais escuro
define('EMAIL_COLOR', '#4a8f29');
define('CONTACT_PHONE', '+351 912 418 976');
define('CONTACT_EMAIL', 'quinta.flores2019@gmail.com');
define('PROPERTY_ADDRESS', 'Travessa da Seara 265-Calheiros, Ponte de Lima');

require __DIR__ . '/../../pagamento/tcpdf/tcpdf.php'; // Ajusta o caminho conforme tua estrutura
require __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) die('Reserva não especificada.');
$reserva_id = (int)$_GET['id'];

// Obter dados da reserva
$query = "SELECT r.*, h.H_nome, h.H_email, h.H_telefone, h.H_documento_ident,
                 c.C_nome, c.C_preco_noite, c.C_descricao, c.C_caracteristicas, c.C_morada
          FROM reservas r
          JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
          JOIN casas c ON r.R_id_casa = c.C_id_casa
          WHERE r.R_id_reserva = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $reserva_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) die('Reserva não encontrada.');
$r = $res->fetch_assoc();
$stmt->close();

// Definir morada da casa para usar no PDF
$morada_casa = htmlspecialchars($r['C_morada'] ?? PROPERTY_ADDRESS);

// Cálculos
$checkin = new DateTime($r['R_data_checkin']);
$checkout = new DateTime($r['R_data_checkout']);
$noites = $checkout->diff($checkin)->days;

// Extrair serviços adicionais
$servicos_adicionais = [];
if (!empty($r['R_servicos']) && strtolower($r['R_servicos']) != 'nenhum serviço adicional') {
    $servicos_adicionais = explode(', ', $r['R_servicos']);
}

// Inicializar PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(SITE_NAME);
$pdf->SetAuthor(SITE_NAME);
$pdf->SetTitle('Comprovativo de Reserva #' . $reserva_id);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// --- CABEÇALHO ESTILIZADO ---
$pdf->SetFillColor(74, 143, 41); // Verde
$pdf->Rect(0, 0, 210, 30, 'F'); // Faixa verde

// Logo (confirma o caminho da imagem)
$pdf->Image(__DIR__ . '/../assets/logos/logotipo1.png', 15, 8, 25, 0, 'PNG', '', 'T', false, 300);

// Título
$pdf->SetY(12);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(255, 255, 255); // Branco
$pdf->Cell(0, 10, 'COMPROVANTE DE RESERVA', 0, 1, 'C');

// Número da reserva
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 5, 'Nº ' . $reserva_id, 0, 1, 'C');

// --- CORPO DO DOCUMENTO ---
$pdf->SetTextColor(0, 0, 0); // Preto
$pdf->SetY(40);

// Seção: Dados da Reserva
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(74, 143, 41); // Verde
$pdf->Cell(0, 10, 'DETALHES DA SUA ESTADIA', 0, 1);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('helvetica', '', 11);
$html = <<<HTML
<table cellpadding="4">
    <tr>
        <td width="30%"><strong>Check-in:</strong></td>
        <td width="70%">{$r['R_data_checkin']} (a partir das 15:00)</td>
    </tr>
    <tr>
        <td><strong>Check-out:</strong></td>
        <td>{$r['R_data_checkout']} (até às 11:00)</td>
    </tr>
    <tr>
        <td><strong>Noites:</strong></td>
        <td>$noites</td>
    </tr>
    <tr>
        <td><strong>Hóspedes:</strong></td>
        <td>{$r['R_num_hospedes']}</td>
    </tr>
    <tr>
        <td><strong>Alojamento:</strong></td>
        <td>{$r['C_nome']}</td>
    </tr>
    <tr>
        <td><strong>Localização:</strong></td>
        <td>$morada_casa</td>
    </tr>
</table>
HTML;
$pdf->writeHTML($html, true, false, true, false, '');

// Seção: Descrição do Alojamento
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(74, 143, 41);
$pdf->Cell(0, 10, 'SOBRE O ALOJAMENTO', 0, 1);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, htmlspecialchars($r['C_descricao']), 0, 'L');

// Seção: Hóspede
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(74, 143, 41);
$pdf->Cell(0, 10, 'DADOS DO HÓSPEDE', 0, 1);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('helvetica', '', 11);
$html = <<<HTML
<table cellpadding="4">
    <tr>
        <td width="30%"><strong>Nome:</strong></td>
        <td width="70%">{$r['H_nome']}</td>
    </tr>
    <tr>
        <td><strong>Contacto:</strong></td>
        <td>{$r['H_telefone']}</td>
    </tr>
    <tr>
        <td><strong>E-mail:</strong></td>
        <td>{$r['H_email']}</td>
    </tr>
    <tr>
        <td><strong>Documento:</strong></td>
        <td>{$r['H_documento_ident']}</td>
    </tr>
</table>
HTML;
$pdf->writeHTML($html, true, false, true, false, '');

// Seção: Detalhes Financeiros
$pdf->SetY($pdf->GetY() + 10);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(74, 143, 41);
$pdf->Cell(0, 10, 'DETALHES FINANCEIROS', 0, 1);
$pdf->SetTextColor(0, 0, 0);

// Tabela de valores
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(120, 7, 'Descrição', 0, 0, 'L');
$pdf->Cell(20, 7, 'Qtd', 0, 0, 'C');
$pdf->Cell(25, 7, 'Preço', 0, 0, 'R');
$pdf->Cell(25, 7, 'Total', 0, 1, 'R');

$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

// Item: Alojamento
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(120, 8, 'Alojamento: ' . $r['C_nome'], 0, 0, 'L');
$pdf->Cell(20, 8, $noites, 0, 0, 'C');
$pdf->Cell(25, 8, number_format($r['C_preco_noite'], 2) . ' €', 0, 0, 'R');
$pdf->Cell(25, 8, number_format($noites * $r['C_preco_noite'], 2) . ' €', 0, 1, 'R');

// Serviços adicionais
foreach ($servicos_adicionais as $servico) {
    $pdf->Cell(120, 8, 'Serviço: ' . htmlspecialchars($servico), 0, 0, 'L');
    $pdf->Cell(20, 8, '1', 0, 0, 'C');
    // Aqui pode-se implementar o preço real, para já deixo um exemplo fixo
    $pdf->Cell(25, 8, '20.00 €', 0, 0, 'R');
    $pdf->Cell(25, 8, '20.00 €', 0, 1, 'R');
}

$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

// Totais
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(140, 8, 'Subtotal:', 0, 0, 'R');
$pdf->Cell(25, 8, number_format($r['R_preco_total'], 2) . ' €', 0, 1, 'R');

$pdf->Cell(140, 8, 'Taxas:', 0, 0, 'R');
$pdf->Cell(25, 8, '0.00 €', 0, 1, 'R');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(74, 143, 41);
$pdf->Cell(140, 10, 'Total:', 0, 0, 'R');
$pdf->Cell(25, 10, number_format($r['R_preco_total'], 2) . ' €', 0, 1, 'R');
$pdf->SetTextColor(0, 0, 0);

// Seção: Características do Alojamento
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(74, 143, 41);
$pdf->Cell(0, 10, 'CARACTERÍSTICAS DO ALOJAMENTO', 0, 1);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, htmlspecialchars($r['C_caracteristicas']), 0, 'L');

// Rodapé
$pdf->SetY(-30);
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(74, 143, 41);
$pdf->Cell(0, 5, 'Quinta das Flores - NIF: 213456789 | ' . $morada_casa, 0, 1, 'C');
$pdf->Cell(0, 5, 'Contacto: ' . CONTACT_PHONE . ' | Email: ' . CONTACT_EMAIL, 0, 1, 'C');

$pdf->Output('comprovativo_reserva_' . $reserva_id . '.pdf', 'I');
