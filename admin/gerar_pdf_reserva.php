<?php
// Ajuste dos caminhos
require __DIR__ . '/lib/fpdf186/fpdf.php';
require __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) {
    die('Reserva não especificada.');
}
$reserva_id = (int)$_GET['id'];

// Mesma query de busca usada em reserva_sucesso.php
$query = "
  SELECT r.R_id_reserva, r.R_data_checkin, r.R_data_checkout, r.R_num_hospedes,
         r.R_preco_total, r.R_metodo_pagamento,
         h.H_nome, h.H_apelido, h.H_email, h.H_telefone,
         c.C_nome
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

// Gerar PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,"Recibo de Reserva #{$reserva_id}",0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(50,8,'Hóspede:',0,0);
$pdf->Cell(0,8,$r['H_nome'].' '.$r['H_apelido'],0,1);

$pdf->Cell(50,8,'Email:',0,0);
$pdf->Cell(0,8,$r['H_email'],0,1);

$pdf->Cell(50,8,'Telefone:',0,0);
$pdf->Cell(0,8,$r['H_telefone'],0,1);

$pdf->Ln(5);
$pdf->Cell(50,8,'Casa:',0,0);
$pdf->Cell(0,8,$r['C_nome'],0,1);

$pdf->Cell(50,8,'Check-in:',0,0);
$pdf->Cell(0,8,$r['R_data_checkin'],0,1);

$pdf->Cell(50,8,'Check-out:',0,0);
$pdf->Cell(0,8,$r['R_data_checkout'],0,1);

$noites = (new DateTime($r['R_data_checkout']))->diff(new DateTime($r['R_data_checkin']))->days;
$pdf->Cell(50,8,'Noites:',0,0);
$pdf->Cell(0,8,$noites,0,1);

$pdf->Cell(50,8,'Nº Hóspedes:',0,0);
$pdf->Cell(0,8,$r['R_num_hospedes'],0,1);

$pdf->Cell(50,8,'Método Pagamento:',0,0);
$pdf->Cell(0,8,ucfirst($r['R_metodo_pagamento']),0,1);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(50,10,'Preço Total:',0,0);
$pdf->Cell(0,10,number_format($r['R_preco_total'],2).'€',0,1);

header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=Reserva_{$reserva_id}.pdf");
$pdf->Output('D',"Reserva_{$reserva_id}.pdf");
exit;
