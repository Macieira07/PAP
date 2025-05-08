<?php
require '../conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Obter valor da manutenção
    $result = $conexao->query("SELECT M_custo FROM manutencao WHERE M_id_manutencao = $id");
    $manutencao = $result->fetch_assoc();
    $valor = $manutencao['M_custo'];
    
    // Obter saldo atual
    $saldo = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1")->fetch_assoc()['saldo'];
    
    if ($saldo >= $valor) {
        // Atualizar saldo
        $conexao->query("UPDATE conta_virtual SET saldo = saldo - $valor WHERE id = 1");
        
        // Registrar movimentação
        $descricao = "Pagamento de manutenção #$id";
        $conexao->query("INSERT INTO movimentacoes (tipo, descricao, valor, origem, origem_id)
                         VALUES ('despesa', '$descricao', $valor, 'manutencao', $id)");
        
        // Marcar como pago
        $conexao->query("UPDATE manutencao SET M_pago = 1 WHERE M_id_manutencao = $id");
        
        header('Location: despesas.php?msg=Manutenção paga com sucesso');
    } else {
        header('Location: despesas.php?erro=Saldo insuficiente para pagar a manutenção');
    }
} else {
    header('Location: despesas.php');
}
exit;
?>