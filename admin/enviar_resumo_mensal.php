<?php
require '../conexao.php';
require_once '../login1/PHPMailer/src/PHPMailer.php';
require_once '../login1/PHPMailer/src/SMTP.php';
require_once '../login1/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuração dos e-mails dos administradores
$admin_emails = [
    'admin1@seudominio.com',
    'admin2@seudominio.com'
    // Adicione mais e-mails se necessário
];

// Datas do mês atual
$inicio = date('Y-m-01');
$fim = date('Y-m-t');

// Total de receitas do mês
$stmt = $conexao->prepare("SELECT SUM(R_valor) as total_receitas FROM receitas WHERE R_data BETWEEN ? AND ?");
$stmt->bind_param('ss', $inicio, $fim);
$stmt->execute();
$res = $stmt->get_result();
$total_receitas = $res->fetch_assoc()['total_receitas'] ?? 0;
$stmt->close();

// Total de despesas do mês
$stmt = $conexao->prepare("SELECT SUM(D_valor) as total_despesas FROM despesas WHERE D_data BETWEEN ? AND ?");
$stmt->bind_param('ss', $inicio, $fim);
$stmt->execute();
$res = $stmt->get_result();
$total_despesas = $res->fetch_assoc()['total_despesas'] ?? 0;
$stmt->close();

// Saldo atual
$res = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
$saldo = $res->fetch_assoc()['saldo'] ?? 0;

// Top 5 receitas do mês
$stmt = $conexao->prepare("SELECT R_descricao, R_valor, R_data FROM receitas WHERE R_data BETWEEN ? AND ? ORDER BY R_valor DESC LIMIT 5");
$stmt->bind_param('ss', $inicio, $fim);
$stmt->execute();
$res = $stmt->get_result();
$top_receitas = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Top 5 despesas do mês
$stmt = $conexao->prepare("SELECT D_nome, D_valor, D_data FROM despesas WHERE D_data BETWEEN ? AND ? ORDER BY D_valor DESC LIMIT 5");
$stmt->bind_param('ss', $inicio, $fim);
$stmt->execute();
$res = $stmt->get_result();
$top_despesas = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Monta o corpo do e-mail
$mensagem = "<h2>Resumo Financeiro - ".date('m/Y')."</h2>";
$mensagem .= "<p><strong>Receitas do mês:</strong> €".number_format($total_receitas,2,',','.')."</p>";
$mensagem .= "<p><strong>Despesas do mês:</strong> €".number_format($total_despesas,2,',','.')."</p>";
$mensagem .= "<p><strong>Saldo atual:</strong> €".number_format($saldo,2,',','.')."</p>";

// Top receitas
$mensagem .= "<h3>Top 5 Receitas do mês</h3><ul>";
foreach ($top_receitas as $r) {
    $mensagem .= "<li>".htmlspecialchars($r['R_descricao'])." - €".number_format($r['R_valor'],2,',','.')." em ".date('d/m/Y', strtotime($r['R_data']))."</li>";
}
$mensagem .= "</ul>";
// Top despesas
$mensagem .= "<h3>Top 5 Despesas do mês</h3><ul>";
foreach ($top_despesas as $d) {
    $mensagem .= "<li>".htmlspecialchars($d['D_nome'])." - €".number_format($d['D_valor'],2,',','.')." em ".date('d/m/Y', strtotime($d['D_data']))."</li>";
}
$mensagem .= "</ul>";

// Envia o e-mail para todos os administradores
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.seudominio.com'; // Altere para o seu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'usuario@seudominio.com'; // Altere para o usuário SMTP
    $mail->Password = 'senha'; // Altere para a senha SMTP
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('no-reply@seudominio.com', 'Sistema Financeiro');
    foreach ($admin_emails as $email) {
        $mail->addAddress($email);
    }
    $mail->isHTML(true);
    $mail->Subject = 'Resumo Financeiro Mensal - '.date('m/Y');
    $mail->Body = $mensagem;
    $mail->send();
    echo "Resumo mensal enviado com sucesso para administradores.";
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
} 