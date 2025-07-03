<?php
require '../../conexao.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Segurança: força inteiro

    // Obter valor da manutenção
    $result = $conexao->query("SELECT M_custo FROM manutencao WHERE M_id_manutencao = $id");
    $manutencao = $result->fetch_assoc();

    if ($manutencao) {
        $valor = $manutencao['M_custo'];

        // Obter saldo atual
        $saldo_result = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
        $saldo_assoc = $saldo_result ? $saldo_result->fetch_assoc() : null;
        $saldo = $saldo_assoc ? $saldo_assoc['saldo'] : null;

        if ($saldo === null) {
            header('Location: despesas.php?erro=Conta virtual não encontrada');
            exit;
        }

        if ($saldo >= $valor) {
            // Atualizar saldo
            $conexao->query("UPDATE conta_virtual SET saldo = saldo - $valor WHERE id = 1");

            // Registrar movimentação
            $descricao = "Pagamento de manutenção #$id";
            $conexao->query("INSERT INTO movimentacoes (tipo, descricao, valor, origem, origem_id)
                             VALUES ('despesa', '$descricao', $valor, 'manutencao', $id)");

            // Registrar receita
            $conexao->query("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_origem, R_origem_id)
                             VALUES ('Receita de manutenção #$id', $valor, NOW(), 'Manutenção', 'manutencao', $id)");

            // Marcar como pago
            $conexao->query("UPDATE manutencao SET M_pago = 1 WHERE M_id_manutencao = $id");

            header('Location:../despesas/despesas.php?msg=Manutenção paga com sucesso');
        } else {
            header('Location:../despesas/despesas.php?erro=Saldo insuficiente para pagar a manutenção');
        }
    } else {
        // ID não encontrado
        header('Location: ../despesas/despesas.php?erro=Manutenção não encontrada');
    }
} else {
    header('Location: ../despesas/despesas.php');
}
exit;
?>