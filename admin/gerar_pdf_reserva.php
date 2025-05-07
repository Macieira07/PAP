<?php
require __DIR__ . '/lib/fpdf186/fpdf.php';
require __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) {
    die('Reserva não especificada.');
}
$reserva_id = (int)$_GET['id'];

// Obter dados da reserva
$query = "
  SELECT r.R_id_reserva, r.R_data_checkin, r.R_data_checkout, r.R_num_hospedes,
         r.R_preco_total, r.R_metodo_pagamento, r.R_nif,
         h.H_nome, h.H_apelido, h.H_email, h.H_telefone,
         c.C_nome, c.C_morada
  FROM reservas r
  JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
  JOIN casas c    ON r.R_id_casa = c.C_id_casa
  WHERE r.R_id_reserva = ?
";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $reserva_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die('Reserva não encontrada.');
}
$r = $res->fetch_assoc();
$stmt->close();
$conexao->close();

// Formatar datas em português
setlocale(LC_TIME, 'pt_PT.utf8');
$data_checkin = strftime('%A, %d de %B de %Y', strtotime($r['R_data_checkin']));
$data_checkout = strftime('%A, %d de %B de %Y', strtotime($r['R_data_checkout']));
$noites = (new DateTime($r['R_data_checkout']))->diff(new DateTime($r['R_data_checkin']))->days;

// Criar PDF
$pdf = new FPDF();
$pdf->AddPage();

// Cores
$corPrincipal = [74, 128, 91]; // #4a805b
$corTexto = [51, 51, 51];      // #333

// Logótipo
$logoPath = __DIR__ . '/logo.png'; // ajusta o caminho se necessário
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 10, 40);
}
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(...$corPrincipal);
$pdf->Cell(0, 10, utf8_decode("Recibo de Reserva #{$reserva_id}"), 0, 1, 'C');
$pdf->Ln(15);

// Corpo
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(...$corTexto);

$pdf->Cell(50, 8, 'Hóspede:', 0, 0);
$pdf->Cell(0, 8, $r['H_nome'] . ' ' . $r['H_apelido'], 0, 1);

$pdf->Cell(50, 8, 'Email:', 0, 0);
$pdf->Cell(0, 8, $r['H_email'], 0, 1);

$pdf->Cell(50, 8, 'Telefone:', 0, 0);
$pdf->Cell(0, 8, $r['H_telefone'], 0, 1);

$pdf->Cell(50, 8, 'NIF:', 0, 0);
$pdf->Cell(0, 8, $r['R_nif'], 0, 1);

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Detalhes da Reserva', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'Casa:', 0, 0);
$pdf->Cell(0, 8, $r['C_nome'], 0, 1);

$pdf->Cell(50, 8, 'Morada:', 0, 0);
$pdf->MultiCell(0, 8, $r['C_morada']);

$pdf->Cell(50, 8, 'Check-in:', 0, 0);
$pdf->Cell(0, 8, $data_checkin, 0, 1);

$pdf->Cell(50, 8, 'Check-out:', 0, 0);
$pdf->Cell(0, 8, $data_checkout, 0, 1);

$pdf->Cell(50, 8, 'Noites:', 0, 0);
$pdf->Cell(0, 8, $noites, 0, 1);

$pdf->Cell(50, 8, 'Nº Hóspedes:', 0, 0);
$pdf->Cell(0, 8, $r['R_num_hospedes'], 0, 1);

$pdf->Cell(50, 8, 'Método Pagamento:', 0, 0);
$pdf->Cell(0, 8, ucfirst($r['R_metodo_pagamento']), 0, 1);

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(...$corPrincipal);
$pdf->Cell(50, 10, 'Preço Total:', 0, 0);
$pdf->Cell(0, 10, number_format($r['R_preco_total'], 2) . ' €', 0, 1);

// Rodapé com sombra leve
$pdf->SetY(-40);
$pdf->SetDrawColor(224, 224, 224);
$pdf->SetLineWidth(0.1);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(119, 119, 119);
$pdf->MultiCell(0, 5, "Alojamento Local Quinta Flores\nTravessa da Seara 265, Calheiros, Ponte de Lima\n4990-575", 0, 'C');

// Guardar o ficheiro na pasta recibos/
$caminho = __DIR__ . "/recibos/Reserva_{$reserva_id}.pdf";
$pdf->Output('F', $caminho);

// Opcional: Forçar download imediato
header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=Reserva_{$reserva_id}.pdf");
readfile($caminho);
exit;
