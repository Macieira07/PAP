<?php
require '../conexao.php';

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se os dados necessários foram enviados
    if (isset($_POST['id_reserva']) && isset($_POST['estado'])) {
        $id_reserva = $_POST['id_reserva'];
        $estado = $_POST['estado'];

        // Preparar e executar a atualização do estado da reserva
        $stmt = $conexao->prepare("UPDATE reservas SET R_estado = ? WHERE R_id_reserva = ?");
        $stmt->bind_param("si", $estado, $id_reserva);

        // Verificar se a atualização foi bem-sucedida
        if ($stmt->execute()) {
            // Mensagem de sucesso
            $sucesso = "Estado da reserva alterado com sucesso!";
            header("Location: reservas.php?sucesso=" . urlencode($sucesso));
            exit; // Garantir que o script pare após o redirecionamento
        } else {
            // Caso ocorra um erro na execução
            $erro = "Erro ao alterar estado da reserva.";
            header("Location: reservas.php?erro=" . urlencode($erro));
            exit; // Garantir que o script pare após o redirecionamento
        }
    } else {
        // Caso os dados não tenham sido enviados corretamente
        header("Location: reservas.php?erro=Dados inválidos.");
        exit; // Garantir que o script pare após o redirecionamento
    }
}
?>

<!-- Formulário de alteração de estado -->
<form method="POST" action="alterar_estado.php">
    <!-- Campo oculto com o ID da reserva -->
    <input type="hidden" name="id_reserva" value="<?= $reserva['R_id_reserva'] ?>">

    <!-- Campo para selecionar o estado da reserva -->
    <select name="estado">
        <option value="pendente" <?= $reserva['R_estado'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
        <option value="confirmada" <?= $reserva['R_estado'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
        <option value="cancelada" <?= $reserva['R_estado'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
        <option value="concluída" <?= $reserva['R_estado'] == 'concluída' ? 'selected' : '' ?>>Concluída</option>
    </select>

    <button type="submit">Alterar</button>
</form>
