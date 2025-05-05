<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_hospede = $_POST['id_hospede'];
    $id_casa = $_POST['id_casa'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $num_hospedes = $_POST['num_hospedes'];
    $preco_total = $_POST['preco_total'];
    $metodo_pagamento = $_POST['metodo_pagamento'];
    $observacoes = $_POST['observacoes'];

    // Preparar a consulta de inserção
    $stmt = $conexao->prepare("INSERT INTO reservas (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, R_num_hospedes, R_preco_total, R_metodo_pagamento, R_observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissdsss", $id_hospede, $id_casa, $checkin, $checkout, $num_hospedes, $preco_total, $metodo_pagamento, $observacoes);

    if ($stmt->execute()) {
        header("Location: reservas.php?sucesso=Reserva adicionada com sucesso.");
        exit;
    } else {
        $erro = "Erro ao adicionar reserva.";
    }
}
?>
<link rel="stylesheet" href="admin.css">
<h1>Adicionar Nova Reserva</h1>

<form method="POST" action="adicionar_reserva.php">
    Hóspede: <input type="text" name="id_hospede" required><br>
    Casa: <input type="text" name="id_casa" required><br>
    Check-in: <input type="date" name="checkin" required><br>
    Check-out: <input type="date" name="checkout" required><br>
    Nº Hóspedes: <input type="number" name="num_hospedes" required><br>
    Preço Total: <input type="number" name="preco_total" required><br>
    Método de Pagamento: <input type="text" name="metodo_pagamento" required><br>
    Observações: <textarea name="observacoes"></textarea><br>
    <button type="submit">Adicionar Reserva</button>
</form>
