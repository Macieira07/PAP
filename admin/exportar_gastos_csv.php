<?php
require('./lib/fpdf186/fpdf.php');
require '../conexao.php';

// Consulta os gastos
$gastos_detalhados = $conexao->query("SELECT * FROM manutencao");

$pdf = new FPDF();
$pdf->AddPage();

// Adiciona o logótipo
$pdf->Image('../assets/logos/logotipo1.png', 10, 6, 30); // Ajuste o caminho do logo

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(200, 10, 'Relatório de Gastos', 0, 1, 'C');
$pdf->Ln(10);

// Cabeçalhos da tabela
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Tipo', 1);
$pdf->Cell(60, 10, 'Valor', 1);
$pdf->Cell(60, 10, 'Estado', 1);
$pdf->Ln();

// Dados dos gastos
$pdf->SetFont('Arial', '', 12);
$total_gastos = 0;
while ($gasto = $gastos_detalhados->fetch_assoc()) {
    $pdf->Cell(60, 10, $gasto['M_tipo'], 1);
    $pdf->Cell(60, 10, '€' . number_format($gasto['M_custo'], 2, ',', '.'), 1);
    $pdf->Cell(60, 10, $gasto['M_pago'] ? 'Pago' : 'Por pagar', 1);
    $pdf->Ln();

    // Somar os gastos totais
    if ($gasto['M_pago']) {
        $total_gastos += $gasto['M_custo'];
    }
}

// Adiciona o total de gastos no final
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(180, 10, 'Total de Gastos: €' . number_format($total_gastos, 2, ',', '.'), 0, 1, 'R');

// Exibe o PDF
$pdf->Output('D', 'relatorio_gastos.pdf'); // O 'D' força o download do arquivo PDF
exit;
?>
