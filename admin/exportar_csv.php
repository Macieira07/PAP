<?php
require '../conexao.php';

if (isset($_GET['exportar'])) {
    $reservas = $conexao->query("SELECT r.*, h.H_nome, c.C_nome FROM reservas r JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede JOIN casas c ON r.R_id_casa = c.C_id_casa");

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reservas.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Hóspede', 'Casa', 'Check-in', 'Check-out', 'Nº Hóspedes', 'Preço Total', 'Estado', 'Método de Pagamento']);
    
    while ($r = $reservas->fetch_assoc()) {
        fputcsv($output, [$r['R_id_reserva'], $r['H_nome'], $r['C_nome'], $r['R_data_checkin'], $r['R_data_checkout'], $r['R_num_hospedes'], $r['R_preco_total'], $r['R_estado'], $r['R_metodo_pagamento']]);
    }

    fclose($output);
    exit;
}
?>
