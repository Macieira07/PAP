<?php
require '../conexao.php';

$id_reserva = $_GET['id'];
$reserva = $conexao->query("SELECT * FROM reservas WHERE R_id_reserva = $id_reserva")->fetch_assoc();
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome FROM hospedes");
$casas = $conexao->query("SELECT C_id_casa, C_nome FROM casas");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_hospede = $_POST['id_hospede'];
    $id_casa = $_POST['id_casa'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $num_hospedes = $_POST['num_hospedes'];
    $preco = $_POST['preco_total'];
    $estado = $_POST['estado'];
    $metodo_pagamento = $_POST['metodo_pagamento'];
    $observacoes = $_POST['observacoes'];
    $servicos = $_POST['servicos'];
    $dados_pagamento = $_POST['dados_pagamento'];

    $stmt = $conexao->prepare("
        UPDATE reservas SET 
            R_id_hospede = ?, 
            R_id_casa = ?, 
            R_data_checkin = ?, 
            R_data_checkout = ?, 
            R_num_hospedes = ?, 
            R_preco_total = ?, 
            R_estado = ?, 
            R_metodo_pagamento = ?, 
            R_observacoes = ?, 
            R_servicos = ?, 
            R_dados_pagamento = ?
        WHERE R_id_reserva = ?
    ");
    $stmt->bind_param("iissidsssssi", $id_hospede, $id_casa, $checkin, $checkout, $num_hospedes, $preco, $estado, $metodo_pagamento, $observacoes, $servicos, $dados_pagamento, $id_reserva);
    $stmt->execute();

    header("Location: reservas.php");
    exit;
}
?>
<link rel="stylesheet" href="admin.css">
<h2>Editar Reserva</h2>
<link rel="stylesheet" href="admin.css">
<form method="post">
    Hóspede:
    <select name="id_hospede" required>
        <?php while ($h = $hospedes->fetch_assoc()): ?>
            <option value="<?= $h['H_id_hospede'] ?>" <?= $h['H_id_hospede'] == $reserva['R_id_hospede'] ? 'selected' : '' ?>><?= $h['H_nome'] ?></option>
        <?php endwhile; ?>
    </select><br><br>

    Casa:
    <select name="id_casa" required>
        <?php while ($c = $casas->fetch_assoc()): ?>
            <option value="<?= $c['C_id_casa'] ?>" <?= $c['C_id_casa'] == $reserva['R_id_casa'] ? 'selected' : '' ?>><?= $c['C_nome'] ?></option>
        <?php endwhile; ?>
    </select><br><br>

    Check-in: <input type="date" name="checkin" value="<?= $reserva['R_data_checkin'] ?>"><br><br>
    Check-out: <input type="date" name="checkout" value="<?= $reserva['R_data_checkout'] ?>"><br><br>
    Nº Hóspedes: <input type="number" name="num_hospedes" value="<?= $reserva['R_num_hospedes'] ?>"><br><br>
    Preço Total: <input type="number" step="0.01" name="preco_total" value="<?= $reserva['R_preco_total'] ?>"><br><br>
    
    Estado:
    <select name="estado">
        <option value="pendente" <?= $reserva['R_estado'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
        <option value="confirmada" <?= $reserva['R_estado'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
        <option value="cancelada" <?= $reserva['R_estado'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
        <option value="concluída" <?= $reserva['R_estado'] == 'concluída' ? 'selected' : '' ?>>Concluída</option>
    </select><br><br>
    
    Método de Pagamento: 
    <input type="text" name="metodo_pagamento" value="<?= $reserva['R_metodo_pagamento'] ?>"><br><br>
    
    Observações:
    <textarea name="observacoes"><?= $reserva['R_observacoes'] ?></textarea><br><br>
    
    Serviços Adicionais:
    <input type="text" name="servicos" value="<?= $reserva['R_servicos'] ?>"><br><br>
    
    Dados de Pagamento:
    <input type="text" name="dados_pagamento" value="<?= $reserva['R_dados_pagamento'] ?>"><br><br>
    
    <button type="submit">Salvar Alterações</button>
</form>
