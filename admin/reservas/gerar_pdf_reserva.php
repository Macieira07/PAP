<?php
// Configurações básicas
define('SITE_NAME', 'Quinta Flores');
define('PRIMARY_COLOR', '#4a8f29');
define('EMAIL_COLOR', '#4a8f29');
define('CONTACT_PHONE', '+351 912 418 976');
define('CONTACT_EMAIL', 'quinta.flores2019@gmail.com');
define('PROPERTY_ADDRESS', 'Travessa da Seara 265-Calheiros, Ponte de Lima');
define('RNAL', 'AL123456'); // Número de registo de alojamento local

require '../../pagamento/tcpdf/tcpdf.php';
require '../../conexao.php';

if (!isset($_GET['id'])) die('Reserva não especificada.');
$reserva_id = (int)$_GET['id'];

// Obter dados da reserva
$query = "SELECT r.*, h.H_nome, h.H_email, h.H_telefone, c.C_nome, c.C_preco_noite
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

// Cálculos
$checkin = new DateTime($r['R_data_checkin']);
$checkout = new DateTime($r['R_data_checkout']);
$diferenca = $checkin->diff($checkout);
$num_noites = $diferenca->days;
$preco_por_noite = $r['C_preco_noite'];
$preco_total = $r['R_preco_total'];
$metodo_pagamento = $r['R_metodo_pagamento'];

// Verificar se é uma oferta especial
$oferta_info = '';
$codigos_oferta = [
    'LOVE260' => 'Pacote Amor (2 noites - €260)',
    'PARTY260' => 'Pacote Festa com Amigos (2 noites - €260)',
    'RETIRO240' => 'Pacote Retiro na Catequese (2 noites - €240)'
];

// Extrair código de oferta dos serviços se existir
if (!empty($r['R_servicos'])) {
    foreach ($codigos_oferta as $codigo => $descricao) {
        if (strpos($r['R_servicos'], $codigo) !== false) {
            $oferta_info = $descricao;
            break;
        }
    }
}

// Extrair serviços adicionais
$servicos_adicionais = [];
if (!empty($r['R_servicos']) && strtolower($r['R_servicos']) != 'nenhum serviço adicional') {
    $servicos = explode(', ', $r['R_servicos']);
    foreach ($servicos as $servico) {
        // Ignorar se for um código de oferta
        $eh_oferta = false;
        foreach ($codigos_oferta as $codigo => $descricao) {
            if (strpos($servico, $codigo) !== false) {
                $eh_oferta = true;
                break;
            }
        }
        if (!$eh_oferta) {
            $servicos_adicionais[] = trim($servico);
        }
    }
}

// Configurar PDF
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
$pdf->Cell(0, 5, $r['H_nome'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Contacto: ' . ($r['H_telefone'] ? $r['H_telefone'] : 'Não informado'), 0, 1, 'L');
$pdf->Cell(0, 5, 'Email: ' . $r['H_email'], 0, 1, 'L');
$pdf->Ln(8);

// Detalhes da reserva - Lista no lado esquerdo
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'Detalhes da Estadia:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Lista de detalhes
$detalhes = [
    'Alojamento' => 'Quinta Flores',
    'Check-in' => $checkin->format('d/m/Y') . ' (a partir das 15:00)',
    'Check-out' => $checkout->format('d/m/Y') . ' (até às 11:00)',
    'Nº de Noites' => $num_noites,
    'Nº de Hóspedes' => $r['R_num_hospedes'],
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

// Output do PDF
$filename = 'Fatura_Reserva_' . str_pad($reserva_id, 5, '0', STR_PAD_LEFT) . '.pdf';
$pdf->Output($filename, 'I');
?>