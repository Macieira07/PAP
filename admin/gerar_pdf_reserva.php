<?php
// Configurações globais
define('SITE_NAME', 'Quinta Flores');
define('PRIMARY_COLOR', '#4a8f29'); // Verde principal
define('SECONDARY_COLOR', '#3a7320'); // Verde mais escuro
define('ACCENT_COLOR', '#a8c89a'); // Verde claro para acentos
define('LIGHT_GREEN', '#f0f7ed'); // Verde muito claro para backgrounds
define('CONTACT_PHONE', '+351 912 418 976');
define('CONTACT_EMAIL', 'quinta.flores2019@gmail.com');
define('PROPERTY_ADDRESS', 'Travessa da Seara 265-Calheiros, Ponte de Lima');
define('WEBSITE', 'www.quintadasflores.pt'); // Adicione se tiver
define('RNAL', 'AL123456'); // Número de registo de alojamento local

require __DIR__ . '/../pagamento/tcpdf/tcpdf.php';
require __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) die('Reserva não especificada.');
$reserva_id = (int)$_GET['id'];

// Obter dados da reserva
$query = "SELECT r.*, h.H_nome, h.H_email, h.H_telefone, h.H_documento_ident,
                 c.C_nome, c.C_preco_noite, c.C_descricao, c.C_caracteristicas
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
$noites = $checkout->diff($checkin)->days;
$data_emissao = date('d.m.Y');
$hora_emissao = date('H:i');

// Status da reserva
$status_color = '#28a745'; // Verde para confirmado
$status_text = 'CONFIRMADA';

// Extrair serviços adicionais
$servicos_lista = '';
$tem_servicos = false;
if (!empty($r['R_servicos']) && strtolower($r['R_servicos']) != 'nenhum serviço adicional') {
    $servicos_array = explode(', ', $r['R_servicos']);
    $servicos_lista = implode('<br>', array_map(function($s) { 
        return '<span style="color:#555; font-size: 9px;">✓ ' . trim($s) . '</span>'; 
    }, $servicos_array));
    $tem_servicos = true;
}

