<?php
require '../conexao.php';

$resultado = $conexao->query("SELECT * FROM servicos");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Serviços</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=rk8gMHQsBQHb&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Todos os Serviços</h1>
    </div>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_servico.php">+ Adicionar Serviço</a>
    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Preço</th>
            <th>Ações</th>
        </tr>
        <?php while ($servico = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $servico['S_id_servico'] ?></td>
                <td><?= $servico['S_nome'] ?></td>
                <td><?= $servico['S_preco'] ?>€</td>
                <td>
                    <a href="editar_servico.php?id=<?= $servico['S_id_servico'] ?>">Editar</a> |
                    <a href="eliminar_servico.php?id=<?= $servico['S_id_servico'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
<a href="admin.php">← Voltar</a>
</html>

<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $valor = $conexao->query("SELECT S_preco FROM servicos WHERE S_id_servico = $id")->fetch_assoc()['S_preco'];
    $saldo = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1")->fetch_assoc()['saldo'];

    if ($saldo >= $valor) {
        // Atualizar saldo
        $conexao->query("UPDATE conta_virtual SET saldo = saldo - $valor WHERE id = 1");

        // Registrar movimentação
        $descricao = "Pagamento de serviço #$id";
        $conexao->query("INSERT INTO movimentacoes (tipo, descricao, valor, origem, origem_id)
                         VALUES ('despesa', '$descricao', $valor, 'servico', $id)");

        // Registrar receita
        $conexao->query("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_origem, R_origem_id)
                         VALUES ('Receita de serviço #$id', $valor, NOW(), 'Serviço', 'servico', $id)");

        // Marcar como pago
        $conexao->query("UPDATE servicos SET S_pago = 1 WHERE S_id_servico = $id");

        header('Location: despesas.php?msg=Serviço pago com sucesso');
    } else {
        echo "Saldo insuficiente.";
    }
}
?>