// Configurar PDF
class MYPDF extends TCPDF {
    public function Header() {
        // Marca d'água elegante
        $this->SetAlpha(0.1);
        $this->SetFont('helvetica', 'B', 60);
        $this->SetTextColor(74, 143, 41);
        $this->StartTransform();
        $this->Rotate(45, 105, 150);
        $this->Text(20, 150, 'QUINTA FLORES');
        $this->StopTransform();
        $this->SetAlpha(1);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 7);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(SITE_NAME);
$pdf->SetAuthor(SITE_NAME);
$pdf->SetTitle('Comprovativo de Reserva #' . $reserva_id);
$pdf->SetSubject('Reserva de Alojamento Local');
$pdf->SetKeywords('reserva, alojamento, turismo rural, quinta das flores');
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

// Função para converter cor hex para RGB
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

$primary_rgb = hexToRgb(PRIMARY_COLOR);
$secondary_rgb = hexToRgb(SECONDARY_COLOR);
$accent_rgb = hexToRgb(ACCENT_COLOR);
$light_green_rgb = hexToRgb(LIGHT_GREEN);

// === CABEÇALHO PRINCIPAL ===
// Caixa verde de fundo para o cabeçalho
$pdf->SetFillColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->Rect(0, 0, 210, 35, 'F');

// Logo/Nome do estabelecimento
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetXY(15, 8);
$pdf->Cell(120, 10, SITE_NAME, 0, 1, 'L');

// Subtítulo
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(15, 18);
$pdf->Cell(120, 6, 'Alojamento Local • Turismo Rural • Ponte de Lima', 0, 1, 'L');

// Informação de registo
$pdf->SetFont('helvetica', '', 8);
$pdf->SetXY(15, 25);
$pdf->Cell(120, 4, 'RNAL: ' . RNAL . ' • Licenciado pelo Turismo de Portugal', 0, 1, 'L');

// Status da reserva (canto superior direito)
$pdf->SetFillColor(40, 167, 69);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetXY(155, 8);
$pdf->Cell(40, 8, $status_text, 0, 1, 'C', true);

// Número da reserva
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetXY(155, 18);
$pdf->Cell(40, 6, 'RESERVA #' . str_pad($reserva_id, 5, '0', STR_PAD_LEFT), 0, 1, 'C');

// Data de emissão
$pdf->SetFont('helvetica', '', 8);
$pdf->SetXY(155, 25);
$pdf->Cell(40, 4, 'Emitido: ' . $data_emissao . ' ' . $hora_emissao, 0, 1, 'C');

// === TÍTULO PRINCIPAL ===
$pdf->SetY(45);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->Cell(0, 12, 'Comprovativo de Reserva', 0, 1, 'C');

// Linha decorativa dupla
$pdf->SetDrawColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->SetLineWidth(1);
$pdf->Line(50, $pdf->GetY() + 2, 160, $pdf->GetY() + 2);
$pdf->SetDrawColor($accent_rgb[0], $accent_rgb[1], $accent_rgb[2]);
$pdf->SetLineWidth(0.5);
$pdf->Line(50, $pdf->GetY() + 4, 160, $pdf->GetY() + 4);

// === DETALHES DA RESERVA ===
$pdf->SetY(70);

// Caixa de destaque para a casa e preço
$pdf->SetFillColor($light_green_rgb[0], $light_green_rgb[1], $light_green_rgb[2]);
$pdf->Rect(15, $pdf->GetY(), 180, 25, 'F');

// Nome da casa
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor($secondary_rgb[0], $secondary_rgb[1], $secondary_rgb[2]);
$pdf->SetXY(20, $pdf->GetY() + 5);
$pdf->Cell(120, 8, $r['C_nome'], 0, 1, 'L');

// Período
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(20, $pdf->GetY() + 2);
$pdf->Cell(120, 6, $checkin->format('d/m/Y') . ' até ' . $checkout->format('d/m/Y') . ' (' . $noites . ' noite' . ($noites > 1 ? 's' : '') . ')', 0, 1, 'L');

// Preço total destacado
$pdf->SetXY(140, 75);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor($secondary_rgb[0], $secondary_rgb[1], $secondary_rgb[2]);
$pdf->Cell(50, 10, '€' . number_format($r['R_preco_total'], 2, ',', '.'), 0, 1, 'R');

$pdf->SetXY(140, 85);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(50, 5, 'Valor Total', 0, 1, 'R');

// === RESUMO DETALHADO ===
$pdf->SetY(105);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->Cell(0, 8, 'Resumo da Reserva', 0, 1, 'L');

$resumo_html = '
<style>
    .resumo-table { 
        border-collapse: collapse; 
        width: 100%; 
        margin: 10px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .resumo-header { 
        background-color: ' . LIGHT_GREEN . '; 
        color: ' . SECONDARY_COLOR . '; 
        font-weight: bold; 
        padding: 8px;
        border-bottom: 2px solid ' . PRIMARY_COLOR . ';
    }
    .resumo-row { 
        padding: 6px 8px; 
        border-bottom: 1px solid #e9ecef; 
    }
    .resumo-label { 
        color: #495057; 
        font-weight: 600; 
        width: 50%;
    }
    .resumo-value { 
        color: #212529; 
        text-align: right; 
        width: 50%;
    }
    .total-row { 
        background-color: ' . LIGHT_GREEN . '; 
        font-weight: bold; 
        border-top: 2px solid ' . PRIMARY_COLOR . ';
    }
    .icon { color: ' . PRIMARY_COLOR . '; }
</style>

<table class="resumo-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="resumo-header">Detalhes</td>
        <td class="resumo-header" style="text-align: right;">Informação</td>
    </tr>
    <tr>
        <td class="resumo-row resumo-label"><span class="icon">🏠</span> Alojamento</td>
        <td class="resumo-row resumo-value">' . htmlspecialchars($r['C_nome']) . '</td>
    </tr>
    <tr>
        <td class="resumo-row resumo-label"><span class="icon">📅</span> Check-in</td>
        <td class="resumo-row resumo-value">' . $checkin->format('d/m/Y (D)') . ' às 15:00h</td>
    </tr>
    <tr>
        <td class="resumo-row resumo-label"><span class="icon">📅</span> Check-out</td>
        <td class="resumo-row resumo-value">' . $checkout->format('d/m/Y (D)') . ' até 11:00h</td>
    </tr>
    <tr>
        <td class="resumo-row resumo-label"><span class="icon">🌙</span> Número de Noites</td>
        <td class="resumo-row resumo-value">' . $noites . ' noite' . ($noites > 1 ? 's' : '') . '</td>
    </tr>
    <tr>
        <td class="resumo-row resumo-label"><span class="icon">👥</span> Número de Hóspedes</td>
        <td class="resumo-row resumo-value">' . $r['R_num_hospedes'] . ' pessoa' . ($r['R_num_hospedes'] > 1 ? 's' : '') . '</td>
    </tr>
    <tr>
        <td class="resumo-row resumo-label"><span class="icon">💰</span> Preço por Noite</td>
        <td class="resumo-row resumo-value">€' . number_format($r['C_preco_noite'], 2, ',', '.') . '</td>
    </tr>';

if ($tem_servicos) {
    $resumo_html .= '
    <tr>
        <td class="resumo-row resumo-label"><span class="icon">✨</span> Serviços Extras</td>
        <td class="resumo-row resumo-value" style="font-size: 9px;">' . $servicos_lista . '</td>
    </tr>';
}

$resumo_html .= '
    <tr class="total-row">
        <td class="resumo-row" style="color: ' . SECONDARY_COLOR . '; font-size: 14px;"><span class="icon">💳</span> TOTAL A PAGAR</td>
        <td class="resumo-row" style="text-align: right; color: ' . SECONDARY_COLOR . '; font-size: 14px;">€' . number_format($r['R_preco_total'], 2, ',', '.') . '</td>
    </tr>
</table>';

$pdf->writeHTML($resumo_html, true, false, true, false, '');

// === DADOS DO HÓSPEDE ===
$pdf->SetY($pdf->GetY() + 10);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->Cell(0, 8, 'Dados do Hóspede Principal', 0, 1, 'L');

$hospede_html = '
<style>
    .hospede-card {
        background-color: #ffffff;
        border: 1px solid ' . ACCENT_COLOR . ';
        border-radius: 8px;
        padding: 12px;
        margin: 8px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .hospede-row {
        margin: 6px 0;
        padding: 4px 0;
    }
    .hospede-label { 
        color: ' . SECONDARY_COLOR . '; 
        font-weight: bold; 
        font-size: 10px;
        display: inline-block;
        width: 25%;
    }
    .hospede-value { 
        color: #212529; 
        font-size: 10px;
        display: inline-block;
        width: 70%;
    }
</style>

<div class="hospede-card">
    <div class="hospede-row">
        <span class="hospede-label">👤 Nome Completo:</span>
        <span class="hospede-value">' . htmlspecialchars($r['H_nome']) . '</span>
    </div>
    <div class="hospede-row">
        <span class="hospede-label">📧 Email:</span>
        <span class="hospede-value">' . htmlspecialchars($r['H_email']) . '</span>
    </div>
    <div class="hospede-row">
        <span class="hospede-label">📱 Telefone:</span>
        <span class="hospede-value">' . htmlspecialchars($r['H_telefone']) . '</span>
    </div>
    <div class="hospede-row">
        <span class="hospede-label">🆔 Documento:</span>
        <span class="hospede-value">' . htmlspecialchars($r['H_documento_ident']) . '</span>
    </div>
</div>';

$pdf->writeHTML($hospede_html, true, false, true, false, '');

// === TERMOS E CONDIÇÕES ===
$pdf->SetY($pdf->GetY() + 8);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->Cell(0, 8, 'Termos e Condições', 0, 1, 'L');

$termos_html = '
<style>
    .termos-box {
        background: linear-gradient(135deg, ' . LIGHT_GREEN . ' 0%, #ffffff 100%);
        border-left: 4px solid ' . PRIMARY_COLOR . ';
        padding: 12px;
        margin: 8px 0;
        border-radius: 0 8px 8px 0;
    }
    .termo-item {
        margin: 8px 0;
        padding: 4px 0;
        font-size: 9px;
        line-height: 1.4;
        color: #495057;
    }
    .termo-title {
        color: ' . SECONDARY_COLOR . ';
        font-weight: bold;
        font-size: 10px;
    }
    .important-note {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        padding: 8px;
        margin: 8px 0;
        border-radius: 4px;
        font-size: 9px;
        color: #856404;
    }
</style>

<div class="termos-box">
    <div class="termo-item">
        <span class="termo-title">🕒 Check-in e Check-out:</span><br>
        • Check-in: A partir das 15:00h (confirmar chegada com 24h de antecedência)<br>
        • Check-out: Até às 11:00h (possível extensão mediante disponibilidade)
    </div>
    
    <div class="termo-item">
        <span class="termo-title">📋 Documentação Necessária:</span><br>
        • Apresentar este comprovativo impresso ou digital<br>
        • Documento de identificação válido de todos os hóspedes<br>
        • Cartão de cidadão ou passaporte (obrigatório por lei)
    </div>
    <div class="termo-item">
        <span class="termo-title">❌ Política de Cancelamento:</span><br>
        • Cancelamento gratuito até 10 dias antes da chegada<br>
        • Cancelamento entre 10-3 dias: retenção de 50% do valor<br>
        • Cancelamento com menos de 3 dias: retenção de 100%
    </div>
    <div class="termo-item">
        <span class="termo-title">💳 Pagamento:</span><br>
        • Sinal de 30% pago na reserva<br>
        • Restante valor a liquidar no check-in<br>
        • Aceitos: Dinheiro, MB Way, Cartão de Débito/Crédito
    </div>
    <div class="termo-item">
        <span class="termo-title">🏠 Regras da Casa:</span><br>
        • Não fumadores • Animais mediante consulta prévia<br>
        • Silêncio após as 22h • Capacidade máxima respeitada
    </div>
</div>
<div class="important-note">
    <strong>⚠️ IMPORTANTE:</strong> Em caso de não comparência (no-show) sem aviso prévio, 
    o valor total da reserva será retido. Para alterações contacte-nos com pelo menos 48h de antecedência.
</div>';
$pdf->writeHTML($termos_html, true, false, true, false, '');
// === CONTACTOS E LOCALIZAÇÃO ===
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->Cell(0, 8, 'Contactos e Como Chegar', 0, 1, 'L');
$contactos_html = '
<style>
    .contactos-grid {
        display: table;
        width: 100%;
        margin: 8px 0;
    }
    .contacto-col {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding: 8px;
    }
    .contacto-box {
        background-color: ' . LIGHT_GREEN . ';
        border-radius: 6px;
        padding: 10px;
        margin: 4px;
        border-left: 3px solid ' . PRIMARY_COLOR . ';
    }
    .contacto-title {
        color: ' . SECONDARY_COLOR . ';
        font-weight: bold;
        font-size: 10px;
        margin-bottom: 6px;
    }
    .contacto-info {
        font-size: 9px;
        line-height: 1.3;
        color: #495057;
    }
</style>
<div class="contactos-grid">
    <div class="contacto-col">
        <div class="contacto-box">
            <div class="contacto-title">📞 Contactos Diretos</div>
            <div class="contacto-info">
                <strong>Telefone:</strong> ' . CONTACT_PHONE . '<br>
                <strong>Email:</strong> ' . CONTACT_EMAIL . '<br>
                <strong>Horário:</strong> 8h-22h (todos os dias)
            </div>
        </div>
    </div>
    <div class="contacto-col">
        <div class="contacto-box">
            <div class="contacto-title">📍 Localização</div>
            <div class="contacto-info">
                ' . PROPERTY_ADDRESS . '<br>
                <strong>GPS:</strong> 41.7677, -8.5834<br>
                <strong>Distância:</strong> Centro Ponte de Lima (2km)
            </div>
        </div>
    </div>
</div>';
$pdf->writeHTML($contactos_html, true, false, true, false, '');
// === RODAPÉ PROFISSIONAL ===
$pdf->SetY(-35);
// Linha decorativa
$pdf->SetDrawColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->SetY(-30);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor($primary_rgb[0], $primary_rgb[1], $primary_rgb[2]);
$pdf->Cell(0, 5, 'Obrigado por escolher a Quinta das Flores!', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 4, 'Documento gerado automaticamente em ' . date('d/m/Y \à\s H:i') . ' • Válido sem assinatura', 0, 1, 'C');
$pdf->Cell(0, 4, CONTACT_PHONE . ' • ' . CONTACT_EMAIL . ' • ' . PROPERTY_ADDRESS, 0, 1, 'C');
// Output do PDF
$filename = 'Comprovativo_Reserva_' . str_pad($reserva_id, 5, '0', STR_PAD_LEFT) . '_' . date('Ymd') . '.pdf';
$pdf->Output($filename, 'I');
?>